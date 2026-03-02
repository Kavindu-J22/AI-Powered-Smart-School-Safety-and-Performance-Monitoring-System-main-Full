"""
Question Generator using NLP and Small Language Models
Generates MCQ, Short Answer, and Descriptive questions from lesson content
"""
import re
import random
import logging
from typing import List, Dict, Any, Optional, Tuple
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
        All generation methods receive the full lesson_data so they can
        produce content-specific, non-generic questions and answers.
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

        # Track used templates to prevent identical question stems
        used_mcq_templates: List[str] = []
        used_short_templates: List[str] = []
        used_desc_templates: List[str] = []

        # Generate MCQ questions
        for i in range(num_mcq):
            topic = topics[i % len(topics)]
            mcq = self._generate_mcq(topic, unit, subject, grade, difficulty,
                                     lesson_data, used_mcq_templates)
            if mcq:
                questions.append(mcq)

        # Generate Short Answer questions
        for i in range(num_short):
            topic = topics[i % len(topics)]
            short_q = self._generate_short_answer(topic, unit, subject, grade, difficulty,
                                                  lesson_data, used_short_templates)
            if short_q:
                questions.append(short_q)

        # Generate Descriptive questions
        for i in range(num_descriptive):
            topic = topics[i % len(topics)]
            desc_q = self._generate_descriptive(topic, unit, subject, grade, difficulty,
                                                lesson_data, used_desc_templates)
            if desc_q:
                questions.append(desc_q)

        return questions
    
    def _generate_mcq(self, topic: str, unit: str, subject: str,
                      grade: int, difficulty: str,
                      lesson_data: Dict[str, Any] = None,
                      used_templates: List[str] = None) -> Dict[str, Any]:
        """Generate a Multiple Choice Question with content-based, shuffled options."""
        if used_templates is None:
            used_templates = []

        # Select an unused template to avoid repetition
        available = [t for t in self.question_templates['MCQ'] if t not in used_templates]
        if not available:
            available = self.question_templates['MCQ']
            used_templates.clear()
        template = random.choice(available)
        used_templates.append(template)
        question_text = template.format(topic=topic, unit=unit)

        content = lesson_data.get('content', '') if lesson_data else ''
        learning_outcomes = lesson_data.get('learning_outcomes', []) if lesson_data else []
        keywords = lesson_data.get('keywords', []) if lesson_data else []
        all_topics = lesson_data.get('topics', [topic]) if lesson_data else [topic]

        # Generate a content-based correct option
        correct_option = self._extract_correct_option(topic, unit, subject, content, learning_outcomes)

        # Generate 3 plausible distractors
        distractors = self._generate_distractors(topic, unit, subject, all_topics, keywords, correct_option, content)

        # Build 4-option list and SHUFFLE so the correct answer is NOT always A
        all_options = [correct_option] + distractors[:3]
        while len(all_options) < 4:
            all_options.append(f"{topic} is not relevant to the study of {unit}")

        random.shuffle(all_options)
        correct_idx = all_options.index(correct_option)
        correct_letter = chr(65 + correct_idx)

        explanation = (
            f"Option {correct_letter} is correct: {correct_option} "
            f"Understanding {topic} is essential in the context of {unit}."
        )

        return {
            'question_type': 'MCQ',
            'question_text': question_text,
            'options': all_options,
            'correct_answer': correct_letter,
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

    # -----------------------------------------------------------------------
    # Content extraction helpers
    # -----------------------------------------------------------------------

    def _extract_topic_sentences(self, content: str, topic: str,
                                  max_sentences: int = 3) -> List[str]:
        """Return up to max_sentences from content that are most relevant to topic."""
        if not content or not topic:
            return []
        sentences = re.split(r'(?<=[.!?])\s+', content)
        topic_words = {w.lower() for w in topic.split() if len(w) > 3}
        scored = []
        for sent in sentences:
            sent = sent.strip()
            if len(sent) < 15:
                continue
            sent_lower = sent.lower()
            score = sum(1 for w in topic_words if w in sent_lower)
            if score > 0:
                scored.append((score, sent))
        scored.sort(key=lambda x: x[0], reverse=True)
        return [s for _, s in scored[:max_sentences]]

    def _extract_correct_option(self, topic: str, unit: str, subject: str,
                                 content: str,
                                 learning_outcomes: List[str]) -> str:
        """Derive a short, factual correct-option statement from lesson content."""
        topic_lower = topic.lower()
        topic_words = {w.lower() for w in topic.split() if len(w) > 3}

        # 1. Use a relevant learning outcome (most concise and factual)
        for outcome in learning_outcomes:
            if topic_lower in outcome.lower() or any(w in outcome.lower() for w in topic_words):
                text = outcome.strip()
                return (text[:127] + '...') if len(text) > 130 else text

        # 2. Use a relevant sentence from the content
        if content:
            for sent in self._extract_topic_sentences(content, topic, max_sentences=3):
                sent = sent.strip()
                if 20 <= len(sent) <= 150:
                    return sent
                if len(sent) > 150:
                    return sent[:127].rstrip(' .,') + '...'

        # 3. Subject-specific fallback
        return self._subject_correct_option(topic, unit, subject)

    def _subject_correct_option(self, topic: str, unit: str, subject: str) -> str:
        """Return a subject-appropriate correct-option phrase."""
        s = subject.lower()
        if any(k in s for k in ['science', 'biology', 'chemistry', 'physics']):
            return f"{topic} plays a fundamental role in {unit} through its key biological and chemical processes"
        if any(k in s for k in ['history', 'social']):
            return f"{topic} significantly influenced the development and key events in {unit}"
        if any(k in s for k in ['english', 'language']):
            return f"{topic} is an essential linguistic element that strengthens understanding in {unit}"
        if any(k in s for k in ['math', 'algebra', 'geometry']):
            return f"{topic} is a mathematical concept used to solve problems related to {unit}"
        if any(k in s for k in ['health', 'medical']):
            return f"{topic} is important for maintaining proper health and function related to {unit}"
        return f"{topic} is a central concept that is essential for understanding {unit}"

    def _generate_distractors(self, topic: str, unit: str, subject: str,
                               all_topics: List[str], keywords: List[str],
                               correct_option: str, content: str) -> List[str]:
        """Generate 3 plausible but clearly incorrect distractors."""
        distractors: List[str] = []
        correct_lower = correct_option.lower()
        s = subject.lower()

        # Strategy 1 – use other topics' "correct" phrasing as cross-topic distractors
        other_topics = [t for t in all_topics if t.lower() != topic.lower()]
        random.shuffle(other_topics)
        for other in other_topics[:2]:
            d = self._subject_correct_option(other, unit, subject)
            if d.lower() != correct_lower and d not in distractors:
                distractors.append(d)

        # Strategy 2 – negation / misconception statements
        if any(k in s for k in ['science', 'biology', 'chemistry', 'physics']):
            negs = [
                f"{topic} has no significant role in {unit} and is a purely passive substance",
                f"{topic} only occurs under extreme laboratory conditions unrelated to {unit}",
                f"{topic} is a byproduct that reduces the efficiency of {unit} processes",
                f"{topic} inhibits the normal functioning of {unit}",
            ]
        elif any(k in s for k in ['history', 'social']):
            negs = [
                f"{topic} had minimal historical impact and is largely unrelated to {unit}",
                f"{topic} developed long after the period associated with {unit}",
                f"The effects of {topic} were confined to regions outside {unit}",
                f"{topic} was reversed before it could meaningfully influence {unit}",
            ]
        elif any(k in s for k in ['english', 'language']):
            negs = [
                f"{topic} is considered a grammatical error in the context of {unit}",
                f"{topic} contradicts the stylistic conventions required in {unit}",
                f"The use of {topic} is discouraged because it reduces clarity in {unit}",
                f"{topic} is an archaic form no longer applicable to {unit}",
            ]
        elif any(k in s for k in ['health', 'medical']):
            negs = [
                f"{topic} has been shown to have harmful effects in the context of {unit}",
                f"{topic} has no measurable impact on health outcomes in {unit}",
                f"The influence of {topic} on {unit} is negligible in modern medicine",
                f"{topic} only applies in rare cases that are unrelated to {unit}",
            ]
        else:
            negs = [
                f"{topic} is not directly related to the study of {unit}",
                f"{topic} contradicts the main principles of {unit}",
                f"The role of {topic} in {unit} is purely theoretical with no practical applications",
                f"{topic} is an outdated concept no longer used in {unit}",
            ]

        random.shuffle(negs)
        for neg in negs:
            if len(distractors) >= 3:
                break
            if neg.lower() != correct_lower and neg not in distractors:
                distractors.append(neg)

        return distractors[:3]

    def _extract_expected_answer(self, topic: str, unit: str, subject: str,
                                  content: str, learning_outcomes: List[str],
                                  max_outcomes: int = 3) -> Tuple[str, List[str]]:
        """
        Build a content-based expected answer and key_points list for
        SHORT_ANSWER and DESCRIPTIVE questions.
        """
        topic_lower = topic.lower()
        topic_words = {w.lower() for w in topic.split() if len(w) > 3}

        # Separate learning outcomes relevant to this topic from the rest
        relevant: List[str] = []
        other: List[str] = []
        for outcome in learning_outcomes:
            if topic_lower in outcome.lower() or any(w in outcome.lower() for w in topic_words):
                relevant.append(outcome.strip())
            else:
                other.append(outcome.strip())

        # Prefer relevant outcomes; pad with others if needed
        selected = relevant[:max_outcomes]
        if len(selected) < 2:
            selected += other[: max_outcomes - len(selected)]

        # Key points = selected outcomes (human-readable learning goals)
        key_points: List[str] = selected[:max_outcomes] if selected else [
            f"Definition and meaning of {topic}",
            f"Relationship between {topic} and {unit}",
            f"Practical application of {topic} in {unit}",
        ]

        # Build expected answer: outcomes + relevant content sentences
        answer_parts: List[str] = []
        if selected:
            answer_parts.append(' '.join(selected))

        if content:
            for sent in self._extract_topic_sentences(content, topic, max_sentences=3):
                if sent not in ' '.join(answer_parts):
                    answer_parts.append(sent)

        if answer_parts:
            expected_answer = ' '.join(answer_parts)
        else:
            expected_answer = (
                f"{topic} is an important concept in {unit}. "
                f"It involves understanding the key principles and mechanisms "
                f"related to {unit} and applying them in practical contexts."
            )

        return expected_answer, key_points

    # -----------------------------------------------------------------------
    # Question generation methods (SHORT_ANSWER / DESCRIPTIVE)
    # -----------------------------------------------------------------------

    def _generate_short_answer(self, topic: str, unit: str, subject: str,
                               grade: int, difficulty: str,
                               lesson_data: Dict[str, Any] = None,
                               used_templates: List[str] = None) -> Dict[str, Any]:
        """Generate a Short Answer Question with content-based expected answer."""
        if used_templates is None:
            used_templates = []
        available = [t for t in self.question_templates['SHORT_ANSWER'] if t not in used_templates]
        if not available:
            available = self.question_templates['SHORT_ANSWER']
            used_templates.clear()
        template = random.choice(available)
        used_templates.append(template)
        question_text = template.format(topic=topic, unit=unit)

        content = lesson_data.get('content', '') if lesson_data else ''
        learning_outcomes = lesson_data.get('learning_outcomes', []) if lesson_data else []

        expected_answer, key_points = self._extract_expected_answer(
            topic, unit, subject, content, learning_outcomes, max_outcomes=3
        )

        return {
            'question_type': 'SHORT_ANSWER',
            'question_text': question_text,
            'expected_answer': expected_answer,
            'key_points': key_points,
            'difficulty': difficulty,
            'marks': 3,
            'subject': subject,
            'grade': grade,
            'unit': unit,
            'topic': topic,
            'bloom_level': 'understand'
        }

    def _generate_descriptive(self, topic: str, unit: str, subject: str,
                              grade: int, difficulty: str,
                              lesson_data: Dict[str, Any] = None,
                              used_templates: List[str] = None) -> Dict[str, Any]:
        """Generate a Descriptive Question with comprehensive content-based answer."""
        if used_templates is None:
            used_templates = []
        available = [t for t in self.question_templates['DESCRIPTIVE'] if t not in used_templates]
        if not available:
            available = self.question_templates['DESCRIPTIVE']
            used_templates.clear()
        template = random.choice(available)
        used_templates.append(template)
        question_text = template.format(topic=topic, unit=unit)

        content = lesson_data.get('content', '') if lesson_data else ''
        learning_outcomes = lesson_data.get('learning_outcomes', []) if lesson_data else []

        # Use all learning outcomes for a descriptive (comprehensive) answer
        expected_answer, key_points = self._extract_expected_answer(
            topic, unit, subject, content, learning_outcomes, max_outcomes=5
        )

        # Ensure at least 5 key points with descriptive-specific additions
        descriptive_additions = [
            f"Practical applications and real-world examples of {topic}",
            f"Critical analysis of {topic} in the context of {unit}",
            f"Relevance of {topic} to the Sri Lankan educational context",
        ]
        for addition in descriptive_additions:
            if len(key_points) >= 5:
                break
            key_points.append(addition)

        return {
            'question_type': 'DESCRIPTIVE',
            'question_text': question_text,
            'expected_answer': expected_answer,
            'key_points': key_points,
            'difficulty': difficulty,
            'marks': 5,
            'subject': subject,
            'grade': grade,
            'unit': unit,
            'topic': topic,
            'bloom_level': 'analyze'
        }

