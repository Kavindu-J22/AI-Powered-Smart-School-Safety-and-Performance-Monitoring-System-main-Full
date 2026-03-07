"""
Attendance Service
===================
Handles real-time attendance marking and reporting.
"""

import cv2
import numpy as np
from typing import Dict, List, Optional, Any
from datetime import datetime, timedelta
import logging
import requests
from threading import Thread, Event, Lock
import time

logger = logging.getLogger(__name__)


class AttendanceService:
    """
    Service for attendance operations.
    
    Features:
    - Real-time attendance marking
    - Attendance reports
    - Dashboard synchronization
    - Webhook notifications
    """
    
    def __init__(
        self,
        attendance_engine: 'AttendanceEngine',
        attendance_db: 'AttendanceDB',
        dashboard_api_url: str = None,
        dashboard_api_key: str = None,
        webhook_url: str = None,
        cooldown_seconds: int = 300
    ):
        """
        Initialize attendance service.
        
        Args:
            attendance_engine: AttendanceEngine instance
            attendance_db: AttendanceDB instance
            dashboard_api_url: Dashboard API URL for sync
            dashboard_api_key: Dashboard API key
            webhook_url: Webhook URL for notifications
            cooldown_seconds: Cooldown between re-marking
        """
        self.engine = attendance_engine
        self.db = attendance_db
        
        self.dashboard_api_url = dashboard_api_url
        self.dashboard_api_key = dashboard_api_key
        self.webhook_url = webhook_url
        self.cooldown_seconds = cooldown_seconds
        
        # Last attendance times for cooldown
        self._last_marked: Dict[str, datetime] = {}
        self._lock = Lock()
        
        # Sync thread
        self._sync_stop = Event()
        self._sync_thread = None
    
    def mark_attendance_from_image(
        self,
        image: np.ndarray,
        location: str = None
    ) -> Dict:
        """
        Mark attendance from a single image.
        
        Args:
            image: Input image (BGR)
            location: Optional location identifier
            
        Returns:
            Attendance marking result
        """
        # Process frame
        results = self.engine.process_frame(image, mark_attendance=False)

        if not results:
            return {
                'success': False,
                'message': 'No faces detected',
                'faces': []
            }

        marked = []
        # Build face list for UI overlay (bbox + confidence for ALL detected faces)
        faces = []
        for result in results:
            bbox = list(result.face_bbox) if result.face_bbox else None
            faces.append({
                'bbox': bbox,
                'confidence': round(float(result.confidence) * 100, 1),
                'is_recognized': result.is_recognized,
                'student_id': result.student_id,
                'student_name': result.student_name,
            })

        for result in results:
            if not result.is_recognized:
                continue

            if not result.is_live:
                logger.warning(f"Spoof detected for {result.student_id}")
                continue

            # Check cooldown
            if not self._check_cooldown(result.student_id):
                continue

            # Mark in database
            record = self.db.mark_attendance(
                student_id=result.student_id,
                confidence=result.confidence,
                liveness_score=result.liveness_score,
                status=self._determine_status()
            )

            if record:
                marked.append({
                    'student_id': result.student_id,
                    'student_name': result.student_name,
                    'confidence': result.confidence,
                    'status': record['status'],
                    'timestamp': record['timestamp'],
                    'already_marked': record.get('already_marked', False),
                })

                # Update cooldown
                with self._lock:
                    self._last_marked[result.student_id] = datetime.now()

                # Send webhook
                if self.webhook_url:
                    self._send_webhook(record)

        return {
            'success': len(marked) > 0,
            'total_faces': len(results),
            'faces': faces,
            'marked': marked
        }
    
    def _check_cooldown(self, student_id: str) -> bool:
        """Check if student can be marked (cooldown passed)."""
        with self._lock:
            last_time = self._last_marked.get(student_id)
            
            if last_time is None:
                return True
            
            elapsed = (datetime.now() - last_time).total_seconds()
            return elapsed >= self.cooldown_seconds
    
    def _determine_status(self) -> str:
        """Determine attendance status based on time."""
        now = datetime.now()
        hour = now.hour
        minute = now.minute
        
        # Configure these thresholds as needed
        if hour < 8:
            return 'early'
        elif hour == 8 and minute <= 15:
            return 'present'
        elif hour < 12:
            return 'late'
        else:
            return 'present'  # Afternoon
    
    def _send_webhook(self, record: dict):
        """Send webhook notification for attendance."""
        if not self.webhook_url:
            return
        
        try:
            payload = {
                'event': 'attendance_marked',
                'data': {
                    'student_id': record['student_id'],
                    'timestamp': record['timestamp'],
                    'status': record['status'],
                    'confidence': record.get('confidence', 0)
                }
            }
            
            headers = {}
            if self.dashboard_api_key:
                headers['Authorization'] = f'Bearer {self.dashboard_api_key}'
            
            response = requests.post(
                self.webhook_url,
                json=payload,
                headers=headers,
                timeout=5
            )
            
            if response.status_code == 200:
                logger.debug(f"Webhook sent for {record['student_id']}")
            else:
                logger.warning(f"Webhook failed: {response.status_code}")
                
        except Exception as e:
            logger.error(f"Webhook error: {e}")
    
    def get_today_attendance(self) -> Dict:
        """Get today's attendance summary."""
        records = self.db.get_today_attendance()
        
        summary = {
            'date': datetime.now().strftime('%Y-%m-%d'),
            'total_marked': len(records),
            'present': 0,
            'late': 0,
            'early': 0,
            'records': []
        }
        
        for record in records:
            summary[record.status] = summary.get(record.status, 0) + 1
            summary['records'].append(record.to_dict())
        
        return summary
    
    def get_attendance_by_date(self, date_str: str) -> Dict:
        """Get attendance for a specific date."""
        records = self.db.get_attendance_by_date(date_str)
        
        return {
            'date': date_str,
            'total_marked': len(records),
            'records': [r.to_dict() for r in records]
        }
    
    def get_student_attendance(
        self,
        student_id: str,
        days: int = 30
    ) -> Dict:
        """Get attendance history for a student."""
        end_date = datetime.now().strftime('%Y-%m-%d')
        start_date = (datetime.now() - timedelta(days=days)).strftime('%Y-%m-%d')
        
        records = self.db.get_student_attendance(
            student_id,
            start_date=start_date,
            end_date=end_date
        )
        
        # Calculate statistics
        total_days = days
        present_days = len(set(r.date for r in records))
        
        return {
            'student_id': student_id,
            'period': f'{start_date} to {end_date}',
            'total_days': total_days,
            'present_days': present_days,
            'attendance_rate': (present_days / total_days) * 100 if total_days > 0 else 0,
            'records': [r.to_dict() for r in records]
        }
    
    def get_attendance_stats(
        self,
        start_date: str = None,
        end_date: str = None
    ) -> Dict:
        """Get attendance statistics."""
        return self.db.get_attendance_stats(start_date, end_date)
    
    def sync_to_dashboard(self) -> Dict:
        """Sync unsynced records to dashboard."""
        if not self.dashboard_api_url:
            return {'error': 'Dashboard API not configured'}
        
        unsynced = self.db.get_unsynced_records()
        
        if not unsynced:
            return {'success': True, 'synced': 0, 'message': 'Nothing to sync'}
        
        synced_count = 0
        failed_count = 0
        
        for record in unsynced:
            try:
                response = requests.post(
                    f"{self.dashboard_api_url}/attendance",
                    json={
                        'student_id': record.student_id,
                        'timestamp': record.timestamp.isoformat(),
                        'status': record.status,
                        'confidence': record.confidence
                    },
                    headers={
                        'Authorization': f'Bearer {self.dashboard_api_key}'
                    } if self.dashboard_api_key else {},
                    timeout=10
                )
                
                if response.status_code in [200, 201]:
                    data = response.json()
                    self.db.mark_synced(
                        record.id,
                        dashboard_record_id=data.get('id')
                    )
                    synced_count += 1
                else:
                    failed_count += 1
                    
            except Exception as e:
                logger.error(f"Sync error for {record.id}: {e}")
                failed_count += 1
        
        return {
            'success': True,
            'synced': synced_count,
            'failed': failed_count
        }
    
    def start_auto_sync(self, interval_seconds: int = 60):
        """Start background sync thread."""
        if self._sync_thread and self._sync_thread.is_alive():
            return
        
        self._sync_stop.clear()
        
        def sync_loop():
            while not self._sync_stop.wait(interval_seconds):
                try:
                    self.sync_to_dashboard()
                except Exception as e:
                    logger.error(f"Auto-sync error: {e}")
        
        self._sync_thread = Thread(target=sync_loop, daemon=True)
        self._sync_thread.start()
        logger.info("Auto-sync started")
    
    def stop_auto_sync(self):
        """Stop background sync thread."""
        self._sync_stop.set()
        if self._sync_thread:
            self._sync_thread.join(timeout=5)
        logger.info("Auto-sync stopped")
    
    def export_attendance_report(
        self,
        start_date: str,
        end_date: str,
        format: str = 'json'
    ) -> Any:
        """
        Export attendance report.
        
        Args:
            start_date: Start date (YYYY-MM-DD)
            end_date: End date (YYYY-MM-DD)
            format: 'json' or 'csv'
            
        Returns:
            Report data
        """
        # Get all records in range
        all_records = []
        current = datetime.strptime(start_date, '%Y-%m-%d')
        end = datetime.strptime(end_date, '%Y-%m-%d')
        
        while current <= end:
            date_str = current.strftime('%Y-%m-%d')
            records = self.db.get_attendance_by_date(date_str)
            all_records.extend(records)
            current += timedelta(days=1)
        
        if format == 'json':
            return {
                'period': f'{start_date} to {end_date}',
                'total_records': len(all_records),
                'records': [r.to_dict() for r in all_records]
            }
        elif format == 'csv':
            import io
            import csv
            
            output = io.StringIO()
            writer = csv.writer(output)
            writer.writerow([
                'Date', 'Student ID', 'Student Name', 
                'Time In', 'Status', 'Confidence'
            ])
            
            for r in all_records:
                writer.writerow([
                    r.date,
                    r.student_id,
                    r.student.name if r.student else '',
                    r.time_in.strftime('%H:%M:%S') if r.time_in else '',
                    r.status,
                    f'{r.confidence:.2f}'
                ])
            
            return output.getvalue()
        
        return None
