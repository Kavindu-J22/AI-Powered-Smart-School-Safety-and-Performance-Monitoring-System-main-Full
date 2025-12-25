"""
Question Generator using NLP and Small Language Models
Generates MCQ, Short Answer, and Descriptive questions from lesson content
"""
import random
import logging
from typing import List, Dict, Any, Optional
from pathlib import Path
import json

logger = logging.getLogger(__name__)

class QuestionGenerator:
    """
    AI-powered question generator that creates structured questions
    from lesson content using NLP techniques and language models.
    """
    
    def __init__(self, nlp_processor=None):
        self.nlp_processor = nlp_processor
        self.question_templates = self._load_question_templates()
        self.model = None
        self.tokenizer = None
        self._initialize_model()
    
    def _initialize_model(self):
        """Initialize the language model for question generation"""
        try:
            from transformers import T5ForConditionalGeneration, T5Tokenizer
            model_name = "google/flan-t5-base"
            self.tokenizer = T5Tokenizer.from_pretrained(model_name)
            self.model = T5ForConditionalGeneration.from_pretrained(model_name)
            logger.info(f"Loaded model: {model_name}")
        except Exception as e:
            logger.warning(f"Could not load T5 model: {e}. Using template-based generation.")
            self.model = None
            self.tokenizer = None
    
    def _load_question_templates(self) -> Dict[str, List[str]]:
        """Load question templates for different question types"""
        return {
            'MCQ': [
                "What is the primary function of {topic}?",
                "Which of the following best describes {topic}?",
                "What happens when {topic} occurs?",
                "In the context of {unit}, {topic} is responsible for:",
                "Which statement about {topic} is correct?",
            ],
            'SHORT_ANSWER': [
                "Explain the process of {topic}.",
                "How does {topic} affect the system?",
                "Describe the relationship between {topic} and {unit}.",
                "What are the key characteristics of {topic}?",
                "Why is {topic} important in {unit}?",
            ],
            'DESCRIPTIVE': [
                "Discuss in detail the scientific principles underlying {topic} and their applications in {unit}.",
                "Analyze the role of {topic} in the broader context of {unit}. Provide examples from Sri Lankan context.",
                "Evaluate the importance of {topic} and explain how it relates to other concepts in {unit}.",
                "Compare and contrast different aspects of {topic}. Include practical applications.",
                "Critically examine {topic} and its significance in understanding {unit}.",
            ]
        }
    
    def generate_questions(self, lesson_data: Dict[str, Any], 
                          num_mcq: int = 2, num_short: int = 2, 
                          num_descriptive: int = 1) -> List[Dict[str, Any]]:
        """
        Generate a set of questions from lesson content.
        """
        questions = []
        topics = lesson_data.get('topics', [])
        unit = lesson_data.get('unit', '')
        subject = lesson_data.get('subject', '')
        grade = lesson_data.get('grade', 6)
        difficulty = lesson_data.get('difficulty', 'beginner')
        
        if not topics:
            logger.warning("No topics found in lesson data")
            return questions
        
        # Generate MCQ questions
        for i in range(num_mcq):
            topic = topics[i % len(topics)]
            mcq = self._generate_mcq(topic, unit, subject, grade, difficulty)
            if mcq:
                questions.append(mcq)
        
        # Generate Short Answer questions
        for i in range(num_short):
            topic = topics[i % len(topics)]
            short_q = self._generate_short_answer(topic, unit, subject, grade, difficulty)
            if short_q:
                questions.append(short_q)
        
        # Generate Descriptive questions
        for i in range(num_descriptive):
            topic = topics[i % len(topics)]
            desc_q = self._generate_descriptive(topic, unit, subject, grade, difficulty)
            if desc_q:
                questions.append(desc_q)
        
        return questions
    
    def _generate_mcq(self, topic: str, unit: str, subject: str,
                      grade: int, difficulty: str) -> Dict[str, Any]:
        """Generate a Multiple Choice Question"""
        template = random.choice(self.question_templates['MCQ'])
        question_text = template.format(topic=topic, unit=unit)

        # Generate options using model or templates
        options = self._generate_options(topic, unit, subject)
        correct_idx = 0  # First option is correct

        # Generate meaningful explanation
        explanation = self._generate_explanation(topic, unit, subject)

        return {
            'question_type': 'MCQ',
            'question_text': question_text,
            'options': options,
            'correct_answer': chr(65 + correct_idx),  # A, B, C, D
            'explanation': explanation,
            'difficulty': difficulty,
            'marks': 1,
            'subject': subject,
            'grade': grade,
            'unit': unit,
            'topic': topic,
            'bloom_level': 'remember'
        }
    
    def _generate_options(self, topic: str, unit: str, subject: str) -> List[str]:
        """Generate MCQ options"""
        if self.model is not None:
            return self._generate_options_with_model(topic, unit, subject)

        # Template-based option generation with subject-specific knowledge
        return self._generate_template_options(topic, unit, subject)

    def _generate_template_options(self, topic: str, unit: str, subject: str) -> List[str]:
        """Generate realistic MCQ options using templates and subject knowledge"""

        # Define option patterns based on subject
        subject_lower = subject.lower()

        # Science-specific options
        if 'science' in subject_lower or 'biology' in subject_lower or 'chemistry' in subject_lower or 'physics' in subject_lower:
            correct_option = f"It is a fundamental component that plays a key role in {unit}"
            distractors = [
                f"It has no significant relationship with {unit}",
                f"It only occurs in extreme conditions unrelated to {unit}",
                f"It is a byproduct that doesn't affect {unit}"
            ]

        # History-specific options
        elif 'history' in subject_lower or 'social' in subject_lower:
            correct_option = f"It significantly influenced the development of {unit}"
            distractors = [
                f"It had minimal impact on {unit}",
                f"It occurred after the period of {unit}",
                f"It was unrelated to the events in {unit}"
            ]

        # English/Language-specific options
        elif 'english' in subject_lower or 'language' in subject_lower:
            correct_option = f"It is an essential element used to enhance {unit}"
            distractors = [
                f"It is rarely used in {unit}",
                f"It contradicts the principles of {unit}",
                f"It is not applicable to {unit}"
            ]

        # Mathematics-specific options
        elif 'math' in subject_lower or 'algebra' in subject_lower or 'geometry' in subject_lower:
            correct_option = f"It is a mathematical concept that helps solve problems in {unit}"
            distractors = [
                f"It cannot be applied to {unit}",
                f"It is only theoretical and not used in {unit}",
                f"It contradicts the principles of {unit}"
            ]

        # Health Science-specific options
        elif 'health' in subject_lower or 'medical' in subject_lower:
            correct_option = f"It is important for maintaining proper function in {unit}"
            distractors = [
                f"It has no effect on {unit}",
                f"It only affects {unit} in rare cases",
                f"It is harmful to {unit}"
            ]

        # General/Default options
        else:
            correct_option = f"It is a key concept that is central to understanding {unit}"
            distractors = [
                f"It is not directly related to {unit}",
                f"It only applies in specific cases outside {unit}",
                f"It contradicts the main principles of {unit}"
            ]

        # Shuffle distractors and insert correct answer at random position
        random.shuffle(distractors)
        all_options = [correct_option] + distractors[:3]  # Take only 3 distractors

        # Ensure we have exactly 4 options
        while len(all_options) < 4:
            all_options.append(f"This is not a characteristic of {topic}")

        return all_options[:4]

    def _generate_options_with_model(self, topic: str, unit: str, subject: str) -> List[str]:
        """Generate options using the language model"""
        try:
            prompt = f"Generate 4 multiple choice options for: What is {topic}? in {subject}"
            inputs = self.tokenizer(prompt, return_tensors="pt", max_length=128, truncation=True)
            outputs = self.model.generate(**inputs, max_length=200, num_return_sequences=1)
            generated = self.tokenizer.decode(outputs[0], skip_special_tokens=True)
            # Parse generated options
            options = generated.split('\n')[:4]
            while len(options) < 4:
                options.append(f"Option {len(options) + 1} about {topic}")
            return options
        except Exception as e:
            logger.warning(f"Model generation failed: {e}")
            return self._generate_options(topic, unit, subject)

    def _generate_explanation(self, topic: str, unit: str, subject: str) -> str:
        """Generate a meaningful explanation for the correct answer"""
        subject_lower = subject.lower()

        explanations = {
            'science': f"Option A is correct because {topic} is a fundamental component in {unit}. It plays a crucial role in the processes and mechanisms that define this area of study.",
            'history': f"Option A is correct because {topic} had a significant historical impact on {unit}. Understanding this relationship is essential for comprehending the broader historical context.",
            'english': f"Option A is correct because {topic} is an important literary or linguistic element in {unit}. It enhances understanding and application of language concepts.",
            'math': f"Option A is correct because {topic} is a key mathematical concept that is essential for solving problems related to {unit}.",
            'health': f"Option A is correct because {topic} plays a vital role in maintaining proper health and function in the context of {unit}.",
        }

        # Find matching subject explanation
        for key, explanation in explanations.items():
            if key in subject_lower:
                return explanation

        # Default explanation
        return f"Option A is correct because {topic} is a central concept in {unit}. Understanding this relationship is fundamental to mastering this subject area."

    def _generate_short_answer(self, topic: str, unit: str, subject: str,
                               grade: int, difficulty: str) -> Dict[str, Any]:
        """Generate a Short Answer Question"""
        template = random.choice(self.question_templates['SHORT_ANSWER'])
        question_text = template.format(topic=topic, unit=unit)
        
        return {
            'question_type': 'SHORT_ANSWER',
            'question_text': question_text,
            'expected_answer': f"A comprehensive explanation of {topic} including its key aspects, relevance to {unit}, and practical applications.",
            'key_points': [f"Definition of {topic}", f"Relationship to {unit}", "Practical application or example"],
            'difficulty': difficulty,
            'marks': 3,
            'subject': subject,
            'grade': grade,
            'unit': unit,
            'topic': topic,
            'bloom_level': 'understand'
        }
    
    def _generate_descriptive(self, topic: str, unit: str, subject: str,
                              grade: int, difficulty: str) -> Dict[str, Any]:
        """Generate a Descriptive Question"""
        template = random.choice(self.question_templates['DESCRIPTIVE'])
        question_text = template.format(topic=topic, unit=unit)
        
        return {
            'question_type': 'DESCRIPTIVE',
            'question_text': question_text,
            'expected_answer': f"A comprehensive analysis of {topic} covering theoretical understanding, practical applications, examples from Sri Lankan context, and critical evaluation.",
            'key_points': [
                f"Theoretical foundation of {topic}",
                "Practical applications and examples",
                "Analysis and critical thinking",
                "Relevance to Sri Lankan context",
                "Conclusions and recommendations"
            ],
            'difficulty': difficulty,
            'marks': 5,
            'subject': subject,
            'grade': grade,
            'unit': unit,
            'topic': topic,
            'bloom_level': 'analyze'
        }

