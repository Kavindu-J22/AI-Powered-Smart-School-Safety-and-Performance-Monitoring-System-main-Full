"""
Dataset Generation Script for Homework Management ML Service
This script generates educational datasets in JSON/JSONL format from curriculum data.

"""

import json
import os
from pathlib import Path
from datetime import datetime
from typing import Dict, List, Any
import random


class DatasetGenerator:
    """
    Generates educational datasets in JSON format for the Homework Management ML Service.
    Supports multiple subjects, grades, and question types.
    """
    
    def __init__(self, output_dir: str = "datasets/raw/srilanka_syllabus"):
        self.output_dir = Path(output_dir)
        self.subjects = ['science', 'history', 'english', 'health_science']
        self.grades = [6, 7, 8, 9, 10, 11]
        
        # Question templates for different types
        self.mcq_templates = [
            "What is the correct usage of {topic}?",
            "Which of the following is an example of {topic}?",
            "Identify the {topic} in the following sentence:",
            "In {unit}, {topic} refers to:",
            "What happens when {topic} occurs?",
            "Which of the following best describes {topic}?",
            "What is the primary function of {topic}?",
            "What was the main cause of {topic}?",
        ]
        
        self.short_answer_templates = [
            "Explain the concept of {topic} in the context of {unit}.",
            "Describe how {topic} relates to {unit}.",
            "What are the key characteristics of {topic}?",
            "Discuss the importance of {topic} in {unit}.",
        ]
        
        self.descriptive_templates = [
            "Analyze the role of {topic} in {unit} and provide examples.",
            "Compare and contrast different aspects of {topic}.",
            "Evaluate the significance of {topic} in modern context.",
            "Critically examine how {topic} impacts {unit}.",
        ]
    
    def generate_lesson(self, subject: str, grade: int, unit: str, 
                       topics: List[str]) -> Dict[str, Any]:
        """Generate a lesson in JSON format"""
        
        difficulty_map = {6: "beginner", 7: "beginner", 8: "intermediate", 
                         9: "intermediate", 10: "advanced", 11: "advanced"}
        
        lesson = {
            "subject": subject,
            "grade": grade,
            "unit": unit,
            "title": f"{unit} - Grade {grade}",
            "topics": topics,
            "content": self._generate_lesson_content(unit, topics, subject, grade),
            "difficulty": difficulty_map.get(grade, "beginner"),
            "duration_minutes": 60,
            "learning_outcomes": [
                f"Understand {topics[0]}",
                f"Apply knowledge of {topics[1] if len(topics) > 1 else topics[0]}",
                f"Analyze concepts related to {unit}"
            ]
        }
        
        return lesson
    
    def _generate_lesson_content(self, unit: str, topics: List[str], 
                                 subject: str, grade: int) -> str:
        """Generate lesson content in markdown format"""
        topics_str = ", ".join(topics)
        
        content = f"""
# {unit} - Grade {grade}

## Learning Objectives
By the end of this lesson, students will be able to:
- Understand the fundamental concepts of {topics_str}
- Apply scientific principles to real-world situations
- Analyze and interpret scientific data related to {unit}

## Introduction
This lesson explores {unit}, focusing on {topics_str}. Students will learn through hands-on activities,
demonstrations, and practical applications relevant to Sri Lankan context.

## Main Content
This section covers {topics_str}. Students will explore each topic through theoretical understanding and practical application.

## Key Concepts
{", ".join(topics[:3])}

## Activities
1. Laboratory experiment demonstrating {topics[0][0]}
2. Group discussion on applications in Sri Lanka
3. Problem-solving exercises
4. Real-world case studies

## Assessment
Students will be assessed through practical work, written tests, and project presentations.

## Resources
- Textbook: {subject.title()} for Grade {grade} (Sri Lankan Curriculum)
- Laboratory equipment
- Digital resources and simulations
"""
        return content
    
    def generate_question(self, question_type: str, topic: str, unit: str,
                         subject: str, grade: int) -> Dict[str, Any]:
        """Generate a question in JSON format"""
        
        difficulty_map = {6: "beginner", 7: "beginner", 8: "intermediate",
                         9: "intermediate", 10: "advanced", 11: "advanced"}
        
        if question_type == "MCQ":
            template = random.choice(self.mcq_templates)
            question_text = template.format(topic=topic, unit=unit)
            
            question = {
                "question_type": "MCQ",
                "question_text": question_text,
                "options": [
                    f"Option A related to {topic}",
                    f"Option B related to {topic}",
                    f"Option C related to {topic}",
                    f"Option D related to {topic}"
                ],
                "correct_answer": "A",
                "explanation": f"This is correct because {topic} functions in this way according to {unit} principles.",
                "difficulty": difficulty_map.get(grade, "beginner"),
                "bloom_level": "remember",
                "marks": 1,
                "subject": subject,
                "grade": grade,
                "unit": unit,
                "topic": topic
            }

        elif question_type == "SHORT_ANSWER":
            template = random.choice(self.short_answer_templates)
            question_text = template.format(topic=topic, unit=unit)

            question = {
                "question_type": "SHORT_ANSWER",
                "question_text": question_text,
                "expected_answer": f"A comprehensive explanation of {topic} including its key aspects.",
                "key_points": [
                    f"Definition of {topic}",
                    f"Relationship to {unit}",
                    "Practical application"
                ],
                "difficulty": difficulty_map.get(grade, "beginner"),
                "bloom_level": "understand",
                "marks": 3,
                "subject": subject,
                "grade": grade,
                "unit": unit,
                "topic": topic
            }

        else:  # DESCRIPTIVE
            template = random.choice(self.descriptive_templates)
            question_text = template.format(topic=topic, unit=unit)

            question = {
                "question_type": "DESCRIPTIVE",
                "question_text": question_text,
                "expected_answer": f"A detailed analysis of {topic} with examples and critical evaluation.",
                "key_points": [
                    f"Theoretical understanding of {topic}",
                    f"Practical applications in {unit}",
                    "Critical analysis and evaluation",
                    "Real-world examples"
                ],
                "difficulty": difficulty_map.get(grade, "beginner"),
                "bloom_level": "analyze",
                "marks": 5,
                "subject": subject,
                "grade": grade,
                "unit": unit,
                "topic": topic
            }

        return question

    def save_to_jsonl(self, data: List[Dict], filepath: Path):
        """Save data to JSONL format (one JSON object per line)"""
        filepath.parent.mkdir(parents=True, exist_ok=True)

        with open(filepath, 'w', encoding='utf-8') as f:
            for item in data:
                f.write(json.dumps(item, ensure_ascii=False) + '\n')

        print(f"✓ Saved {len(data)} items to {filepath}")

    def save_to_json(self, data: Any, filepath: Path):
        """Save data to JSON format"""
        filepath.parent.mkdir(parents=True, exist_ok=True)

        with open(filepath, 'w', encoding='utf-8') as f:
            json.dump(data, f, indent=2, ensure_ascii=False)

        print(f"✓ Saved data to {filepath}")

    def generate_complete_dataset(self, curriculum_data: Dict[str, Dict[str, List[str]]]):
        """
        Generate complete dataset from curriculum data

        Args:
            curriculum_data: Dictionary with structure:
                {
                    'subject_name': {
                        'unit_name': ['topic1', 'topic2', ...],
                        ...
                    },
                    ...
                }
        """

        stats = {
            "total_lessons": 0,
            "total_questions": 0,
            "by_subject": {}
        }

        for subject in self.subjects:
            if subject not in curriculum_data:
                print(f"⚠ Warning: No data for subject '{subject}'")
                continue

            stats["by_subject"][subject] = {"lessons": 0, "questions": 0}

            for grade in self.grades:
                lessons = []
                questions = []

                # Generate lessons and questions for each unit
                for unit, topics in curriculum_data[subject].items():
                    # Generate lesson
                    lesson = self.generate_lesson(subject, grade, unit, topics)
                    lessons.append(lesson)
                    stats["total_lessons"] += 1
                    stats["by_subject"][subject]["lessons"] += 1

                    # Generate questions for each topic (10 questions per topic)
                    for topic in topics:
                        for _ in range(10):
                            q_type = random.choice(["MCQ", "SHORT_ANSWER", "DESCRIPTIVE"])
                            question = self.generate_question(q_type, topic, unit, subject, grade)
                            questions.append(question)
                            stats["total_questions"] += 1
                            stats["by_subject"][subject]["questions"] += 1

                # Save lessons
                lessons_path = self.output_dir / "lessons" / subject / f"grade_{grade}" / "lessons.jsonl"
                self.save_to_jsonl(lessons, lessons_path)

                # Save questions
                questions_path = self.output_dir / "questions" / subject / f"grade_{grade}" / "questions.jsonl"
                self.save_to_jsonl(questions, questions_path)

        # Generate report
        report = {
            "generation_date": datetime.now().isoformat(),
            "curriculum": "Sri Lankan National Curriculum (English Medium)",
            "grades": self.grades,
            "subjects": self.subjects,
            "statistics": stats
        }

        report_path = self.output_dir.parent / "dataset_report.json"
        self.save_to_json(report, report_path)

        print("\n" + "="*60)
        print("DATASET GENERATION COMPLETE!")
        print("="*60)
        print(f"Total Lessons: {stats['total_lessons']}")
        print(f"Total Questions: {stats['total_questions']}")
        print(f"\nDataset saved to: {self.output_dir}")
        print(f"Report saved to: {report_path}")


def main():
    """Main function with example curriculum data"""

    # Example curriculum data structure
    # Replace this with your actual curriculum data
    curriculum_data = {
        'science': {
            'Measurements and Units': ['Length', 'Mass', 'Time', 'Temperature', 'SI Units'],
            'Force and Motion': ['Types of Forces', 'Friction', 'Simple Machines', 'Motion'],
            'Energy': ['Forms of Energy', 'Energy Transformation', 'Heat', 'Light', 'Sound'],
            'Matter': ['States of Matter', 'Properties of Matter', 'Changes in Matter'],
            'Living Things and Their Environment': ['Ecosystems', 'Food Chains', 'Habitats'],
            'Plant Kingdom': ['Parts of Plants', 'Photosynthesis', 'Plant Classification'],
            'Animal Kingdom': ['Animal Classification', 'Vertebrates', 'Invertebrates'],
            'Human Body Systems': ['Digestive System', 'Respiratory System', 'Circulatory System'],
        },
        'history': {
            'Ancient Civilizations': ['Indus Valley', 'Mesopotamia', 'Egypt'],
            'Sri Lankan History': ['Ancient Kingdoms', 'Colonial Period', 'Independence'],
            'World History': ['World Wars', 'Industrial Revolution', 'Modern Era'],
            'Cultural Heritage': ['Architecture', 'Literature', 'Art and Culture'],
        },
        'english': {
            'Reading Comprehension': ['Main Idea', 'Supporting Details', 'Inference'],
            'Grammar Basics': ['Parts of Speech', 'Tenses', 'Subject-Verb Agreement'],
            'Vocabulary Building': ['Synonyms', 'Antonyms', 'Prefixes and Suffixes'],
            'Writing Skills': ['Paragraph Writing', 'Essay Structure', 'Creative Writing'],
            'Poetry': ['Rhyme', 'Rhythm', 'Simple Poems'],
            'Short Stories': ['Story Elements', 'Characters', 'Plot'],
        },
        'health_science': {
            'Personal Hygiene': ['Hand Washing', 'Dental Care', 'Body Cleanliness'],
            'Nutrition': ['Food Groups', 'Balanced Diet', 'Healthy Eating'],
            'Disease Prevention': ['Common Diseases', 'Vaccination', 'Hygiene Practices'],
            'Mental Health': ['Stress Management', 'Emotional Well-being', 'Social Skills'],
        }
    }

    print("="*60)
    print("DATASET GENERATOR FOR HOMEWORK MANAGEMENT ML SERVICE")
    print("="*60)
    print("\nThis script generates educational datasets in JSON/JSONL format")
    print("from curriculum data for the Homework Management ML Service.\n")

    generator = DatasetGenerator()
    generator.generate_complete_dataset(curriculum_data)


if __name__ == "__main__":
    main()

