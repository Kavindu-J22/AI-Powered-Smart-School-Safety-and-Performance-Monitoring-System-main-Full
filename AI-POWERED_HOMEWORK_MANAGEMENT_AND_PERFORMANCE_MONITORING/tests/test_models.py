"""
Unit tests for AI Homework Management Models
"""
import os
import sys
import unittest
from pathlib import Path

# Add project root to path
PROJECT_ROOT = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(PROJECT_ROOT))


class TestNLPProcessor(unittest.TestCase):
    """Test cases for NLP Processor"""
    
    @classmethod
    def setUpClass(cls):
        from models.nlp_processor import NLPProcessor
        cls.nlp = NLPProcessor()
    
    def test_extract_keywords(self):
        """Test keyword extraction from text"""
        text = "Photosynthesis is the process by which plants convert sunlight into energy. Plants use chlorophyll to capture light energy."
        keywords = self.nlp.extract_keywords(text, max_keywords=5)
        
        self.assertIsInstance(keywords, list)
        self.assertGreater(len(keywords), 0)
        self.assertLessEqual(len(keywords), 5)
    
    def test_extract_keywords_empty_text(self):
        """Test keyword extraction with empty text"""
        keywords = self.nlp.extract_keywords("", max_keywords=5)
        self.assertEqual(keywords, [])
    
    def test_parse_lesson(self):
        """Test lesson parsing"""
        lesson_data = {
            'subject': 'science',
            'grade': 6,
            'unit': 'Photosynthesis',
            'title': 'Introduction to Photosynthesis',
            'topics': ['photosynthesis', 'plants', 'chlorophyll'],
            'content': 'Plants use sunlight to make food through photosynthesis.',
            'difficulty': 'beginner'
        }
        
        parsed = self.nlp.parse_lesson(lesson_data)
        
        self.assertEqual(parsed['subject'], 'science')
        self.assertEqual(parsed['grade'], 6)
        self.assertIn('keywords', parsed)
        self.assertIn('concepts', parsed)
    
    def test_calculate_similarity(self):
        """Test text similarity calculation"""
        text1 = "The sun provides energy for plants to grow"
        text2 = "Plants need sunlight energy to grow and develop"
        text3 = "Computers are electronic devices"
        
        sim_similar = self.nlp.calculate_similarity(text1, text2)
        sim_different = self.nlp.calculate_similarity(text1, text3)
        
        self.assertGreater(sim_similar, sim_different)


class TestQuestionGenerator(unittest.TestCase):
    """Test cases for Question Generator"""
    
    @classmethod
    def setUpClass(cls):
        from models.question_generator import QuestionGenerator
        from models.nlp_processor import NLPProcessor
        cls.generator = QuestionGenerator(NLPProcessor())
    
    def test_generate_questions(self):
        """Test question generation from lesson data"""
        lesson_data = {
            'subject': 'science',
            'grade': 6,
            'unit': 'Photosynthesis',
            'topics': ['photosynthesis', 'chlorophyll'],
            'difficulty': 'beginner'
        }
        
        questions = self.generator.generate_questions(
            lesson_data, 
            num_mcq=2, 
            num_short=1, 
            num_descriptive=1
        )
        
        self.assertIsInstance(questions, list)
        self.assertEqual(len(questions), 4)  # 2 MCQ + 1 Short + 1 Descriptive
    
    def test_question_types(self):
        """Test that correct question types are generated"""
        lesson_data = {
            'subject': 'history',
            'grade': 8,
            'unit': 'Ancient Civilizations',
            'topics': ['Mesopotamia', 'Egypt'],
            'difficulty': 'intermediate'
        }
        
        questions = self.generator.generate_questions(lesson_data, 1, 1, 1)
        
        types = [q['question_type'] for q in questions]
        self.assertIn('MCQ', types)
        self.assertIn('SHORT_ANSWER', types)
        self.assertIn('DESCRIPTIVE', types)
    
    def test_mcq_has_options(self):
        """Test that MCQ questions have options"""
        lesson_data = {
            'subject': 'english',
            'grade': 7,
            'unit': 'Grammar',
            'topics': ['nouns', 'verbs'],
            'difficulty': 'beginner'
        }
        
        questions = self.generator.generate_questions(lesson_data, num_mcq=1, num_short=0, num_descriptive=0)
        mcq = questions[0]
        
        self.assertEqual(mcq['question_type'], 'MCQ')
        self.assertIn('options', mcq)
        self.assertEqual(len(mcq['options']), 4)
        self.assertIn('correct_answer', mcq)


class TestAnswerEvaluator(unittest.TestCase):
    """Test cases for Answer Evaluator"""
    
    @classmethod
    def setUpClass(cls):
        from models.answer_evaluator import AnswerEvaluator
        cls.evaluator = AnswerEvaluator()
    
    def test_evaluate_mcq_correct(self):
        """Test MCQ evaluation with correct answer"""
        question = {
            'question_type': 'MCQ',
            'question_text': 'What is the capital of Sri Lanka?',
            'options': ['Colombo', 'Kandy', 'Galle', 'Jaffna'],
            'correct_answer': 'A',
            'marks': 1
        }
        
        result = self.evaluator.evaluate_answer(question, 'A')
        
        self.assertTrue(result['is_correct'])
        self.assertEqual(result['marks_obtained'], 1)
        self.assertEqual(result['percentage'], 100.0)
    
    def test_evaluate_mcq_incorrect(self):
        """Test MCQ evaluation with incorrect answer"""
        question = {
            'question_type': 'MCQ',
            'question_text': 'What is 2 + 2?',
            'options': ['3', '4', '5', '6'],
            'correct_answer': 'B',
            'marks': 1
        }
        
        result = self.evaluator.evaluate_answer(question, 'A')
        
        self.assertFalse(result['is_correct'])
        self.assertEqual(result['marks_obtained'], 0)
    
    def test_evaluate_short_answer(self):
        """Test short answer evaluation"""
        question = {
            'question_type': 'SHORT_ANSWER',
            'question_text': 'Explain photosynthesis',
            'expected_answer': 'Photosynthesis is the process by which plants convert sunlight into energy using chlorophyll.',
            'key_points': ['sunlight', 'plants', 'energy', 'chlorophyll'],
            'marks': 3
        }
        
        # Good answer
        good_answer = "Plants use sunlight to make energy through photosynthesis. Chlorophyll helps capture light."
        result = self.evaluator.evaluate_answer(question, good_answer)
        
        self.assertIn('marks_obtained', result)
        self.assertIn('percentage', result)
        self.assertIn('feedback', result)
    
    def test_evaluate_descriptive(self):
        """Test descriptive answer evaluation"""
        question = {
            'question_type': 'DESCRIPTIVE',
            'question_text': 'Discuss the importance of education',
            'expected_answer': 'Education is crucial for personal development, career opportunities, and societal progress.',
            'key_points': ['personal development', 'career', 'society', 'knowledge'],
            'marks': 5
        }
        
        answer = "Education plays a vital role in personal development. It opens career opportunities and contributes to societal growth. Through education, people gain knowledge and skills."
        result = self.evaluator.evaluate_answer(question, answer)
        
        self.assertIn('detailed_scores', result)
        self.assertIn('improvement_suggestions', result)


class TestDataLoader(unittest.TestCase):
    """Test cases for Data Loader"""
    
    def test_data_loader_initialization(self):
        """Test data loader can be initialized"""
        from training.data_loader import DataLoader
        loader = DataLoader()
        self.assertIsNotNone(loader)
    
    def test_get_statistics(self):
        """Test getting dataset statistics"""
        from training.data_loader import DataLoader
        loader = DataLoader()
        stats = loader.get_statistics()
        
        self.assertIn('total_lessons', stats)
        self.assertIn('total_questions', stats)
        self.assertIn('by_subject', stats)


if __name__ == '__main__':
    print("=" * 60)
    print("AI Homework Management System - Unit Tests")
    print("=" * 60)
    
    # Run tests
    unittest.main(verbosity=2)

