"""
Homework Assignment Routes
Handles homework creation, scheduling, and assignment
"""
from flask import Blueprint, request, jsonify
import logging
from datetime import datetime, timedelta
import random
import uuid

logger = logging.getLogger(__name__)
homework_bp = Blueprint('homework', __name__)


@homework_bp.route('/create', methods=['POST'])
def create_homework():
    """
    Create a new homework assignment
    ---
    Request Body:
        - title: str
        - subject: str
        - grade: int
        - class_id: int
        - questions: list[dict]
        - due_date: str (YYYY-MM-DD)
        - assigned_by: int (teacher_id)
    """
    try:
        data = request.get_json()
        
        required_fields = ['title', 'subject', 'grade', 'questions']
        for field in required_fields:
            if field not in data:
                return jsonify({'error': f'{field} is required'}), 400
        
        # Create homework object
        homework = {
            'id': str(uuid.uuid4()),
            'title': data['title'],
            'subject': data['subject'],
            'grade': data['grade'],
            'class_id': data.get('class_id'),
            'questions': data['questions'],
            'total_marks': sum(q.get('marks', 1) for q in data['questions']),
            'due_date': data.get('due_date', (datetime.now() + timedelta(days=3)).strftime('%Y-%m-%d')),
            'assigned_by': data.get('assigned_by'),
            'created_at': datetime.now().isoformat(),
            'status': 'active'
        }
        
        return jsonify({
            'success': True,
            'homework': homework,
            'message': 'Homework created successfully'
        })
    except Exception as e:
        logger.error(f"Error creating homework: {str(e)}")
        return jsonify({'error': str(e)}), 500


@homework_bp.route('/schedule-weekly', methods=['POST'])
def schedule_weekly_homework():
    """
    Schedule 2 homework assignments per subject per week
    ---
    Request Body:
        - subject: str
        - grade: int
        - class_id: int
        - week_start: str (YYYY-MM-DD)
        - lesson_data: dict
    """
    try:
        data = request.get_json()
        
        subject = data.get('subject')
        grade = data.get('grade')
        class_id = data.get('class_id')
        week_start = datetime.strptime(data.get('week_start', datetime.now().strftime('%Y-%m-%d')), '%Y-%m-%d')
        lesson_data = data.get('lesson_data', {})
        
        # Generate questions for two assignments
        from models.question_generator import QuestionGenerator
        from models.nlp_processor import NLPProcessor
        
        generator = QuestionGenerator(NLPProcessor())
        
        # Assignment 1 (due in 3 days)
        questions_1 = generator.generate_questions(lesson_data, num_mcq=2, num_short=2, num_descriptive=1)
        assignment_1 = {
            'id': str(uuid.uuid4()),
            'title': f"{subject.title()} - Week Assignment 1",
            'subject': subject,
            'grade': grade,
            'class_id': class_id,
            'questions': questions_1,
            'total_marks': sum(q.get('marks', 1) for q in questions_1),
            'due_date': (week_start + timedelta(days=3)).strftime('%Y-%m-%d'),
            'scheduled_for': week_start.strftime('%Y-%m-%d'),
            'status': 'scheduled'
        }
        
        # Assignment 2 (due in 6 days)
        random.shuffle(lesson_data.get('topics', []))  # Randomize topics
        questions_2 = generator.generate_questions(lesson_data, num_mcq=2, num_short=2, num_descriptive=1)
        assignment_2 = {
            'id': str(uuid.uuid4()),
            'title': f"{subject.title()} - Week Assignment 2",
            'subject': subject,
            'grade': grade,
            'class_id': class_id,
            'questions': questions_2,
            'total_marks': sum(q.get('marks', 1) for q in questions_2),
            'due_date': (week_start + timedelta(days=6)).strftime('%Y-%m-%d'),
            'scheduled_for': (week_start + timedelta(days=3)).strftime('%Y-%m-%d'),
            'status': 'scheduled'
        }
        
        return jsonify({
            'success': True,
            'assignments': [assignment_1, assignment_2],
            'message': f'2 assignments scheduled for {subject} grade {grade}'
        })
    except Exception as e:
        logger.error(f"Error scheduling homework: {str(e)}")
        return jsonify({'error': str(e)}), 500


@homework_bp.route('/assign', methods=['POST'])
def assign_homework():
    """
    Assign homework to students
    ---
    Request Body:
        - homework_id: str
        - student_ids: list[int]
    """
    try:
        data = request.get_json()
        homework_id = data.get('homework_id')
        student_ids = data.get('student_ids', [])
        
        assignments = []
        for student_id in student_ids:
            assignment = {
                'id': str(uuid.uuid4()),
                'homework_id': homework_id,
                'student_id': student_id,
                'assigned_at': datetime.now().isoformat(),
                'status': 'assigned',
                'started_at': None,
                'submitted_at': None,
                'marks_obtained': None,
                'percentage': None
            }
            assignments.append(assignment)
        
        return jsonify({
            'success': True,
            'assignments': assignments,
            'total_assigned': len(assignments)
        })
    except Exception as e:
        logger.error(f"Error assigning homework: {str(e)}")
        return jsonify({'error': str(e)}), 500


@homework_bp.route('/submit', methods=['POST'])
def submit_homework():
    """
    Submit student homework answers
    ---
    Request Body:
        - assignment_id: str
        - student_id: int
        - answers: list[dict] (question_id, answer)
    """
    try:
        data = request.get_json()
        
        submission = {
            'id': str(uuid.uuid4()),
            'assignment_id': data.get('assignment_id'),
            'student_id': data.get('student_id'),
            'answers': data.get('answers', []),
            'submitted_at': datetime.now().isoformat(),
            'status': 'submitted'
        }
        
        return jsonify({
            'success': True,
            'submission': submission,
            'message': 'Homework submitted successfully'
        })
    except Exception as e:
        logger.error(f"Error submitting homework: {str(e)}")
        return jsonify({'error': str(e)}), 500

