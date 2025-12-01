"""
NLP Processor for Lesson Understanding and Keyword Extraction
"""
import re
import json
import logging
from typing import List, Dict, Any, Tuple
from pathlib import Path
import numpy as np

# Set up logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class NLPProcessor:
    """
    NLP Processor for extracting topics, keywords, and concepts from lesson content.
    Uses lightweight models suitable for educational content processing.
    """
    
    def __init__(self):
        self.stopwords = self._load_stopwords()
        self.embeddings_model = None
        self._initialize_models()
    
    def _load_stopwords(self) -> set:
        """Load common English stopwords"""
        stopwords = {
            'a', 'an', 'the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
            'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'been',
            'be', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could',
            'should', 'may', 'might', 'must', 'shall', 'can', 'this', 'that', 'these',
            'those', 'it', 'its', 'they', 'them', 'their', 'what', 'which', 'who',
            'whom', 'whose', 'when', 'where', 'why', 'how', 'all', 'each', 'every',
            'both', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor',
            'not', 'only', 'own', 'same', 'so', 'than', 'too', 'very', 'just', 'also'
        }
        return stopwords
    
    def _initialize_models(self):
        """Initialize NLP models lazily"""
        try:
            from sentence_transformers import SentenceTransformer
            self.embeddings_model = SentenceTransformer('all-MiniLM-L6-v2')
            logger.info("Sentence transformer model loaded successfully")
        except Exception as e:
            logger.warning(f"Could not load sentence transformer: {e}")
            self.embeddings_model = None
    
    def extract_keywords(self, text: str, max_keywords: int = 10) -> List[str]:
        """
        Extract key concepts and keywords from text using TF-IDF approach.
        """
        # Clean and tokenize
        words = self._tokenize(text)
        
        # Filter stopwords and short words
        words = [w for w in words if w.lower() not in self.stopwords and len(w) > 2]
        
        # Calculate word frequencies
        word_freq = {}
        for word in words:
            word_lower = word.lower()
            word_freq[word_lower] = word_freq.get(word_lower, 0) + 1
        
        # Sort by frequency and get top keywords
        sorted_words = sorted(word_freq.items(), key=lambda x: x[1], reverse=True)
        keywords = [word for word, freq in sorted_words[:max_keywords]]
        
        return keywords
    
    def _tokenize(self, text: str) -> List[str]:
        """Simple tokenization"""
        # Remove special characters and split
        text = re.sub(r'[^\w\s]', ' ', text)
        words = text.split()
        return words
    
    def extract_topics(self, lesson_content: Dict[str, Any]) -> List[str]:
        """
        Extract main topics from lesson content.
        """
        topics = []
        
        # Get topics from lesson metadata
        if 'topics' in lesson_content:
            topics.extend(lesson_content['topics'])
        
        # Get unit as a topic
        if 'unit' in lesson_content:
            topics.append(lesson_content['unit'])
        
        return list(set(topics))
    
    def parse_lesson(self, lesson_data: Dict[str, Any]) -> Dict[str, Any]:
        """
        Parse and analyze lesson content to extract structured information.
        """
        parsed = {
            'subject': lesson_data.get('subject', ''),
            'grade': lesson_data.get('grade', 0),
            'unit': lesson_data.get('unit', ''),
            'title': lesson_data.get('title', ''),
            'topics': lesson_data.get('topics', []),
            'difficulty': lesson_data.get('difficulty', 'beginner'),
            'learning_outcomes': lesson_data.get('learning_outcomes', []),
            'keywords': [],
            'concepts': []
        }
        
        # Extract keywords from content
        content = lesson_data.get('content', '')
        parsed['keywords'] = self.extract_keywords(content)
        
        # Extract key concepts (combination of topics and keywords)
        parsed['concepts'] = list(set(parsed['topics'] + parsed['keywords'][:5]))
        
        return parsed
    
    def calculate_similarity(self, text1: str, text2: str) -> float:
        """
        Calculate semantic similarity between two texts.
        """
        if self.embeddings_model is None:
            # Fall back to simple word overlap
            return self._simple_similarity(text1, text2)
        
        try:
            embeddings = self.embeddings_model.encode([text1, text2])
            similarity = np.dot(embeddings[0], embeddings[1]) / (
                np.linalg.norm(embeddings[0]) * np.linalg.norm(embeddings[1])
            )
            return float(similarity)
        except Exception as e:
            logger.warning(f"Error calculating similarity: {e}")
            return self._simple_similarity(text1, text2)
    
    def _simple_similarity(self, text1: str, text2: str) -> float:
        """Simple word overlap similarity"""
        words1 = set(self._tokenize(text1.lower()))
        words2 = set(self._tokenize(text2.lower()))
        
        if not words1 or not words2:
            return 0.0
        
        intersection = words1.intersection(words2)
        union = words1.union(words2)
        
        return len(intersection) / len(union)
    
    def get_embeddings(self, texts: List[str]) -> np.ndarray:
        """Get embeddings for a list of texts"""
        if self.embeddings_model is None:
            raise ValueError("Embeddings model not initialized")
        return self.embeddings_model.encode(texts)

