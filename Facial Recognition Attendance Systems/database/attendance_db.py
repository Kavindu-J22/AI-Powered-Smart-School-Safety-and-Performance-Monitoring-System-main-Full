"""
Attendance Database Module
===========================
Handles attendance records using SQLAlchemy.
"""

from datetime import datetime, date, timedelta
from typing import List, Optional, Dict, Any
from sqlalchemy import create_engine, and_, func
from sqlalchemy.orm import sessionmaker, Session
import logging

from .models import Base, Student, AttendanceRecord, TrainingLog

logger = logging.getLogger(__name__)


class AttendanceDB:
    """
    Database interface for attendance records.
    """
    
    def __init__(self, database_url: str):
        """
        Initialize attendance database.
        
        Args:
            database_url: SQLAlchemy database URL
        """
        self.engine = create_engine(database_url, echo=False)
        Base.metadata.create_all(self.engine)
        self.SessionLocal = sessionmaker(bind=self.engine)
        
        logger.info(f"AttendanceDB initialized: {database_url}")
    
    def get_session(self) -> Session:
        """Get a new database session."""
        return self.SessionLocal()
    
    # ==================== Student Operations ====================
    
    def add_student(
        self,
        student_id: str,
        name: str,
        email: str = None,
        grade: str = None,
        section: str = None,
        dashboard_student_id: int = None
    ) -> Optional[Student]:
        """Add a new student."""
        session = self.get_session()
        try:
            student = Student(
                student_id=student_id,
                name=name,
                email=email,
                grade=grade,
                section=section,
                dashboard_student_id=dashboard_student_id
            )
            session.add(student)
            session.commit()
            session.refresh(student)
            logger.info(f"Added student: {student_id}")
            return student
        except Exception as e:
            session.rollback()
            logger.error(f"Error adding student: {e}")
            return None
        finally:
            session.close()
    
    def get_student(self, student_id: str) -> Optional[Student]:
        """Get student by ID."""
        session = self.get_session()
        try:
            return session.query(Student).filter(
                Student.student_id == student_id
            ).first()
        finally:
            session.close()
    
    def get_student_by_dashboard_id(self, dashboard_id: int) -> Optional[Student]:
        """Get student by dashboard ID."""
        session = self.get_session()
        try:
            return session.query(Student).filter(
                Student.dashboard_student_id == dashboard_id
            ).first()
        finally:
            session.close()
    
    def update_student(
        self,
        student_id: str,
        **kwargs
    ) -> bool:
        """Update student information."""
        session = self.get_session()
        try:
            student = session.query(Student).filter(
                Student.student_id == student_id
            ).first()
            
            if not student:
                return False
            
            for key, value in kwargs.items():
                if hasattr(student, key):
                    setattr(student, key, value)
            
            session.commit()
            return True
        except Exception as e:
            session.rollback()
            logger.error(f"Error updating student: {e}")
            return False
        finally:
            session.close()
    
    def mark_face_registered(
        self,
        student_id: str,
        images_count: int
    ) -> bool:
        """Mark student as having face registered."""
        return self.update_student(
            student_id,
            is_face_registered=True,
            face_images_count=images_count,
            registration_date=datetime.now()
        )
    
    def get_all_students(self) -> List[Student]:
        """Get all students."""
        session = self.get_session()
        try:
            return session.query(Student).all()
        finally:
            session.close()
    
    def get_registered_students(self) -> List[Student]:
        """Get students with face registered."""
        session = self.get_session()
        try:
            return session.query(Student).filter(
                Student.is_face_registered == True
            ).all()
        finally:
            session.close()
    
    # ==================== Attendance Operations ====================
    
    def mark_attendance(
        self,
        student_id: str,
        confidence: float = 0.0,
        liveness_score: float = 1.0,
        status: str = 'present',
        image_path: str = None
    ) -> Optional[AttendanceRecord]:
        """
        Mark attendance for a student.
        
        Args:
            student_id: Student identifier
            confidence: Recognition confidence
            liveness_score: Liveness detection score
            status: Attendance status
            image_path: Path to captured image
            
        Returns:
            AttendanceRecord if successful
        """
        session = self.get_session()
        try:
            now = datetime.now()
            today_str = now.strftime("%Y-%m-%d")

            # Check if already marked today
            existing = session.query(AttendanceRecord).filter(
                and_(
                    AttendanceRecord.student_id == student_id,
                    AttendanceRecord.date == today_str
                )
            ).first()

            if existing:
                # Update time_out if already marked
                existing.time_out = now
                session.commit()
                session.refresh(existing)
                logger.info(f"Updated attendance (time_out) for {student_id}")
                # Extract data BEFORE session closes to avoid DetachedInstanceError
                return {
                    'id': existing.id,
                    'student_id': existing.student_id,
                    'status': existing.status,
                    'timestamp': existing.timestamp.isoformat() if existing.timestamp else now.isoformat(),
                    'date': existing.date,
                    'time_in': existing.time_in.isoformat() if existing.time_in else None,
                    'time_out': existing.time_out.isoformat() if existing.time_out else None,
                    'confidence': float(existing.confidence) if existing.confidence is not None else 0.0,
                    'already_marked': True,
                }

            # Create new record
            record = AttendanceRecord(
                student_id=student_id,
                timestamp=now,
                date=today_str,
                time_in=now,
                confidence=confidence,
                liveness_score=liveness_score,
                status=status,
                image_path=image_path
            )

            session.add(record)
            session.commit()
            session.refresh(record)

            logger.info(f"Marked attendance for {student_id}: {status}")
            # Extract data BEFORE session closes to avoid DetachedInstanceError
            return {
                'id': record.id,
                'student_id': record.student_id,
                'status': record.status,
                'timestamp': record.timestamp.isoformat() if record.timestamp else now.isoformat(),
                'date': record.date,
                'time_in': record.time_in.isoformat() if record.time_in else None,
                'time_out': record.time_out.isoformat() if record.time_out else None,
                'confidence': float(record.confidence) if record.confidence is not None else 0.0,
                'already_marked': False,
            }

        except Exception as e:
            session.rollback()
            logger.error(f"Error marking attendance: {e}")
            return None
        finally:
            session.close()
    
    def get_attendance_by_date(
        self,
        date_str: str
    ) -> List[AttendanceRecord]:
        """Get all attendance records for a date."""
        session = self.get_session()
        try:
            return session.query(AttendanceRecord).filter(
                AttendanceRecord.date == date_str
            ).all()
        finally:
            session.close()
    
    def get_today_attendance(self) -> List[AttendanceRecord]:
        """Get today's attendance."""
        today_str = datetime.now().strftime("%Y-%m-%d")
        return self.get_attendance_by_date(today_str)
    
    def get_student_attendance(
        self,
        student_id: str,
        start_date: str = None,
        end_date: str = None
    ) -> List[AttendanceRecord]:
        """Get attendance records for a student."""
        session = self.get_session()
        try:
            query = session.query(AttendanceRecord).filter(
                AttendanceRecord.student_id == student_id
            )
            
            if start_date:
                query = query.filter(AttendanceRecord.date >= start_date)
            if end_date:
                query = query.filter(AttendanceRecord.date <= end_date)
            
            return query.order_by(AttendanceRecord.date.desc()).all()
        finally:
            session.close()
    
    def get_attendance_stats(
        self,
        start_date: str = None,
        end_date: str = None
    ) -> Dict[str, Any]:
        """Get attendance statistics."""
        session = self.get_session()
        try:
            query = session.query(AttendanceRecord)
            
            if start_date:
                query = query.filter(AttendanceRecord.date >= start_date)
            if end_date:
                query = query.filter(AttendanceRecord.date <= end_date)
            
            records = query.all()
            
            total_students = session.query(Student).count()
            
            stats = {
                'total_records': len(records),
                'total_students': total_students,
                'present': sum(1 for r in records if r.status == 'present'),
                'late': sum(1 for r in records if r.status == 'late'),
                'early': sum(1 for r in records if r.status == 'early'),
                'unique_students_marked': len(set(r.student_id for r in records))
            }
            
            return stats
        finally:
            session.close()
    
    def get_unsynced_records(self) -> List[AttendanceRecord]:
        """Get attendance records not synced to dashboard."""
        session = self.get_session()
        try:
            return session.query(AttendanceRecord).filter(
                AttendanceRecord.synced_to_dashboard == False
            ).all()
        finally:
            session.close()
    
    def mark_synced(self, record_id: int, dashboard_record_id: int = None):
        """Mark attendance record as synced."""
        session = self.get_session()
        try:
            record = session.query(AttendanceRecord).filter(
                AttendanceRecord.id == record_id
            ).first()
            
            if record:
                record.synced_to_dashboard = True
                record.dashboard_record_id = dashboard_record_id
                session.commit()
        except Exception as e:
            session.rollback()
            logger.error(f"Error marking synced: {e}")
        finally:
            session.close()
    
    # ==================== Training Log Operations ====================
    
    def create_training_log(self) -> TrainingLog:
        """Create a new training log entry."""
        session = self.get_session()
        try:
            log = TrainingLog(
                started_at=datetime.now(),
                status='training'
            )
            session.add(log)
            session.commit()
            session.refresh(log)
            return log
        except Exception as e:
            session.rollback()
            logger.error(f"Error creating training log: {e}")
            return None
        finally:
            session.close()
    
    def update_training_log(
        self,
        log_id: int,
        status: str = None,
        **kwargs
    ):
        """Update training log."""
        session = self.get_session()
        try:
            log = session.query(TrainingLog).filter(
                TrainingLog.id == log_id
            ).first()
            
            if log:
                if status:
                    log.status = status
                if status == 'completed':
                    log.completed_at = datetime.now()
                
                for key, value in kwargs.items():
                    if hasattr(log, key):
                        setattr(log, key, value)
                
                session.commit()
        except Exception as e:
            session.rollback()
            logger.error(f"Error updating training log: {e}")
        finally:
            session.close()
    
    def get_latest_training_log(self) -> Optional[TrainingLog]:
        """Get most recent training log."""
        session = self.get_session()
        try:
            return session.query(TrainingLog).order_by(
                TrainingLog.id.desc()
            ).first()
        finally:
            session.close()
