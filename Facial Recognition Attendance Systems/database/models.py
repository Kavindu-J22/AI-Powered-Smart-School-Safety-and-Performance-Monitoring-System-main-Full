"""
SQLAlchemy Database Models
===========================
Database models for students and attendance records.
"""

from datetime import datetime
from sqlalchemy import (
    Column, Integer, String, Float, DateTime, 
    LargeBinary, ForeignKey, Boolean, Text
)
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import relationship

Base = declarative_base()


class Student(Base):
    """Student model with face registration status."""
    
    __tablename__ = 'students'
    
    id = Column(Integer, primary_key=True, autoincrement=True)
    student_id = Column(String(50), unique=True, nullable=False, index=True)
    name = Column(String(200), nullable=False)
    email = Column(String(200), nullable=True)
    grade = Column(String(20), nullable=True)
    section = Column(String(10), nullable=True)
    
    # Face registration status
    is_face_registered = Column(Boolean, default=False)
    face_images_count = Column(Integer, default=0)
    registration_date = Column(DateTime, nullable=True)
    last_trained = Column(DateTime, nullable=True)
    
    # Dashboard integration
    dashboard_student_id = Column(Integer, nullable=True)  # ID from School Dashboard
    
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    # Relationships
    attendance_records = relationship("AttendanceRecord", back_populates="student")
    
    def __repr__(self):
        return f"<Student(student_id='{self.student_id}', name='{self.name}')>"
    
    def to_dict(self):
        return {
            'id': self.id,
            'student_id': self.student_id,
            'name': self.name,
            'email': self.email,
            'grade': self.grade,
            'section': self.section,
            'is_face_registered': self.is_face_registered,
            'face_images_count': self.face_images_count,
            'registration_date': self.registration_date.isoformat() if self.registration_date else None,
            'created_at': self.created_at.isoformat() if self.created_at else None
        }


class AttendanceRecord(Base):
    """Attendance record model."""
    
    __tablename__ = 'attendance_records'
    
    id = Column(Integer, primary_key=True, autoincrement=True)
    student_id = Column(String(50), ForeignKey('students.student_id'), nullable=False, index=True)
    
    # Attendance details
    timestamp = Column(DateTime, nullable=False, default=datetime.utcnow)
    date = Column(String(10), nullable=False, index=True)  # YYYY-MM-DD format
    time_in = Column(DateTime, nullable=True)
    time_out = Column(DateTime, nullable=True)
    
    # Recognition details
    confidence = Column(Float, default=0.0)
    liveness_score = Column(Float, default=1.0)
    
    # Status
    status = Column(String(20), default='present')  # present, late, absent, early_leave
    
    # Additional info
    notes = Column(Text, nullable=True)
    image_path = Column(String(500), nullable=True)  # Path to captured image
    
    # Sync status with dashboard
    synced_to_dashboard = Column(Boolean, default=False)
    dashboard_record_id = Column(Integer, nullable=True)
    
    created_at = Column(DateTime, default=datetime.utcnow)
    
    # Relationships
    student = relationship("Student", back_populates="attendance_records")
    
    def __repr__(self):
        return f"<AttendanceRecord(student_id='{self.student_id}', date='{self.date}', status='{self.status}')>"
    
    def to_dict(self):
        return {
            'id': self.id,
            'student_id': self.student_id,
            'student_name': self.student.name if self.student else None,
            'timestamp': self.timestamp.isoformat(),
            'date': self.date,
            'time_in': self.time_in.isoformat() if self.time_in else None,
            'time_out': self.time_out.isoformat() if self.time_out else None,
            'confidence': self.confidence,
            'status': self.status,
            'synced_to_dashboard': self.synced_to_dashboard
        }


class FaceEmbedding(Base):
    """Face embedding storage for fast lookup."""
    
    __tablename__ = 'face_embeddings'
    
    id = Column(Integer, primary_key=True, autoincrement=True)
    student_id = Column(String(50), ForeignKey('students.student_id'), nullable=False, index=True)
    
    # Embedding data
    embedding = Column(LargeBinary, nullable=False)  # Serialized numpy array
    embedding_version = Column(String(20), default='v1')  # For model version tracking
    
    # Quality metrics
    quality_score = Column(Float, default=0.0)
    
    created_at = Column(DateTime, default=datetime.utcnow)
    updated_at = Column(DateTime, default=datetime.utcnow, onupdate=datetime.utcnow)
    
    def __repr__(self):
        return f"<FaceEmbedding(student_id='{self.student_id}')>"


class TrainingLog(Base):
    """Training log for tracking model updates."""
    
    __tablename__ = 'training_logs'
    
    id = Column(Integer, primary_key=True, autoincrement=True)
    
    started_at = Column(DateTime, nullable=False)
    completed_at = Column(DateTime, nullable=True)
    
    status = Column(String(20), default='pending')  # pending, training, completed, failed
    
    # Statistics
    total_students = Column(Integer, default=0)
    total_images = Column(Integer, default=0)
    total_embeddings = Column(Integer, default=0)
    
    # Details
    error_message = Column(Text, nullable=True)
    notes = Column(Text, nullable=True)
    
    def __repr__(self):
        return f"<TrainingLog(id={self.id}, status='{self.status}')>"
    
    def to_dict(self):
        return {
            'id': self.id,
            'started_at': self.started_at.isoformat() if self.started_at else None,
            'completed_at': self.completed_at.isoformat() if self.completed_at else None,
            'status': self.status,
            'total_students': self.total_students,
            'total_images': self.total_images,
            'total_embeddings': self.total_embeddings,
            'error_message': self.error_message
        }
