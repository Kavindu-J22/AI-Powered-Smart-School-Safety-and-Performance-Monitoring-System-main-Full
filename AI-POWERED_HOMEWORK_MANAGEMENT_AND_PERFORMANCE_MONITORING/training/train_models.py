"""
Model Training Script
Trains the question generation and answer evaluation models
"""
import os
import sys
import json
import logging
from pathlib import Path
from typing import List, Dict, Any, Tuple
import pickle
from datetime import datetime

# Add project root to path
PROJECT_ROOT = Path(__file__).resolve().parent.parent
sys.path.insert(0, str(PROJECT_ROOT))

from training.data_loader import DataLoader

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


class ModelTrainer:
    """
    Trainer for the homework management AI models.
    Trains:
    1. Question Generation patterns
    2. Answer Evaluation similarity model
    3. Keyword extraction model
    """
    
    def __init__(self, output_dir: str = None):
        if output_dir is None:
            self.output_dir = PROJECT_ROOT / "models" / "saved"
        else:
            self.output_dir = Path(output_dir)
        
        self.output_dir.mkdir(parents=True, exist_ok=True)
        self.data_loader = DataLoader()
    
    def train_all(self):
        """Train all models"""
        logger.info("Starting model training...")
        
        # Load training data
        lessons = self.data_loader.load_all_lessons()
        questions = self.data_loader.load_all_questions()
        pairs = self.data_loader.get_training_pairs()
        
        logger.info(f"Loaded {len(lessons)} lessons, {len(questions)} questions")
        
        # Train models
        self.train_keyword_extractor(lessons)
        self.train_question_templates(pairs)
        self.train_answer_patterns(questions)
        
        # Save training metadata
        self._save_training_metadata(lessons, questions)
        
        logger.info("Training completed successfully!")
    
    def train_keyword_extractor(self, lessons: List[Dict]):
        """Train keyword extraction patterns from lessons"""
        logger.info("Training keyword extractor...")
        
        # Extract vocabulary and term frequencies
        vocabulary = {}
        topic_keywords = {}
        
        for lesson in lessons:
            subject = lesson.get('subject', '')
            topics = lesson.get('topics', [])
            content = lesson.get('content', '')
            
            # Build subject-specific vocabulary
            if subject not in topic_keywords:
                topic_keywords[subject] = set()
            
            for topic in topics:
                topic_keywords[subject].add(topic.lower())
                
                # Extract words from topic
                words = topic.lower().split()
                for word in words:
                    if len(word) > 3:
                        vocabulary[word] = vocabulary.get(word, 0) + 1
        
        # Save keyword data
        keyword_data = {
            'vocabulary': vocabulary,
            'topic_keywords': {k: list(v) for k, v in topic_keywords.items()},
            'trained_at': datetime.now().isoformat()
        }
        
        with open(self.output_dir / 'keyword_data.json', 'w') as f:
            json.dump(keyword_data, f, indent=2)
        
        logger.info(f"Saved keyword data with {len(vocabulary)} vocabulary terms")
    
    def train_question_templates(self, pairs: List[Tuple[Dict, List[Dict]]]):
        """Learn question patterns from lesson-question pairs"""
        logger.info("Training question templates...")
        
        templates = {
            'MCQ': [],
            'SHORT_ANSWER': [],
            'DESCRIPTIVE': []
        }
        
        for lesson, questions in pairs:
            for q in questions:
                q_type = q.get('question_type', 'MCQ')
                q_text = q.get('question_text', '')
                topic = q.get('topic', '')
                unit = q.get('unit', '')
                
                # Create template by replacing topic/unit with placeholders
                template = q_text.replace(topic, '{topic}').replace(unit, '{unit}')
                
                if template not in templates.get(q_type, []):
                    templates[q_type].append({
                        'template': template,
                        'original': q_text,
                        'topic': topic,
                        'unit': unit,
                        'difficulty': q.get('difficulty', 'beginner'),
                        'bloom_level': q.get('bloom_level', 'remember')
                    })
        
        # Save templates
        with open(self.output_dir / 'question_templates.json', 'w') as f:
            json.dump(templates, f, indent=2)
        
        logger.info(f"Saved {sum(len(v) for v in templates.values())} question templates")
    
    def train_answer_patterns(self, questions: List[Dict]):
        """Learn answer patterns for evaluation"""
        logger.info("Training answer patterns...")
        
        answer_patterns = {
            'MCQ': [],
            'SHORT_ANSWER': [],
            'DESCRIPTIVE': []
        }
        
        for q in questions:
            q_type = q.get('question_type', 'MCQ')
            
            if q_type == 'MCQ':
                pattern = {
                    'options_count': len(q.get('options', [])),
                    'correct_answer': q.get('correct_answer', 'A')
                }
            elif q_type == 'SHORT_ANSWER':
                pattern = {
                    'expected_answer': q.get('expected_answer', ''),
                    'key_points': q.get('key_points', []),
                    'marks': q.get('marks', 3)
                }
            else:  # DESCRIPTIVE
                pattern = {
                    'expected_answer': q.get('expected_answer', ''),
                    'key_points': q.get('key_points', []),
                    'marks': q.get('marks', 5),
                    'bloom_level': q.get('bloom_level', 'analyze')
                }
            
            answer_patterns[q_type].append(pattern)
        
        with open(self.output_dir / 'answer_patterns.json', 'w') as f:
            json.dump(answer_patterns, f, indent=2)
        
        logger.info(f"Saved answer patterns for {len(questions)} questions")
    
    def _save_training_metadata(self, lessons: List[Dict], questions: List[Dict]):
        """Save training metadata"""
        metadata = {
            'trained_at': datetime.now().isoformat(),
            'total_lessons': len(lessons),
            'total_questions': len(questions),
            'subjects': list(set(l.get('subject', '') for l in lessons)),
            'grades': list(set(l.get('grade', 0) for l in lessons)),
            'question_types': list(set(q.get('question_type', '') for q in questions))
        }
        
        with open(self.output_dir / 'training_metadata.json', 'w') as f:
            json.dump(metadata, f, indent=2)


if __name__ == "__main__":
    trainer = ModelTrainer()
    trainer.train_all()

