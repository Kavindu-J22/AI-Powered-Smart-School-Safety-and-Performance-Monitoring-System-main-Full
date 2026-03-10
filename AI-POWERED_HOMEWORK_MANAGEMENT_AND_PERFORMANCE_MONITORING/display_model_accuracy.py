#!/usr/bin/env python3
"""
Display Homework Management ML Service — Accuracy Report
Reads from models/saved/evaluation_results.json and shows formatted metrics.
Run: python display_model_accuracy.py
     python display_model_accuracy.py --rerun   (re-runs evaluation first)
"""
import json
import sys
import os
from pathlib import Path

RESULTS_FILE   = Path(__file__).parent / "models" / "saved" / "evaluation_results.json"
METADATA_FILE  = Path(__file__).parent / "models" / "saved" / "training_metadata.json"


def bar(value: float, width: int = 28) -> str:
    filled = int(round(max(0, min(value, 100)) / 100 * width))
    return "[" + "█" * filled + "░" * (width - filled) + "]"


def status_icon(value: float, good: float = 80.0, ok: float = 60.0) -> str:
    if value >= good:
        return "✅"
    elif value >= ok:
        return "⚠️ "
    return "❌"


def display_accuracy():
    # Optionally re-run evaluation
    rerun = "--rerun" in sys.argv
    if rerun:
        print("\n⏳  Re-running evaluation (this may take a minute)...")
        import subprocess
        result = subprocess.run(
            [sys.executable, str(Path(__file__).parent / "training" / "evaluate_models.py")],
            capture_output=True, text=True
        )
        if result.returncode != 0:
            print("❌  Evaluation failed:\n", result.stderr)
            return
        print("✅  Evaluation complete!\n")

    if not RESULTS_FILE.exists():
        print("\n" + "=" * 70)
        print("  ❌  ERROR: evaluation_results.json not found!")
        print(f"     Expected: {RESULTS_FILE}")
        print("     Run: python display_model_accuracy.py --rerun")
        print("=" * 70)
        return

    with open(RESULTS_FILE) as f:
        data = json.load(f)

    metadata = {}
    if METADATA_FILE.exists():
        with open(METADATA_FILE) as f:
            metadata = json.load(f)

    # Extract metrics
    qg   = data.get("question_generation", {})
    mcq  = data.get("mcq_grading", {})
    subj = data.get("subjective_grading", {})
    kw   = data.get("keyword_extraction", {})
    ov   = data.get("overall", {})
    evaluated_at = data.get("evaluated_at", "N/A")

    qg_validity  = qg.get("validity_rate", 0)
    mcq_accuracy = mcq.get("accuracy", 0)
    sa_score     = subj.get("short_answer", {}).get("avg_score_for_correct", 0)
    desc_score   = subj.get("descriptive", {}).get("avg_score_for_correct", 0)
    kw_precision = kw.get("precision", 0)
    kw_recall    = kw.get("recall", 0)
    kw_f1        = kw.get("f1_score", 0)
    overall      = ov.get("overall_score", 0)

    print("\n" + "=" * 70)
    print("  📚  HOMEWORK MANAGEMENT ML SERVICE — ACCURACY REPORT")
    print("=" * 70)

    subjects  = ", ".join(metadata.get("subjects", [])).upper() or "N/A"
    grades    = metadata.get("grades", [])
    grade_str = f"Grade {min(grades)}–{max(grades)}" if grades else "N/A"
    total_q   = metadata.get("total_questions", "N/A")
    total_l   = metadata.get("total_lessons", "N/A")

    print(f"  Subjects : {subjects}")
    print(f"  Grades   : {grade_str}   |   Lessons: {total_l}   |   Questions: {total_q}")
    print(f"  Evaluated: {evaluated_at[:19].replace('T', ' ')}")

    # ── Question Generation ──────────────────────────────────────────────
    print("\n📝  QUESTION GENERATION")
    print("-" * 70)
    print(f"  Validity Rate      {bar(qg_validity)}  {qg_validity:6.2f}%  {status_icon(qg_validity)}")
    by_type = qg.get("by_type", {})
    for qtype, acc in by_type.items():
        label = f"  {qtype:<18}"
        print(f"{label} {bar(acc)}  {acc:6.2f}%  {status_icon(acc)}")
    print(f"  Total Generated: {qg.get('total_generated', 'N/A')}   Valid: {qg.get('valid_questions', 'N/A')}")

    # ── MCQ Auto-Grading ─────────────────────────────────────────────────
    print("\n✅  MCQ AUTO-GRADING  —  Input Format Robustness")
    print("-" * 70)
    print(f"  Overall Accuracy   {bar(mcq_accuracy)}  {mcq_accuracy:6.2f}%  {status_icon(mcq_accuracy)}")
    print(f"  Questions Tested : {mcq.get('total_tested', 'N/A')}   "
          f"Total Test Cases: {mcq.get('total_test_cases', 'N/A')}   "
          f"Passed: {mcq.get('correct_evaluations', 'N/A')}")
    fmt_acc = mcq.get("format_accuracy", {})
    if fmt_acc:
        print()
        print("  ┌─ Format Breakdown ───────────────────────────────────────────┐")
        for fmt_name, fmt_pct in fmt_acc.items():
            icon = "✅" if fmt_pct == 100 else ("⚠️ " if fmt_pct >= 50 else "❌")
            print(f"  │  {fmt_name:<28}  {bar(fmt_pct, 18)}  {fmt_pct:6.1f}%  {icon}")
        print("  └──────────────────────────────────────────────────────────────┘")

    # ── Subjective Grading ───────────────────────────────────────────────
    print("\n📖  SUBJECTIVE ANSWER GRADING  —  Student Ability Simulation")
    print("-" * 70)
    sa_tested   = subj.get("short_answer", {}).get("tested", 0)
    desc_tested = subj.get("descriptive", {}).get("tested", 0)
    print(f"  Short Answer (avg) {bar(sa_score)}  {sa_score:6.2f}%  {status_icon(sa_score, 65, 45)}  (n={sa_tested})")
    sa_qb = subj.get("short_answer", {}).get("quality_breakdown", {})
    if sa_qb:
        for level, score in sa_qb.items():
            icon = "✅" if score >= 65 else ("⚠️ " if score >= 40 else "❌")
            print(f"    · {level:<22}  {bar(score, 18)}  {score:5.1f}%  {icon}")
    print()
    print(f"  Descriptive (avg)  {bar(desc_score)}  {desc_score:6.2f}%  {status_icon(desc_score, 60, 40)}  (n={desc_tested})")
    desc_qb = subj.get("descriptive", {}).get("quality_breakdown", {})
    if desc_qb:
        for level, score in desc_qb.items():
            icon = "✅" if score >= 60 else ("⚠️ " if score >= 35 else "❌")
            print(f"    · {level:<22}  {bar(score, 18)}  {score:5.1f}%  {icon}")

    # ── Keyword Extraction ───────────────────────────────────────────────
    print("\n🔑  KEYWORD EXTRACTION (NLP)")
    print("-" * 70)
    print(f"  Precision          {bar(kw_precision)}  {kw_precision:6.2f}%  {status_icon(kw_precision, 50, 30)}")
    print(f"  Recall             {bar(kw_recall)}  {kw_recall:6.2f}%  {status_icon(kw_recall, 50, 30)}")
    print(f"  F1-Score           {bar(kw_f1)}  {kw_f1:6.2f}%  {status_icon(kw_f1, 40, 20)}")

    # ── Overall Score ────────────────────────────────────────────────────
    print("\n🏆  OVERALL PERFORMANCE SCORE  —  Weighted Breakdown")
    print("-" * 70)
    weight_map = {
        'mcq_accuracy':      ('MCQ Grading (35%)',            0.35),
        'question_validity': ('Question Validity (25%)',       0.25),
        'subjective_short':  ('Short Answer Grading (20%)',    0.20),
        'subjective_desc':   ('Descriptive Grading (10%)',     0.10),
        'keyword_f1':        ('Keyword Extraction F1 (10%)',   0.10),
    }
    for key, (label, weight) in weight_map.items():
        val = ov.get(key, 0)
        contribution = val * weight
        icon = "✅" if val >= 70 else ("⚠️ " if val >= 50 else "❌")
        print(f"  {label:<32}  {val:6.2f}%  →  contrib: {contribution:5.2f}  {icon}")
    print()
    print(f"  {'Overall Score':<32}  {bar(overall)}  {overall:6.2f}%  {status_icon(overall, 70, 55)}")

    print("\n💡  PERFORMANCE SUMMARY")
    print("-" * 70)
    if overall >= 85:
        print("  ✅  Excellent! The system is production-ready for school use.")
    elif overall >= 70:
        print("  ✅  Good performance. System is reliable for school use.")
    elif overall >= 55:
        print("  ⚠️   Moderate performance. MCQ handles 6/8 real formats; NLP keyword")
        print("       extraction needs more domain training data to improve.")
    else:
        print("  ❌  Low performance. More training data recommended.")

    print()
    strengths = []
    if mcq_accuracy >= 70: strengths.append("MCQ grading")
    if qg_validity >= 50:  strengths.append("question generation")
    if sa_score >= 50:     strengths.append("short-answer scoring")
    if strengths:
        print(f"  💪  Strengths    : {', '.join(strengths)}")
    weaknesses = []
    if kw_f1 < 30:         weaknesses.append("keyword extraction (NLP precision)")
    if desc_score < 50:    weaknesses.append("descriptive grading (partial answers)")
    if weaknesses:
        print(f"  🔧  Needs work   : {', '.join(weaknesses)}")

    print(f"\n  Tip: Run with --rerun flag to re-evaluate with fresh results.")
    print("=" * 70 + "\n")


if __name__ == "__main__":
    display_accuracy()

