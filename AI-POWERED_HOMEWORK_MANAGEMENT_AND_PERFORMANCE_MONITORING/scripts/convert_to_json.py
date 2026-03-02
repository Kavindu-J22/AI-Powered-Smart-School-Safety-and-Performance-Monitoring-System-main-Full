"""
Data Conversion Script - Convert Various Formats to JSON/JSONL
This script provides utilities to convert CSV, Excel, and Text files to JSON format
for the Homework Management ML Service.

"""

import json
import csv
from pathlib import Path
from typing import List, Dict, Any
import argparse


class DataConverter:
    """Convert various data formats to JSON/JSONL for ML service"""
    
    def __init__(self):
        self.supported_formats = ['csv', 'txt', 'json']
    
    def csv_to_jsonl(self, csv_file: str, output_file: str, 
                     mapping: Dict[str, str] = None):
        """
        Convert CSV file to JSONL format
        
        Args:
            csv_file: Path to input CSV file
            output_file: Path to output JSONL file
            mapping: Dictionary mapping CSV columns to JSON fields
                    Example: {'Question': 'question_text', 'Type': 'question_type'}
        """
        
        # Default mapping if none provided
        if mapping is None:
            mapping = {
                'question': 'question_text',
                'type': 'question_type',
                'subject': 'subject',
                'grade': 'grade',
                'unit': 'unit',
                'topic': 'topic',
                'option_a': 'option_a',
                'option_b': 'option_b',
                'option_c': 'option_c',
                'option_d': 'option_d',
                'answer': 'correct_answer',
                'marks': 'marks',
                'difficulty': 'difficulty'
            }
        
        converted_count = 0
        
        with open(csv_file, 'r', encoding='utf-8') as f_in:
            reader = csv.DictReader(f_in)
            
            with open(output_file, 'w', encoding='utf-8') as f_out:
                for row in reader:
                    # Build JSON object based on question type
                    question_type = row.get('type', row.get('Type', 'MCQ')).upper()
                    
                    json_obj = {
                        "question_type": question_type,
                        "question_text": row.get('question', row.get('Question', '')),
                        "subject": row.get('subject', row.get('Subject', '')),
                        "grade": int(row.get('grade', row.get('Grade', 6))),
                        "unit": row.get('unit', row.get('Unit', '')),
                        "topic": row.get('topic', row.get('Topic', '')),
                        "difficulty": row.get('difficulty', row.get('Difficulty', 'beginner')),
                        "marks": int(row.get('marks', row.get('Marks', 1)))
                    }
                    
                    # Add type-specific fields
                    if question_type == 'MCQ':
                        json_obj["options"] = [
                            row.get('option_a', row.get('Option_A', '')),
                            row.get('option_b', row.get('Option_B', '')),
                            row.get('option_c', row.get('Option_C', '')),
                            row.get('option_d', row.get('Option_D', ''))
                        ]
                        json_obj["correct_answer"] = row.get('answer', row.get('Answer', 'A'))
                        json_obj["explanation"] = row.get('explanation', row.get('Explanation', ''))
                        json_obj["bloom_level"] = "remember"
                    
                    elif question_type == 'SHORT_ANSWER':
                        json_obj["expected_answer"] = row.get('expected_answer', 
                                                              row.get('Expected_Answer', ''))
                        json_obj["key_points"] = row.get('key_points', '').split(';') if row.get('key_points') else []
                        json_obj["bloom_level"] = "understand"
                    
                    elif question_type == 'DESCRIPTIVE':
                        json_obj["expected_answer"] = row.get('expected_answer', 
                                                              row.get('Expected_Answer', ''))
                        json_obj["key_points"] = row.get('key_points', '').split(';') if row.get('key_points') else []
                        json_obj["bloom_level"] = "analyze"
                    
                    # Write JSON line
                    f_out.write(json.dumps(json_obj, ensure_ascii=False) + '\n')
                    converted_count += 1
        
        print(f"✓ Converted {converted_count} records from CSV to JSONL")
        print(f"  Input:  {csv_file}")
        print(f"  Output: {output_file}")
        
        return converted_count
    
    def text_to_json(self, text_file: str, output_file: str, 
                     subject: str, grade: int, unit: str):
        """
        Convert text file to JSON lesson format
        
        Args:
            text_file: Path to input text file
            output_file: Path to output JSON file
            subject: Subject name
            grade: Grade level
            unit: Unit/chapter name
        """
        
        with open(text_file, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Extract topics from content (simple extraction)
        # You can customize this based on your text format
        topics = []
        for line in content.split('\n'):
            if line.startswith('- ') or line.startswith('* '):
                topics.append(line[2:].strip())
        
        lesson = {
            "subject": subject,
            "grade": grade,
            "unit": unit,
            "title": f"{unit} - Grade {grade}",
            "topics": topics if topics else ["General Topic"],
            "content": content,
            "difficulty": "beginner" if grade <= 7 else "intermediate" if grade <= 9 else "advanced",
            "duration_minutes": 60,
            "learning_outcomes": [
                f"Understand key concepts of {unit}",
                f"Apply knowledge in practical situations",
                f"Analyze and evaluate {unit} principles"
            ]
        }
        
        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(lesson, f, indent=2, ensure_ascii=False)
        
        print(f"✓ Converted text file to JSON lesson")
        print(f"  Input:  {text_file}")
        print(f"  Output: {output_file}")
        
        return lesson
    
    def json_to_jsonl(self, json_file: str, output_file: str):
        """
        Convert JSON array to JSONL format
        
        Args:
            json_file: Path to input JSON file (containing array)
            output_file: Path to output JSONL file
        """
        
        with open(json_file, 'r', encoding='utf-8') as f:
            data = json.load(f)
        
        # Handle both single object and array
        if not isinstance(data, list):
            data = [data]
        
        with open(output_file, 'w', encoding='utf-8') as f:
            for item in data:
                f.write(json.dumps(item, ensure_ascii=False) + '\n')
        
        print(f"✓ Converted {len(data)} records from JSON to JSONL")
        print(f"  Input:  {json_file}")
        print(f"  Output: {output_file}")
        
        return len(data)

    def jsonl_to_json(self, jsonl_file: str, output_file: str):
        """
        Convert JSONL to JSON array format

        Args:
            jsonl_file: Path to input JSONL file
            output_file: Path to output JSON file
        """

        data = []
        with open(jsonl_file, 'r', encoding='utf-8') as f:
            for line in f:
                if line.strip():
                    data.append(json.loads(line))

        with open(output_file, 'w', encoding='utf-8') as f:
            json.dump(data, f, indent=2, ensure_ascii=False)

        print(f"✓ Converted {len(data)} records from JSONL to JSON")
        print(f"  Input:  {jsonl_file}")
        print(f"  Output: {output_file}")

        return len(data)


def main():
    """Main function with CLI interface"""

    parser = argparse.ArgumentParser(
        description='Convert various data formats to JSON/JSONL for Homework Management ML Service'
    )

    parser.add_argument('--mode', type=str, required=True,
                       choices=['csv-to-jsonl', 'text-to-json', 'json-to-jsonl', 'jsonl-to-json'],
                       help='Conversion mode')

    parser.add_argument('--input', type=str, required=True,
                       help='Input file path')

    parser.add_argument('--output', type=str, required=True,
                       help='Output file path')

    parser.add_argument('--subject', type=str, default='science',
                       help='Subject name (for text-to-json mode)')

    parser.add_argument('--grade', type=int, default=6,
                       help='Grade level (for text-to-json mode)')

    parser.add_argument('--unit', type=str, default='General',
                       help='Unit/chapter name (for text-to-json mode)')

    args = parser.parse_args()

    converter = DataConverter()

    print("="*60)
    print("DATA CONVERSION TOOL")
    print("="*60)
    print(f"\nMode: {args.mode}")
    print(f"Input: {args.input}")
    print(f"Output: {args.output}\n")

    try:
        if args.mode == 'csv-to-jsonl':
            converter.csv_to_jsonl(args.input, args.output)

        elif args.mode == 'text-to-json':
            converter.text_to_json(args.input, args.output,
                                  args.subject, args.grade, args.unit)

        elif args.mode == 'json-to-jsonl':
            converter.json_to_jsonl(args.input, args.output)

        elif args.mode == 'jsonl-to-json':
            converter.jsonl_to_json(args.input, args.output)

        print("\n✅ Conversion completed successfully!")

    except Exception as e:
        print(f"\n❌ Error during conversion: {str(e)}")
        return 1

    return 0


# Example usage functions
def example_csv_conversion():
    """Example: Convert CSV to JSONL"""
    converter = DataConverter()

    # Example CSV file structure:
    # question,type,subject,grade,unit,topic,option_a,option_b,option_c,option_d,answer,marks

    converter.csv_to_jsonl(
        csv_file='data/questions.csv',
        output_file='data/questions.jsonl'
    )


def example_text_conversion():
    """Example: Convert text lesson to JSON"""
    converter = DataConverter()

    converter.text_to_json(
        text_file='data/lesson.txt',
        output_file='data/lesson.json',
        subject='science',
        grade=6,
        unit='Physics Basics'
    )


def example_json_conversion():
    """Example: Convert JSON array to JSONL"""
    converter = DataConverter()

    converter.json_to_jsonl(
        json_file='data/questions_array.json',
        output_file='data/questions.jsonl'
    )


if __name__ == "__main__":
    # If run with command line arguments, use CLI mode
    import sys
    if len(sys.argv) > 1:
        exit(main())
    else:
        # Otherwise, show usage examples
        print("="*60)
        print("DATA CONVERSION TOOL - USAGE EXAMPLES")
        print("="*60)
        print("\n1. Convert CSV to JSONL:")
        print("   python convert_to_json.py --mode csv-to-jsonl --input questions.csv --output questions.jsonl")
        print("\n2. Convert Text to JSON:")
        print("   python convert_to_json.py --mode text-to-json --input lesson.txt --output lesson.json --subject science --grade 6 --unit Physics")
        print("\n3. Convert JSON to JSONL:")
        print("   python convert_to_json.py --mode json-to-jsonl --input data.json --output data.jsonl")
        print("\n4. Convert JSONL to JSON:")
        print("   python convert_to_json.py --mode jsonl-to-json --input data.jsonl --output data.json")
        print("\n" + "="*60)

