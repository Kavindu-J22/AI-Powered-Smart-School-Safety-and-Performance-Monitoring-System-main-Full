"""
Real-time Detection API Routes
HTTP endpoints for real-time audio streaming
"""
from flask import Blueprint, request, jsonify
import numpy as np
import base64
import os
import sys
import time

sys.path.append(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))))
from utils.audio_processor import AudioProcessor
from models.threat_detector import ThreatDetector
from config import AudioConfig

detection_bp = Blueprint('detection', __name__)

# Initialize components
audio_processor = AudioProcessor()
threat_detector = ThreatDetector()

# Store active sessions
active_sessions = {}


@detection_bp.route('/start', methods=['POST'])
def start_detection():
    """Start a detection session"""
    session_id = request.json.get('session_id', str(time.time()))
    
    active_sessions[session_id] = {
        'started_at': time.time(),
        'alerts_count': 0,
        'audio_chunks_processed': 0
    }
    
    return jsonify({
        'success': True,
        'session_id': session_id,
        'message': 'Detection session started'
    })


@detection_bp.route('/stop', methods=['POST'])
def stop_detection():
    """Stop a detection session"""
    session_id = request.json.get('session_id')
    
    if session_id and session_id in active_sessions:
        session_info = active_sessions.pop(session_id)
        duration = time.time() - session_info['started_at']
        
        return jsonify({
            'success': True,
            'session_id': session_id,
            'duration': round(duration, 2),
            'alerts_count': session_info['alerts_count'],
            'chunks_processed': session_info['audio_chunks_processed']
        })
    
    return jsonify({
        'success': False,
        'error': 'Session not found'
    }), 404


@detection_bp.route('/process-chunk', methods=['POST'])
def process_audio_chunk():
    """Process a single audio chunk and return results"""
    try:
        data = request.get_json()
        
        if 'audio_data' not in data:
            return jsonify({'error': 'audio_data required'}), 400
        
        session_id = data.get('session_id')
        
        # Decode audio
        audio_data = audio_processor.decode_base64_audio(data['audio_data'])
        
        # Check if silent
        if audio_processor.is_silent(audio_data):
            return jsonify({
                'success': True,
                'is_threat': False,
                'skipped': True,
                'reason': 'silent_audio'
            })
        
        # Analyze
        result = threat_detector.analyze_audio(audio_data)
        
        # Update session if exists
        if session_id and session_id in active_sessions:
            active_sessions[session_id]['audio_chunks_processed'] += 1
            if result['is_threat']:
                active_sessions[session_id]['alerts_count'] += 1
        
        return jsonify({
            'success': True,
            **result
        })
        
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500


@detection_bp.route('/sessions', methods=['GET'])
def list_sessions():
    """List active detection sessions"""
    sessions = []
    for session_id, info in active_sessions.items():
        sessions.append({
            'session_id': session_id,
            'duration': round(time.time() - info['started_at'], 2),
            'alerts_count': info['alerts_count'],
            'chunks_processed': info['audio_chunks_processed']
        })
    
    return jsonify({
        'success': True,
        'active_sessions': sessions
    })


# Note: WebSocket support removed for Python 3.14 compatibility
# Using HTTP polling instead via the /process-chunk endpoint

