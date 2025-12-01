"""
Answer Evaluation Routes
Handles automated grading of student submissions
"""
from flask import Blueprint, request, jsonify
import logging

logger = logging.getLogger(__name__)
evaluation_bp = Blueprint('evaluation', __name__)

# Initialize evaluator lazily
_answer_evaluator = None

def get_answer_evaluator():
    global _answer_evaluator
    if _answer_evaluator is None:
        from models.answer_evaluator import AnswerEvaluator
        _answer_evaluator = AnswerEvaluator()
    return _answer_evaluator


@evaluation_bp.route('/evaluate', methods=['POST'])
def evaluate_submission():
    """
    Evaluate a complete homework submission
    ---
    Request Body:
        - questions: list[dict] (question objects with correct answers)
        - answers: list[dict] (student answers with question_id and answer)
    """
    try:
        data = request.get_json()
        
        questions = data.get('questions', [])
        answers = data.get('answers', [])
        
        if not questions or not answers:
            return jsonify({'error': 'Questions and answers are required'}), 400
        
        evaluator = get_answer_evaluator()
        
        # Create question lookup
        question_lookup = {i: q for i, q in enumerate(questions)}
        
        results = []
        total_marks = 0
        marks_obtained = 0
        
        for answer_data in answers:
            question_idx = answer_data.get('question_idx', 0)
            student_answer = answer_data.get('answer', '')
            
            if question_idx in question_lookup:
                question = question_lookup[question_idx]
                evaluation = evaluator.evaluate_answer(question, student_answer)
                
                total_marks += evaluation.get('max_marks', 0)
                marks_obtained += evaluation.get('marks_obtained', 0)
                
                results.append({
                    'question_idx': question_idx,
                    'question_text': question.get('question_text', ''),
                    'student_answer': student_answer,
                    'evaluation': evaluation
                })
        
        percentage = (marks_obtained / total_marks * 100) if total_marks > 0 else 0
        
        return jsonify({
            'success': True,
            'results': results,
            'summary': {
                'total_marks': total_marks,
                'marks_obtained': round(marks_obtained, 1),
                'percentage': round(percentage, 1),
                'grade': _calculate_grade(percentage),
                'questions_correct': sum(1 for r in results if r['evaluation'].get('is_correct', False)),
                'total_questions': len(results)
            }
        })
    except Exception as e:
        logger.error(f"Error evaluating submission: {str(e)}")
        return jsonify({'error': str(e)}), 500


@evaluation_bp.route('/evaluate-single', methods=['POST'])
def evaluate_single_answer():
    """
    Evaluate a single answer
    ---
    Request Body:
        - question: dict (question object)
        - answer: str (student's answer)
    """
    try:
        data = request.get_json()
        
        question = data.get('question')
        answer = data.get('answer', '')
        
        if not question:
            return jsonify({'error': 'Question is required'}), 400
        
        evaluator = get_answer_evaluator()
        result = evaluator.evaluate_answer(question, answer)
        
        return jsonify({
            'success': True,
            'evaluation': result
        })
    except Exception as e:
        logger.error(f"Error evaluating answer: {str(e)}")
        return jsonify({'error': str(e)}), 500


@evaluation_bp.route('/batch-evaluate', methods=['POST'])
def batch_evaluate():
    """
    Evaluate multiple submissions at once
    ---
    Request Body:
        - submissions: list[dict] (each with questions and answers)
    """
    try:
        data = request.get_json()
        submissions = data.get('submissions', [])
        
        evaluator = get_answer_evaluator()
        all_results = []
        
        for submission in submissions:
            questions = submission.get('questions', [])
            answers = submission.get('answers', [])
            student_id = submission.get('student_id')
            
            submission_result = {
                'student_id': student_id,
                'results': [],
                'total_marks': 0,
                'marks_obtained': 0
            }
            
            for i, (question, answer) in enumerate(zip(questions, answers)):
                evaluation = evaluator.evaluate_answer(question, answer.get('answer', ''))
                submission_result['results'].append(evaluation)
                submission_result['total_marks'] += evaluation.get('max_marks', 0)
                submission_result['marks_obtained'] += evaluation.get('marks_obtained', 0)
            
            total = submission_result['total_marks']
            obtained = submission_result['marks_obtained']
            submission_result['percentage'] = (obtained / total * 100) if total > 0 else 0
            submission_result['grade'] = _calculate_grade(submission_result['percentage'])
            
            all_results.append(submission_result)
        
        return jsonify({
            'success': True,
            'results': all_results,
            'total_submissions': len(all_results)
        })
    except Exception as e:
        logger.error(f"Error in batch evaluation: {str(e)}")
        return jsonify({'error': str(e)}), 500


def _calculate_grade(percentage: float) -> str:
    """Calculate letter grade from percentage"""
    if percentage >= 90: return 'A+'
    if percentage >= 85: return 'A'
    if percentage >= 80: return 'A-'
    if percentage >= 75: return 'B+'
    if percentage >= 70: return 'B'
    if percentage >= 65: return 'B-'
    if percentage >= 60: return 'C+'
    if percentage >= 55: return 'C'
    if percentage >= 50: return 'C-'
    if percentage >= 45: return 'D+'
    if percentage >= 40: return 'D'
    return 'F'

