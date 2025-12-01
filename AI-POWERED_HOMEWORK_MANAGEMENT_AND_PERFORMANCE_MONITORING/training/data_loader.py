"""
Data Loader for Sri Lankan Curriculum Dataset
Loads lessons and questions for model training
"""
import json
import os
from pathlib import Path
from typing import List, Dict, Any, Tuple
import logging

logger = logging.getLogger(__name__)

class DataLoader:
    """
    Loads and processes the Sri Lankan curriculum dataset
    for training the question generation and evaluation models.
    """
    
    def __init__(self, data_dir: str = None):
        if data_dir is None:
            self.data_dir = Path(__file__).parent.parent / "datasets" / "raw" / "srilanka_syllabus"
        else:
            self.data_dir = Path(data_dir)
        
        self.subjects = ['science', 'history', 'english', 'health_science']
        self.grades = [6, 7, 8, 9, 10, 11]
    
    def load_all_lessons(self) -> List[Dict[str, Any]]:
        """Load all lessons from all subjects and grades"""
        all_lessons = []
        
        for subject in self.subjects:
            for grade in self.grades:
                lessons = self.load_lessons(subject, grade)
                all_lessons.extend(lessons)
        
        logger.info(f"Loaded {len(all_lessons)} lessons total")
        return all_lessons
    
    def load_lessons(self, subject: str, grade: int) -> List[Dict[str, Any]]:
        """Load lessons for a specific subject and grade"""
        lessons_file = self.data_dir / "lessons" / subject / f"grade_{grade}" / "lessons.jsonl"
        
        if not lessons_file.exists():
            logger.warning(f"Lessons file not found: {lessons_file}")
            return []
        
        lessons = []
        with open(lessons_file, 'r', encoding='utf-8') as f:
            for line in f:
                if line.strip():
                    lesson = json.loads(line)
                    lessons.append(lesson)
        
        return lessons
    
    def load_all_questions(self) -> List[Dict[str, Any]]:
        """Load all questions from all subjects and grades"""
        all_questions = []
        
        for subject in self.subjects:
            for grade in self.grades:
                questions = self.load_questions(subject, grade)
                all_questions.extend(questions)
        
        logger.info(f"Loaded {len(all_questions)} questions total")
        return all_questions
    
    def load_questions(self, subject: str, grade: int) -> List[Dict[str, Any]]:
        """Load questions for a specific subject and grade"""
        questions_file = self.data_dir / "questions" / subject / f"grade_{grade}" / "questions.jsonl"
        
        if not questions_file.exists():
            logger.warning(f"Questions file not found: {questions_file}")
            return []
        
        questions = []
        with open(questions_file, 'r', encoding='utf-8') as f:
            for line in f:
                if line.strip():
                    question = json.loads(line)
                    questions.append(question)
        
        return questions
    
    def get_training_pairs(self) -> List[Tuple[Dict, List[Dict]]]:
        """
        Get lesson-question pairs for training.
        Returns list of (lesson, [questions]) tuples.
        """
        pairs = []
        
        for subject in self.subjects:
            for grade in self.grades:
                lessons = self.load_lessons(subject, grade)
                questions = self.load_questions(subject, grade)
                
                # Group questions by unit
                questions_by_unit = {}
                for q in questions:
                    unit = q.get('unit', '')
                    if unit not in questions_by_unit:
                        questions_by_unit[unit] = []
                    questions_by_unit[unit].append(q)
                
                # Create pairs
                for lesson in lessons:
                    unit = lesson.get('unit', '')
                    if unit in questions_by_unit:
                        pairs.append((lesson, questions_by_unit[unit]))
        
        logger.info(f"Created {len(pairs)} lesson-question pairs")
        return pairs
    
    def get_statistics(self) -> Dict[str, Any]:
        """Get dataset statistics"""
        stats = {
            'total_lessons': 0,
            'total_questions': 0,
            'by_subject': {},
            'by_grade': {},
            'by_question_type': {'MCQ': 0, 'SHORT_ANSWER': 0, 'DESCRIPTIVE': 0}
        }
        
        for subject in self.subjects:
            stats['by_subject'][subject] = {'lessons': 0, 'questions': 0}
            
            for grade in self.grades:
                if grade not in stats['by_grade']:
                    stats['by_grade'][grade] = {'lessons': 0, 'questions': 0}
                
                lessons = self.load_lessons(subject, grade)
                questions = self.load_questions(subject, grade)
                
                stats['total_lessons'] += len(lessons)
                stats['total_questions'] += len(questions)
                stats['by_subject'][subject]['lessons'] += len(lessons)
                stats['by_subject'][subject]['questions'] += len(questions)
                stats['by_grade'][grade]['lessons'] += len(lessons)
                stats['by_grade'][grade]['questions'] += len(questions)
                
                for q in questions:
                    q_type = q.get('question_type', 'MCQ')
                    if q_type in stats['by_question_type']:
                        stats['by_question_type'][q_type] += 1
        
        return stats


if __name__ == "__main__":
    # Test the data loader
    loader = DataLoader()
    stats = loader.get_statistics()
    print("\nDataset Statistics:")
    print(f"Total Lessons: {stats['total_lessons']}")
    print(f"Total Questions: {stats['total_questions']}")
    print("\nBy Subject:")
    for subject, data in stats['by_subject'].items():
        print(f"  {subject}: {data['lessons']} lessons, {data['questions']} questions")
    print("\nBy Question Type:")
    for q_type, count in stats['by_question_type'].items():
        print(f"  {q_type}: {count}")

