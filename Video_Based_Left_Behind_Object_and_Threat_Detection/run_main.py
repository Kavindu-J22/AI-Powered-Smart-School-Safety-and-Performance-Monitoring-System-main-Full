"""
Wrapper script to run main.py with progress indication
Helps with PyTorch slow loading on Windows
"""

import sys
import time
import threading

def show_loading_animation():
    """Show loading animation while PyTorch loads"""
    animation = ["⠋", "⠙", "⠹", "⠸", "⠼", "⠴", "⠦", "⠧", "⠇", "⠏"]
    idx = 0
    start_time = time.time()
    
    while not loading_complete:
        elapsed = int(time.time() - start_time)
        sys.stdout.write(f"\r{animation[idx % len(animation)]} Loading PyTorch and models... ({elapsed}s elapsed)")
        sys.stdout.flush()
        time.sleep(0.1)
        idx += 1
    
    sys.stdout.write("\r✓ Loading complete!                                    \n")
    sys.stdout.flush()

# Global flag
loading_complete = False

print("=" * 70)
print(" Video-Based Left Behind Object and Threat Detection System")
print("=" * 70)
print("\nInitializing system...")
print("Note: First run may take 1-5 minutes on Windows due to PyTorch loading")
print("      This is normal. Please be patient...\n")

# Start loading animation in background
animation_thread = threading.Thread(target=show_loading_animation, daemon=True)
animation_thread.start()

try:
    # Import main module (this is where PyTorch loads)
    from main import main
    
    # Stop animation
    loading_complete = True
    time.sleep(0.2)  # Give animation thread time to finish
    
    print("\n" + "=" * 70)
    print(" System loaded successfully!")
    print("=" * 70)
    
    # Run main application
    sys.exit(main())
    
except KeyboardInterrupt:
    loading_complete = True
    print("\n\n✗ Interrupted by user")
    print("\nIf PyTorch is taking too long to load, see PYTORCH_WINDOWS_FIX.md")
    print("for solutions to speed up loading on Windows.")
    sys.exit(1)
    
except Exception as e:
    loading_complete = True
    print(f"\n\n✗ Error: {e}")
    import traceback
    traceback.print_exc()
    sys.exit(1)

