#!/usr/bin/env python3
"""
Audio-Based Threat Detection API Server
Flask application for real-time audio analysis
"""
import os
import sys
import warnings
from flask import jsonify

# Add project root to path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

# Suppress warnings for cleaner logs
warnings.filterwarnings('ignore', category=UserWarning)
warnings.filterwarnings('ignore', category=RuntimeWarning)

# Configure FFmpeg path for pydub (Windows)
import glob
ffmpeg_paths = [
    r"C:\ffmpeg\bin",
    r"C:\Program Files\ffmpeg\bin",
    os.path.expanduser(r"~\AppData\Local\Microsoft\WinGet\Links"),
    os.path.expanduser(r"~\scoop\shims"),
]
# Also search for WinGet installed FFmpeg
winget_pattern = os.path.expanduser(r"~\AppData\Local\Microsoft\WinGet\Packages\*FFmpeg*\*\bin")
winget_matches = glob.glob(winget_pattern)
ffmpeg_paths.extend(winget_matches)

for ffmpeg_path in ffmpeg_paths:
    if os.path.exists(ffmpeg_path):
        os.environ["PATH"] = ffmpeg_path + os.pathsep + os.environ.get("PATH", "")
        print(f"✅ FFmpeg found at: {ffmpeg_path}")
        break
else:
    print("⚠️ FFmpeg not found in common locations. Audio format support may be limited.")

# Suppress FFmpeg/pydub verbose output
os.environ['PYDUB_FFMPEG_SILENCE'] = '1'
# Redirect FFmpeg stderr to null to suppress format detection errors
if sys.platform == 'win32':
    os.environ['FFMPEG_HIDE_BANNER'] = '1'

from api import create_app
from config import FlaskConfig


def create_application():
    """Create and configure the Flask application"""
    app = create_app()

    # Root endpoint
    @app.route('/')
    def index():
        return jsonify({
            'service': 'Audio-Based Threat Detection API',
            'version': '1.0.0',
            'status': 'running',
            'endpoints': {
                'health': 'GET /api/audio/health',
                'status': 'GET /api/audio/status',
                'analyze': 'POST /api/audio/analyze',
                'calibrate': 'POST /api/audio/calibrate',
                'test': 'GET /api/audio/test',
                'sensitivity': 'GET/POST /api/audio/sensitivity',
                'reset_session': 'POST /api/audio/reset-session',
                'start_session': 'POST /api/detection/start',
                'stop_session': 'POST /api/detection/stop',
                'process_chunk': 'POST /api/detection/process-chunk',
                'sessions': 'GET /api/detection/sessions'
            }
        })

    # Error handlers
    @app.errorhandler(404)
    def not_found(error):
        return jsonify({'error': 'Endpoint not found'}), 404

    @app.errorhandler(500)
    def server_error(error):
        return jsonify({'error': 'Internal server error'}), 500

    return app


def print_banner():
    """Print startup banner"""
    print("\n" + "=" * 60)
    print("   AUDIO-BASED THREAT DETECTION API SERVER")
    print("   Smart School Safety Monitoring System")
    print("=" * 60)
    print(f"\n🚀 Server starting on http://{FlaskConfig.HOST}:{FlaskConfig.PORT}")
    print("\n📡 Available Endpoints:")
    print("   - GET  /                          API Info")
    print("   - GET  /api/audio/health          Health Check")
    print("   - GET  /api/audio/status          Detector Status")
    print("   - POST /api/audio/analyze         Analyze Audio")
    print("   - POST /api/audio/calibrate       Calibrate Noise")
    print("   - GET  /api/audio/test            Test Detection")
    print("   - GET/POST /api/audio/sensitivity Adjust Sensitivity")
    print("   - POST /api/audio/reset-session   Reset Detection Session")
    print("   - POST /api/detection/start       Start Session")
    print("   - POST /api/detection/stop        Stop Session")
    print("   - POST /api/detection/process-chunk  Process Audio Chunk")
    print("=" * 60 + "\n")


if __name__ == '__main__':
    print_banner()

    app = create_application()

    # Run Flask server
    app.run(
        host=FlaskConfig.HOST,
        port=FlaskConfig.PORT,
        debug=FlaskConfig.DEBUG,
        threaded=True
    )

