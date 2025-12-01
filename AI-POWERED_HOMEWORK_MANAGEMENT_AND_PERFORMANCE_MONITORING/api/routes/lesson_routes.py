"""
Lesson Processing Routes
Handles lesson input, parsing, and question generation
"""
from flask import Blueprint, request, jsonify
import logging

logger = logging.getLogger(__name__)
lesson_bp = Blueprint('lessons', __name__)

# Initialize models lazily
_nlp_processor = None
_question_generator = None

def get_nlp_processor():
    global _nlp_processor
    if _nlp_processor is None:
        from models.nlp_processor import NLPProcessor
        _nlp_processor = NLPProcessor()
    return _nlp_processor

def get_question_generator():
    global _question_generator
    if _question_generator is None:
        from models.question_generator import QuestionGenerator
        _question_generator = QuestionGenerator(get_nlp_processor())
    return _question_generator


@lesson_bp.route('/parse', methods=['POST'])
def parse_lesson():
    """
    Parse lesson content and extract key information
    ---
    Request Body:
        - content: str (lesson text content)
        - subject: str (science, history, english, health_science)
        - grade: int (6-11)
        - unit: str (unit/topic name)
        - topics: list[str] (main topics)
    """
    try:
        data = request.get_json()
        
        if not data:
            return jsonify({'error': 'No data provided'}), 400
        
        nlp = get_nlp_processor()
        parsed = nlp.parse_lesson(data)
        
        return jsonify({
            'success': True,
            'data': parsed
        })
    except Exception as e:
        logger.error(f"Error parsing lesson: {str(e)}")
        return jsonify({'error': str(e)}), 500


@lesson_bp.route('/generate-questions', methods=['POST'])
def generate_questions():
    """
    Generate questions from lesson content
    ---
    Request Body:
        - lesson_data: dict (parsed lesson data)
        - num_mcq: int (default: 2)
        - num_short: int (default: 2)
        - num_descriptive: int (default: 1)
    """
    try:
        data = request.get_json()
        
        if not data or 'lesson_data' not in data:
            return jsonify({'error': 'lesson_data is required'}), 400
        
        lesson_data = data['lesson_data']
        num_mcq = data.get('num_mcq', 2)
        num_short = data.get('num_short', 2)
        num_descriptive = data.get('num_descriptive', 1)
        
        generator = get_question_generator()
        questions = generator.generate_questions(
            lesson_data, num_mcq, num_short, num_descriptive
        )
        
        return jsonify({
            'success': True,
            'questions': questions,
            'total': len(questions),
            'breakdown': {
                'mcq': num_mcq,
                'short_answer': num_short,
                'descriptive': num_descriptive
            }
        })
    except Exception as e:
        logger.error(f"Error generating questions: {str(e)}")
        return jsonify({'error': str(e)}), 500


@lesson_bp.route('/extract-keywords', methods=['POST'])
def extract_keywords():
    """Extract keywords from lesson content"""
    try:
        data = request.get_json()
        content = data.get('content', '')
        max_keywords = data.get('max_keywords', 10)
        
        nlp = get_nlp_processor()
        keywords = nlp.extract_keywords(content, max_keywords)
        
        return jsonify({
            'success': True,
            'keywords': keywords
        })
    except Exception as e:
        logger.error(f"Error extracting keywords: {str(e)}")
        return jsonify({'error': str(e)}), 500


@lesson_bp.route('/subjects', methods=['GET'])
def get_subjects():
    """Get list of supported subjects"""
    from config import SUPPORTED_SUBJECTS, SUPPORTED_GRADES
    return jsonify({
        'subjects': SUPPORTED_SUBJECTS,
        'grades': SUPPORTED_GRADES
    })

