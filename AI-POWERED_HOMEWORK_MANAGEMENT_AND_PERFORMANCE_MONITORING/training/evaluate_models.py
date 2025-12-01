"""
Model Evaluation Script
Evaluates the trained models and calculates accuracy metrics
"""
import os
import sys
import json
import logging
from pathlib import Path
from typing import List, Dict, Any, Tuple
from datetime import datetime
import random

PROJECT_ROOT = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(PROJECT_ROOT))

from training.data_loader import DataLoader

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


class ModelEvaluator:
    """
    Evaluator for the homework management AI models.
    Tests and calculates accuracy metrics.
    """
    
    def __init__(self, model_dir: str = None):
        if model_dir is None:
            self.model_dir = PROJECT_ROOT / "models" / "saved"
        else:
            self.model_dir = Path(model_dir)
        
        self.data_loader = DataLoader()
        self.results = {}
    
    def evaluate_all(self) -> Dict[str, Any]:
        """Run all evaluations"""
        logger.info("Starting model evaluation...")
        
        # Load test data
        questions = self.data_loader.load_all_questions()
        pairs = self.data_loader.get_training_pairs()
        
        # Split for testing (20% holdout)
        test_questions = random.sample(questions, len(questions) // 5)
        test_pairs = random.sample(pairs, len(pairs) // 5)
        
        logger.info(f"Testing with {len(test_questions)} questions, {len(test_pairs)} pairs")
        
        # Evaluate models
        self.results['question_generation'] = self.evaluate_question_generation(test_pairs)
        self.results['mcq_grading'] = self.evaluate_mcq_grading(test_questions)
        self.results['subjective_grading'] = self.evaluate_subjective_grading(test_questions)
        self.results['keyword_extraction'] = self.evaluate_keyword_extraction(test_pairs)
        
        # Calculate overall metrics
        self.results['overall'] = self._calculate_overall_metrics()
        self.results['evaluated_at'] = datetime.now().isoformat()
        
        # Save results
        self._save_results()
        
        logger.info("Evaluation completed!")
        return self.results
    
    def evaluate_question_generation(self, test_pairs: List[Tuple]) -> Dict[str, Any]:
        """Evaluate question generation quality"""
        logger.info("Evaluating question generation...")
        
        from models.question_generator import QuestionGenerator
        from models.nlp_processor import NLPProcessor
        
        generator = QuestionGenerator(NLPProcessor())
        
        total_generated = 0
        valid_questions = 0
        type_accuracy = {'MCQ': 0, 'SHORT_ANSWER': 0, 'DESCRIPTIVE': 0}
        type_total = {'MCQ': 0, 'SHORT_ANSWER': 0, 'DESCRIPTIVE': 0}
        
        for lesson, expected_questions in test_pairs[:20]:  # Limit for speed
            generated = generator.generate_questions(lesson, 2, 2, 1)
            total_generated += len(generated)
            
            for q in generated:
                q_type = q.get('question_type', 'MCQ')
                type_total[q_type] = type_total.get(q_type, 0) + 1
                
                # Validate question
                if self._validate_question(q):
                    valid_questions += 1
                    type_accuracy[q_type] = type_accuracy.get(q_type, 0) + 1
        
        validity_rate = valid_questions / total_generated * 100 if total_generated > 0 else 0
        
        return {
            'total_generated': total_generated,
            'valid_questions': valid_questions,
            'validity_rate': round(validity_rate, 2),
            'by_type': {
                k: round(type_accuracy[k] / type_total[k] * 100, 2) 
                if type_total[k] > 0 else 0 
                for k in type_accuracy
            }
        }
    
    def evaluate_mcq_grading(self, test_questions: List[Dict]) -> Dict[str, Any]:
        """Evaluate MCQ auto-grading accuracy"""
        logger.info("Evaluating MCQ grading...")
        
        from models.answer_evaluator import AnswerEvaluator
        evaluator = AnswerEvaluator()
        
        mcq_questions = [q for q in test_questions if q.get('question_type') == 'MCQ'][:50]
        
        correct_evaluations = 0
        total = len(mcq_questions)
        
        for q in mcq_questions:
            correct_answer = q.get('correct_answer', 'A')
            
            # Test correct answer
            result = evaluator.evaluate_answer(q, correct_answer)
            if result.get('is_correct'):
                correct_evaluations += 1
            
            # Test incorrect answer
            wrong_answer = 'B' if correct_answer != 'B' else 'C'
            result = evaluator.evaluate_answer(q, wrong_answer)
            if not result.get('is_correct'):
                correct_evaluations += 1
            
            total += 1
        
        accuracy = correct_evaluations / total * 100 if total > 0 else 0
        
        return {
            'total_tested': len(mcq_questions),
            'correct_evaluations': correct_evaluations,
            'accuracy': round(accuracy, 2)
        }
    
    def evaluate_subjective_grading(self, test_questions: List[Dict]) -> Dict[str, Any]:
        """Evaluate subjective answer grading"""
        logger.info("Evaluating subjective grading...")
        
        from models.answer_evaluator import AnswerEvaluator
        evaluator = AnswerEvaluator()
        
        short_questions = [q for q in test_questions if q.get('question_type') == 'SHORT_ANSWER'][:30]
        desc_questions = [q for q in test_questions if q.get('question_type') == 'DESCRIPTIVE'][:20]
        
        results = {'SHORT_ANSWER': [], 'DESCRIPTIVE': []}
        
        # Test short answers
        for q in short_questions:
            expected = q.get('expected_answer', '')
            result = evaluator.evaluate_answer(q, expected)
            results['SHORT_ANSWER'].append(result.get('percentage', 0))
        
        # Test descriptive answers
        for q in desc_questions:
            expected = q.get('expected_answer', '')
            result = evaluator.evaluate_answer(q, expected)
            results['DESCRIPTIVE'].append(result.get('percentage', 0))
        
        return {
            'short_answer': {
                'tested': len(short_questions),
                'avg_score_for_correct': round(sum(results['SHORT_ANSWER']) / len(results['SHORT_ANSWER']), 2) if results['SHORT_ANSWER'] else 0
            },
            'descriptive': {
                'tested': len(desc_questions),
                'avg_score_for_correct': round(sum(results['DESCRIPTIVE']) / len(results['DESCRIPTIVE']), 2) if results['DESCRIPTIVE'] else 0
            }
        }
    
    def evaluate_keyword_extraction(self, test_pairs: List[Tuple]) -> Dict[str, Any]:
        """Evaluate keyword extraction accuracy"""
        logger.info("Evaluating keyword extraction...")
        
        from models.nlp_processor import NLPProcessor
        nlp = NLPProcessor()
        
        precision_scores = []
        recall_scores = []
        
        for lesson, _ in test_pairs[:30]:
            expected_topics = set(t.lower() for t in lesson.get('topics', []))
            content = lesson.get('content', '')
            
            extracted = set(k.lower() for k in nlp.extract_keywords(content, 10))
            
            # Calculate precision and recall
            if extracted:
                true_positives = len(expected_topics.intersection(extracted))
                precision = true_positives / len(extracted)
                precision_scores.append(precision)
            
            if expected_topics:
                true_positives = len(expected_topics.intersection(extracted))
                recall = true_positives / len(expected_topics)
                recall_scores.append(recall)
        
        avg_precision = sum(precision_scores) / len(precision_scores) * 100 if precision_scores else 0
        avg_recall = sum(recall_scores) / len(recall_scores) * 100 if recall_scores else 0
        f1 = 2 * avg_precision * avg_recall / (avg_precision + avg_recall) if (avg_precision + avg_recall) > 0 else 0
        
        return {
            'precision': round(avg_precision, 2),
            'recall': round(avg_recall, 2),
            'f1_score': round(f1, 2)
        }
    
    def _validate_question(self, question: Dict) -> bool:
        """Validate a generated question"""
        required_fields = ['question_type', 'question_text', 'marks']
        for field in required_fields:
            if field not in question or not question[field]:
                return False
        
        if question['question_type'] == 'MCQ':
            if 'options' not in question or len(question.get('options', [])) < 4:
                return False
            if 'correct_answer' not in question:
                return False
        
        return True
    
    def _calculate_overall_metrics(self) -> Dict[str, float]:
        """Calculate overall performance metrics"""
        metrics = {}
        
        if 'question_generation' in self.results:
            metrics['question_validity'] = self.results['question_generation']['validity_rate']
        
        if 'mcq_grading' in self.results:
            metrics['mcq_accuracy'] = self.results['mcq_grading']['accuracy']
        
        if 'keyword_extraction' in self.results:
            metrics['keyword_f1'] = self.results['keyword_extraction']['f1_score']
        
        metrics['overall_score'] = round(sum(metrics.values()) / len(metrics), 2) if metrics else 0
        
        return metrics
    
    def _save_results(self):
        """Save evaluation results"""
        output_file = self.model_dir / 'evaluation_results.json'
        with open(output_file, 'w') as f:
            json.dump(self.results, f, indent=2)
        logger.info(f"Results saved to {output_file}")


if __name__ == "__main__":
    evaluator = ModelEvaluator()
    results = evaluator.evaluate_all()
    
    print("\n" + "="*50)
    print("EVALUATION RESULTS")
    print("="*50)
    print(json.dumps(results, indent=2))

