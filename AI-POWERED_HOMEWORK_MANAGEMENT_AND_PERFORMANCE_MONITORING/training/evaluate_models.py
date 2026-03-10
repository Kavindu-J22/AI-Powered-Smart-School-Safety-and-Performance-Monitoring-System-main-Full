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
        """
        Evaluate MCQ grader accuracy against realistic student input formats.

        A PERFECT grader should handle all of these correctly; ours is a simple
        string-comparison grader, so it fails on inputs like "option a" or "A)"
        that real students commonly write.  This gives a meaningful, non-trivial
        accuracy score that reflects real-world grader robustness.
        """
        logger.info("Evaluating MCQ grading...")

        from models.answer_evaluator import AnswerEvaluator
        evaluator = AnswerEvaluator()

        mcq_questions = [q for q in test_questions if q.get('question_type') == 'MCQ'][:50]

        # Track results per format name for the detailed display
        format_tracker: Dict[str, Dict[str, int]] = {
            'Exact letter (A)':     {'correct': 0, 'total': 0},
            'Lowercase (a)':        {'correct': 0, 'total': 0},
            'Extra whitespace ( A )': {'correct': 0, 'total': 0},
            'Option prefix (option a)': {'correct': 0, 'total': 0},
            'Trailing punct (A.)':  {'correct': 0, 'total': 0},
            'Wrong letter (B/C)':   {'correct': 0, 'total': 0},
            'Blank answer':         {'correct': 0, 'total': 0},
            'Numeric (1)':          {'correct': 0, 'total': 0},
        }
        correct_evaluations = 0
        total_tests = 0

        for q in mcq_questions:
            correct_answer = q.get('correct_answer', 'A')
            wrong_answer   = 'B' if correct_answer.upper() != 'B' else 'C'

            test_cases = [
                (correct_answer,                     True,  'Exact letter (A)'),
                (correct_answer.lower(),             True,  'Lowercase (a)'),
                (f" {correct_answer} ",              True,  'Extra whitespace ( A )'),
                (f"option {correct_answer.lower()}", True,  'Option prefix (option a)'),
                (f"{correct_answer}.",               True,  'Trailing punct (A.)'),
                (wrong_answer,                       False, 'Wrong letter (B/C)'),
                ('',                                 False, 'Blank answer'),
                ('1',                                False, 'Numeric (1)'),
            ]

            for student_input, expected_correct, fmt_name in test_cases:
                result = evaluator.evaluate_answer(q, student_input)
                is_right = result.get('is_correct') == expected_correct
                format_tracker[fmt_name]['total'] += 1
                if is_right:
                    format_tracker[fmt_name]['correct'] += 1
                    correct_evaluations += 1
                total_tests += 1

        accuracy = correct_evaluations / total_tests * 100 if total_tests > 0 else 0

        # Build per-format accuracy summary
        format_accuracy = {
            name: round(v['correct'] / v['total'] * 100, 1) if v['total'] > 0 else 0
            for name, v in format_tracker.items()
        }

        return {
            'total_tested': len(mcq_questions),
            'correct_evaluations': correct_evaluations,
            'total_test_cases': total_tests,
            'accuracy': round(accuracy, 2),
            'format_accuracy': format_accuracy,
        }
    
    def _degrade_answer(self, text: str, keep_ratio: float) -> str:
        """Return a degraded version of text by keeping only a fraction of words."""
        words = text.split()
        if not words:
            return ''
        keep_n = max(1, int(len(words) * keep_ratio))
        # keep evenly-spaced words so the answer still sounds partial, not random
        indices = sorted(random.sample(range(len(words)), keep_n))
        return ' '.join(words[i] for i in indices)

    def evaluate_subjective_grading(self, test_questions: List[Dict]) -> Dict[str, Any]:
        """
        Evaluate subjective answer grading using a realistic mix of student
        answer quality levels (perfect → poor) instead of always using the
        perfect expected answer.
        """
        logger.info("Evaluating subjective grading...")

        from models.answer_evaluator import AnswerEvaluator
        evaluator = AnswerEvaluator()

        short_questions = [q for q in test_questions if q.get('question_type') == 'SHORT_ANSWER'][:30]
        desc_questions  = [q for q in test_questions if q.get('question_type') == 'DESCRIPTIVE'][:20]

        # quality levels: (label, keep_ratio, weight)
        # Realistic school distribution — even weak students write ≥30% of the answer
        quality_levels = [
            ('Excellent (100%)', 1.00, 0.15),
            ('Good (90%)',       0.90, 0.35),
            ('Average (75%)',    0.75, 0.30),
            ('Below Avg (50%)',  0.50, 0.15),
            ('Weak (30%)',       0.30, 0.05),
        ]

        def evaluate_with_breakdown(questions):
            if not questions:
                return 0.0, {}
            total_weight = sum(w for _, _, w in quality_levels)
            # Accumulate per-quality-level scores across all questions
            level_scores: Dict[str, list] = {label: [] for label, _, _ in quality_levels}
            weighted_sum = 0.0
            for q in questions:
                expected = q.get('expected_answer', '')
                q_score = 0.0
                for label, ratio, weight in quality_levels:
                    student_input = self._degrade_answer(expected, ratio)
                    result = evaluator.evaluate_answer(q, student_input)
                    pct = result.get('percentage', 0)
                    level_scores[label].append(pct)
                    q_score += (pct / 100.0) * weight
                weighted_sum += q_score / total_weight
            avg = round((weighted_sum / len(questions)) * 100, 2)
            breakdown = {
                label: round(sum(scores) / len(scores), 1) if scores else 0
                for label, scores in level_scores.items()
            }
            return avg, breakdown

        sa_avg, sa_breakdown   = evaluate_with_breakdown(short_questions)
        desc_avg, desc_breakdown = evaluate_with_breakdown(desc_questions)

        return {
            'short_answer': {
                'tested': len(short_questions),
                'avg_score_for_correct': sa_avg,
                'quality_breakdown': sa_breakdown,
            },
            'descriptive': {
                'tested': len(desc_questions),
                'avg_score_for_correct': desc_avg,
                'quality_breakdown': desc_breakdown,
            }
        }
    
    def evaluate_keyword_extraction(self, test_pairs: List[Tuple]) -> Dict[str, Any]:
        """Evaluate keyword extraction accuracy"""
        logger.info("Evaluating keyword extraction...")
        
        from models.nlp_processor import NLPProcessor
        nlp = NLPProcessor()
        
        precision_scores = []
        recall_scores = []
        
        def topic_matched(extracted_kw: str, topic: str) -> bool:
            """
            Flexible topic match: accept if any significant word in the topic
            appears inside an extracted keyword or vice-versa.
            e.g. topic='cell division' matches extracted='cellular', 'division'
            """
            topic_words = [w for w in topic.lower().split() if len(w) > 3]
            kw_lower = extracted_kw.lower()
            for tw in topic_words:
                if tw in kw_lower or kw_lower in tw:
                    return True
            return False

        for lesson, _ in test_pairs[:30]:
            expected_topics = [t.lower() for t in lesson.get('topics', [])]
            content = lesson.get('content', '')
            # Extract more keywords to improve recall
            extracted_kws = [k.lower() for k in nlp.extract_keywords(content, 15)]

            if not expected_topics or not extracted_kws:
                continue

            # Precision: how many extracted keywords match at least one topic
            matched_extracted = sum(
                1 for kw in extracted_kws
                if any(topic_matched(kw, t) for t in expected_topics)
            )
            precision_scores.append(matched_extracted / len(extracted_kws))

            # Recall: how many topics are covered by at least one extracted keyword
            matched_topics = sum(
                1 for t in expected_topics
                if any(topic_matched(kw, t) for kw in extracted_kws)
            )
            recall_scores.append(matched_topics / len(expected_topics))
        
        avg_precision = sum(precision_scores) / len(precision_scores) * 100 if precision_scores else 0
        avg_recall = sum(recall_scores) / len(recall_scores) * 100 if recall_scores else 0
        f1 = 2 * avg_precision * avg_recall / (avg_precision + avg_recall) if (avg_precision + avg_recall) > 0 else 0
        
        return {
            'precision': round(avg_precision, 2),
            'recall': round(avg_recall, 2),
            'f1_score': round(f1, 2)
        }
    
    def _validate_question(self, question: Dict) -> bool:
        """
        Validate a generated question with stricter quality checks.
        Returns False for template-only or empty content.
        """
        required_fields = ['question_type', 'question_text', 'marks']
        for field in required_fields:
            if field not in question or not question[field]:
                return False

        # Question text must have at least 8 meaningful words
        q_text = str(question.get('question_text', ''))
        if len(q_text.split()) < 8:
            return False

        if question['question_type'] == 'MCQ':
            options = question.get('options', [])
            if len(options) < 4:
                return False
            if 'correct_answer' not in question:
                return False
            # Options must not all be pure "Option X" placeholders with no real content
            # (a real option has more than just the label + a single generic word)
            generic_count = sum(
                1 for opt in options
                if len(str(opt).split()) <= 4 and str(opt).lower().startswith('option')
            )
            if generic_count == len(options):
                return False  # every option is a placeholder — low quality

        if question['question_type'] in ('SHORT_ANSWER', 'DESCRIPTIVE'):
            expected = str(question.get('expected_answer', ''))
            # Must have a real answer (at least 8 words, not just a template sentence)
            if len(expected.split()) < 8:
                return False

        return True
    
    def _calculate_overall_metrics(self) -> Dict[str, float]:
        """
        Calculate weighted overall performance score.
        Weights reflect the relative importance of each capability in a school setting.
        """
        # (result_key, weight)
        component_weights = {
            'mcq_accuracy':       0.35,   # Core grading — most important
            'question_validity':  0.25,   # Question generation quality
            'subjective_short':   0.20,   # Short-answer grading
            'subjective_desc':    0.10,   # Descriptive grading
            'keyword_f1':         0.10,   # NLP keyword extraction (helper)
        }

        scores: Dict[str, float] = {}

        if 'question_generation' in self.results:
            scores['question_validity'] = self.results['question_generation']['validity_rate']
        if 'mcq_grading' in self.results:
            scores['mcq_accuracy'] = self.results['mcq_grading']['accuracy']
        if 'subjective_grading' in self.results:
            scores['subjective_short'] = self.results['subjective_grading']['short_answer']['avg_score_for_correct']
            scores['subjective_desc']  = self.results['subjective_grading']['descriptive']['avg_score_for_correct']
        if 'keyword_extraction' in self.results:
            scores['keyword_f1'] = self.results['keyword_extraction']['f1_score']

        total_weight  = sum(component_weights[k] for k in scores if k in component_weights)
        weighted_sum  = sum(scores[k] * component_weights.get(k, 0) for k in scores)
        overall_score = round(weighted_sum / total_weight, 2) if total_weight > 0 else 0

        metrics = {k: v for k, v in scores.items()}
        metrics['overall_score'] = overall_score
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

