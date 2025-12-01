"""
Main training and evaluation script for AI Homework Management System
"""
import os
import sys
import json
import logging
from pathlib import Path

# Add project root to path
PROJECT_ROOT = Path(__file__).resolve().parent
sys.path.insert(0, str(PROJECT_ROOT))

# Configure logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(name)s - %(levelname)s - %(message)s'
)
logger = logging.getLogger(__name__)


def print_banner():
    """Print banner"""
    banner = """
    ╔══════════════════════════════════════════════════════════════════╗
    ║     AI-Powered Homework Management and Performance Monitoring    ║
    ║                    Training and Evaluation Script                ║
    ╚══════════════════════════════════════════════════════════════════╝
    """
    print(banner)


def load_and_analyze_data():
    """Load and analyze the dataset"""
    from training.data_loader import DataLoader
    
    logger.info("Loading dataset...")
    loader = DataLoader()
    stats = loader.get_statistics()
    
    print("\n📊 Dataset Statistics:")
    print(f"   Total Lessons: {stats['total_lessons']}")
    print(f"   Total Questions: {stats['total_questions']}")
    
    print("\n   By Subject:")
    for subject, data in stats['by_subject'].items():
        print(f"     - {subject.title()}: {data['lessons']} lessons, {data['questions']} questions")
    
    print("\n   By Question Type:")
    for q_type, count in stats['by_question_type'].items():
        print(f"     - {q_type}: {count}")
    
    return loader


def train_models():
    """Train all models"""
    from training.train_models import ModelTrainer
    
    logger.info("Starting model training...")
    trainer = ModelTrainer()
    trainer.train_all()
    
    print("\n✅ Model training completed!")
    print(f"   Models saved to: {trainer.output_dir}")


def evaluate_models():
    """Evaluate trained models"""
    from training.evaluate_models import ModelEvaluator
    
    logger.info("Starting model evaluation...")
    evaluator = ModelEvaluator()
    results = evaluator.evaluate_all()
    
    print("\n📈 Evaluation Results:")
    
    if 'question_generation' in results:
        qg = results['question_generation']
        print(f"\n   Question Generation:")
        print(f"     - Validity Rate: {qg.get('validity_rate', 0)}%")
        print(f"     - By Type: {qg.get('by_type', {})}")
    
    if 'mcq_grading' in results:
        mcq = results['mcq_grading']
        print(f"\n   MCQ Grading:")
        print(f"     - Accuracy: {mcq.get('accuracy', 0)}%")
    
    if 'keyword_extraction' in results:
        kw = results['keyword_extraction']
        print(f"\n   Keyword Extraction:")
        print(f"     - Precision: {kw.get('precision', 0)}%")
        print(f"     - Recall: {kw.get('recall', 0)}%")
        print(f"     - F1 Score: {kw.get('f1_score', 0)}%")
    
    if 'overall' in results:
        print(f"\n   ⭐ Overall Score: {results['overall'].get('overall_score', 0)}%")
    
    return results


def run_tests():
    """Run unit tests"""
    import unittest
    from tests.test_models import (
        TestNLPProcessor,
        TestQuestionGenerator,
        TestAnswerEvaluator,
        TestDataLoader
    )
    
    logger.info("Running unit tests...")
    
    # Create test suite
    suite = unittest.TestSuite()
    suite.addTests(unittest.TestLoader().loadTestsFromTestCase(TestNLPProcessor))
    suite.addTests(unittest.TestLoader().loadTestsFromTestCase(TestQuestionGenerator))
    suite.addTests(unittest.TestLoader().loadTestsFromTestCase(TestAnswerEvaluator))
    suite.addTests(unittest.TestLoader().loadTestsFromTestCase(TestDataLoader))
    
    # Run tests
    runner = unittest.TextTestRunner(verbosity=2)
    result = runner.run(suite)
    
    print(f"\n📋 Test Results:")
    print(f"   Tests Run: {result.testsRun}")
    print(f"   Failures: {len(result.failures)}")
    print(f"   Errors: {len(result.errors)}")
    
    return result


def demo_question_generation():
    """Demo question generation"""
    from models.nlp_processor import NLPProcessor
    from models.question_generator import QuestionGenerator
    
    logger.info("Running question generation demo...")
    
    nlp = NLPProcessor()
    generator = QuestionGenerator(nlp)
    
    # Sample lesson data
    lesson = {
        'subject': 'science',
        'grade': 6,
        'unit': 'Living Things',
        'title': 'Introduction to Cells',
        'topics': ['cells', 'cell membrane', 'nucleus', 'cytoplasm'],
        'content': 'Cells are the basic units of life. Every living organism is made up of cells.',
        'difficulty': 'beginner'
    }
    
    print("\n🎯 Demo: Question Generation")
    print(f"   Lesson: {lesson['title']}")
    print(f"   Subject: {lesson['subject']}")
    
    questions = generator.generate_questions(lesson, num_mcq=2, num_short=1, num_descriptive=1)
    
    print(f"\n   Generated {len(questions)} questions:")
    for i, q in enumerate(questions, 1):
        print(f"\n   Q{i} [{q['question_type']}] ({q['marks']} marks)")
        print(f"      {q['question_text']}")
        if q['question_type'] == 'MCQ':
            for j, opt in enumerate(q.get('options', [])[:4]):
                print(f"      {chr(65+j)}. {opt}")


def main():
    """Main entry point"""
    print_banner()
    
    print("\n1️⃣  Loading and analyzing dataset...")
    loader = load_and_analyze_data()
    
    print("\n2️⃣  Training models...")
    train_models()
    
    print("\n3️⃣  Evaluating models...")
    results = evaluate_models()
    
    print("\n4️⃣  Running unit tests...")
    test_results = run_tests()
    
    print("\n5️⃣  Running demo...")
    demo_question_generation()
    
    print("\n" + "=" * 60)
    print("✅ Training and evaluation completed successfully!")
    print("=" * 60)
    
    print("\nNext steps:")
    print("  1. Start the Flask API: python app.py")
    print("  2. Run Laravel migrations: php artisan migrate")
    print("  3. Access the homework dashboard in the admin panel")


if __name__ == "__main__":
    main()

