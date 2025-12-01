"""
Answer Evaluator for automated grading of student submissions
Handles MCQ auto-grading and NLP-assisted evaluation for subjective answers
"""
import logging
from typing import Dict, Any, List, Tuple, Optional
import numpy as np
import re

logger = logging.getLogger(__name__)

class AnswerEvaluator:
    """
    AI-powered answer evaluator for automated grading.
    - Instant grading for MCQs
    - NLP-based semantic evaluation for short answers and descriptive questions
    """
    
    def __init__(self, nlp_processor=None):
        self.nlp_processor = nlp_processor
        self.similarity_threshold = {
            'SHORT_ANSWER': 0.6,
            'DESCRIPTIVE': 0.5
        }
        self._initialize()
    
    def _initialize(self):
        """Initialize components for answer evaluation"""
        if self.nlp_processor is None:
            from .nlp_processor import NLPProcessor
            self.nlp_processor = NLPProcessor()
    
    def evaluate_answer(self, question: Dict[str, Any], 
                       student_answer: str) -> Dict[str, Any]:
        """
        Evaluate a student's answer based on question type.
        """
        question_type = question.get('question_type', 'MCQ')
        
        if question_type == 'MCQ':
            return self._evaluate_mcq(question, student_answer)
        elif question_type == 'SHORT_ANSWER':
            return self._evaluate_short_answer(question, student_answer)
        elif question_type == 'DESCRIPTIVE':
            return self._evaluate_descriptive(question, student_answer)
        else:
            return {'error': f'Unknown question type: {question_type}'}
    
    def _evaluate_mcq(self, question: Dict[str, Any], 
                      student_answer: str) -> Dict[str, Any]:
        """Evaluate MCQ answer - instant grading"""
        correct_answer = question.get('correct_answer', '').upper().strip()
        student_answer = student_answer.upper().strip()
        
        is_correct = student_answer == correct_answer
        max_marks = question.get('marks', 1)
        
        return {
            'question_type': 'MCQ',
            'is_correct': is_correct,
            'marks_obtained': max_marks if is_correct else 0,
            'max_marks': max_marks,
            'percentage': 100.0 if is_correct else 0.0,
            'feedback': 'Correct!' if is_correct else f'Incorrect. The correct answer is {correct_answer}.',
            'correct_answer': correct_answer,
            'student_answer': student_answer,
            'explanation': question.get('explanation', '')
        }
    
    def _evaluate_short_answer(self, question: Dict[str, Any], 
                               student_answer: str) -> Dict[str, Any]:
        """Evaluate short answer using NLP semantic similarity"""
        expected_answer = question.get('expected_answer', '')
        key_points = question.get('key_points', [])
        max_marks = question.get('marks', 3)
        
        # Calculate semantic similarity
        similarity = self.nlp_processor.calculate_similarity(student_answer, expected_answer)
        
        # Check key points coverage
        key_points_score = self._check_key_points(student_answer, key_points)
        
        # Combined score (60% semantic, 40% key points)
        combined_score = (similarity * 0.6) + (key_points_score * 0.4)
        
        # Calculate marks
        marks_obtained = round(combined_score * max_marks, 1)
        percentage = round(combined_score * 100, 1)
        
        # Generate feedback
        feedback = self._generate_feedback(combined_score, key_points, key_points_score)
        
        return {
            'question_type': 'SHORT_ANSWER',
            'is_correct': combined_score >= self.similarity_threshold['SHORT_ANSWER'],
            'marks_obtained': marks_obtained,
            'max_marks': max_marks,
            'percentage': percentage,
            'semantic_similarity': round(similarity * 100, 1),
            'key_points_coverage': round(key_points_score * 100, 1),
            'feedback': feedback,
            'missing_points': self._get_missing_points(student_answer, key_points)
        }
    
    def _evaluate_descriptive(self, question: Dict[str, Any], 
                              student_answer: str) -> Dict[str, Any]:
        """Evaluate descriptive answer using comprehensive NLP analysis"""
        expected_answer = question.get('expected_answer', '')
        key_points = question.get('key_points', [])
        max_marks = question.get('marks', 5)
        
        # Multiple evaluation criteria
        scores = {
            'semantic_similarity': self.nlp_processor.calculate_similarity(
                student_answer, expected_answer
            ),
            'key_points_coverage': self._check_key_points(student_answer, key_points),
            'length_adequacy': self._check_length_adequacy(student_answer, 'DESCRIPTIVE'),
            'coherence': self._check_coherence(student_answer)
        }
        
        # Weighted average
        weights = {'semantic_similarity': 0.4, 'key_points_coverage': 0.3,
                   'length_adequacy': 0.15, 'coherence': 0.15}
        combined_score = sum(scores[k] * weights[k] for k in scores)
        
        marks_obtained = round(combined_score * max_marks, 1)
        percentage = round(combined_score * 100, 1)
        
        return {
            'question_type': 'DESCRIPTIVE',
            'is_correct': combined_score >= self.similarity_threshold['DESCRIPTIVE'],
            'marks_obtained': marks_obtained,
            'max_marks': max_marks,
            'percentage': percentage,
            'detailed_scores': {k: round(v * 100, 1) for k, v in scores.items()},
            'feedback': self._generate_detailed_feedback(scores, key_points),
            'missing_points': self._get_missing_points(student_answer, key_points),
            'improvement_suggestions': self._get_improvement_suggestions(scores)
        }
    
    def _check_key_points(self, answer: str, key_points: List[str]) -> float:
        """Check how many key points are covered in the answer"""
        if not key_points:
            return 1.0
        
        answer_lower = answer.lower()
        covered = 0
        
        for point in key_points:
            point_words = set(point.lower().split())
            if any(word in answer_lower for word in point_words if len(word) > 3):
                covered += 1
        
        return covered / len(key_points)
    
    def _check_length_adequacy(self, answer: str, question_type: str) -> float:
        """Check if answer length is appropriate"""
        word_count = len(answer.split())
        min_words = {'SHORT_ANSWER': 20, 'DESCRIPTIVE': 100}
        max_words = {'SHORT_ANSWER': 100, 'DESCRIPTIVE': 500}
        
        min_w = min_words.get(question_type, 20)
        max_w = max_words.get(question_type, 200)
        
        if word_count < min_w:
            return word_count / min_w
        elif word_count > max_w:
            return max(0.5, 1 - (word_count - max_w) / max_w)
        return 1.0
    
    def _check_coherence(self, answer: str) -> float:
        """Simple coherence check based on sentence structure"""
        sentences = re.split(r'[.!?]+', answer)
        sentences = [s.strip() for s in sentences if s.strip()]
        
        if len(sentences) < 2:
            return 0.5
        
        # Check for proper sentence structure
        valid_sentences = sum(1 for s in sentences if len(s.split()) >= 3)
        return valid_sentences / len(sentences)
    
    def _get_missing_points(self, answer: str, key_points: List[str]) -> List[str]:
        """Identify which key points are missing"""
        missing = []
        answer_lower = answer.lower()
        for point in key_points:
            point_words = set(point.lower().split())
            if not any(word in answer_lower for word in point_words if len(word) > 3):
                missing.append(point)
        return missing
    
    def _generate_feedback(self, score: float, key_points: List[str], 
                          key_points_score: float) -> str:
        """Generate constructive feedback"""
        if score >= 0.8:
            return "Excellent answer! You've covered the topic comprehensively."
        elif score >= 0.6:
            return "Good answer. Consider adding more details about the key concepts."
        elif score >= 0.4:
            return "Satisfactory answer. Try to cover more key points and provide examples."
        else:
            return "Answer needs improvement. Review the topic and include main concepts."
    
    def _generate_detailed_feedback(self, scores: Dict[str, float], 
                                    key_points: List[str]) -> str:
        """Generate detailed feedback for descriptive answers"""
        feedback_parts = []
        
        if scores['semantic_similarity'] < 0.5:
            feedback_parts.append("Your answer needs to focus more on the main topic.")
        if scores['key_points_coverage'] < 0.6:
            feedback_parts.append("Try to cover more key points in your answer.")
        if scores['length_adequacy'] < 0.7:
            feedback_parts.append("Consider providing more detailed explanations.")
        if scores['coherence'] < 0.7:
            feedback_parts.append("Improve the structure and flow of your answer.")
        
        if not feedback_parts:
            return "Excellent work! Your answer is comprehensive and well-structured."
        return " ".join(feedback_parts)
    
    def _get_improvement_suggestions(self, scores: Dict[str, float]) -> List[str]:
        """Get specific improvement suggestions"""
        suggestions = []
        if scores['key_points_coverage'] < 0.6:
            suggestions.append("Include all key concepts mentioned in the lesson")
        if scores['length_adequacy'] < 0.7:
            suggestions.append("Provide more detailed explanations with examples")
        if scores['coherence'] < 0.7:
            suggestions.append("Use clear paragraph structure with topic sentences")
        return suggestions

