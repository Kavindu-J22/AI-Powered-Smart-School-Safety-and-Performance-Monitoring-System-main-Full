"""
Debug script to test audio detection and see actual probabilities
"""
import numpy as np
import sys
import os

sys.path.append(os.path.dirname(os.path.abspath(__file__)))

from models.threat_detector import ThreatDetector
from utils.audio_processor import AudioProcessor

def test_normal_audio():
    """Test with simulated normal speech/ambient noise"""
    print("\n" + "="*60)
    print("TESTING NORMAL AUDIO (Low Energy)")
    print("="*60)
    
    detector = ThreatDetector()
    
    # Simulate normal speech - low energy, typical frequency
    sample_rate = 16000
    duration = 4.0
    samples = int(sample_rate * duration)
    
    # Generate low-energy audio (normal speech level)
    audio = np.random.normal(0, 0.05, samples).astype(np.float32)
    
    result = detector.analyze_audio(audio, enable_speech=False, enable_non_speech=True)
    
    print(f"\nAudio Energy: {result['details'].get('audio_energy', 'N/A')}")
    print(f"Min Energy Threshold: {detector.min_energy_threshold}")
    print(f"High Energy Threshold: {detector.high_energy_threshold}")
    
    if result.get('non_speech_result'):
        ns = result['non_speech_result']
        print(f"\nDetected Class: {ns['detected_class']}")
        print(f"Confidence: {ns['confidence']:.4f}")
        print(f"Class Threshold: {ns.get('class_threshold', 'N/A'):.4f}")
        print(f"Adaptive Threshold: {ns.get('threshold_used', 'N/A'):.4f}")
        print(f"Initial Detection: {ns.get('initial_detection', False)}")
        print(f"Confirmed Threat: {ns['is_threat']}")
        
        print(f"\nAll Probabilities:")
        for cls, prob in ns['all_probabilities'].items():
            print(f"  {cls:20s}: {prob:.4f} ({prob*100:.1f}%)")
    
    print(f"\nFinal Result: {'THREAT' if result['is_threat'] else 'SAFE'}")
    print(f"Threat Type: {result.get('threat_type', 'None')}")


def test_high_energy_audio():
    """Test with high energy audio (actual screaming)"""
    print("\n" + "="*60)
    print("TESTING HIGH ENERGY AUDIO (Simulated Screaming)")
    print("="*60)
    
    detector = ThreatDetector()
    
    # Simulate high-energy audio (actual screaming)
    sample_rate = 16000
    duration = 4.0
    samples = int(sample_rate * duration)
    
    # Generate high-energy audio
    audio = np.random.normal(0, 0.35, samples).astype(np.float32)
    
    result = detector.analyze_audio(audio, enable_speech=False, enable_non_speech=True)
    
    print(f"\nAudio Energy: {result['details'].get('audio_energy', 'N/A')}")
    print(f"Min Energy Threshold: {detector.min_energy_threshold}")
    print(f"High Energy Threshold: {detector.high_energy_threshold}")
    
    if result.get('non_speech_result'):
        ns = result['non_speech_result']
        print(f"\nDetected Class: {ns['detected_class']}")
        print(f"Confidence: {ns['confidence']:.4f}")
        print(f"Class Threshold: {ns.get('class_threshold', 'N/A'):.4f}")
        print(f"Adaptive Threshold: {ns.get('threshold_used', 'N/A'):.4f}")
        print(f"Initial Detection: {ns.get('initial_detection', False)}")
        print(f"Confirmed Threat: {ns['is_threat']}")
        
        print(f"\nAll Probabilities:")
        for cls, prob in ns['all_probabilities'].items():
            print(f"  {cls:20s}: {prob:.4f} ({prob*100:.1f}%)")
    
    print(f"\nFinal Result: {'THREAT' if result['is_threat'] else 'SAFE'}")
    print(f"Threat Type: {result.get('threat_type', 'None')}")


def test_detector_settings():
    """Show current detector settings"""
    print("\n" + "="*60)
    print("CURRENT DETECTOR SETTINGS")
    print("="*60)
    
    detector = ThreatDetector()
    settings = detector.get_sensitivity_settings()
    
    print(f"\nConsecutive Required: {settings['consecutive_required']}")
    print(f"Min Energy Threshold: {settings['min_energy_threshold']}")
    print(f"High Energy Threshold: {settings['high_energy_threshold']}")
    
    print(f"\nClass Thresholds:")
    for cls, threshold in settings['class_thresholds'].items():
        print(f"  {cls:20s}: {threshold:.4f} ({threshold*100:.1f}%)")
    
    print(f"\nNoise Profiler Status:")
    noise_status = detector.noise_profiler.get_status()
    for key, value in noise_status.items():
        print(f"  {key}: {value}")


if __name__ == '__main__':
    test_detector_settings()
    test_normal_audio()
    test_high_energy_audio()
    
    print("\n" + "="*60)
    print("TESTING COMPLETE")
    print("="*60 + "\n")

