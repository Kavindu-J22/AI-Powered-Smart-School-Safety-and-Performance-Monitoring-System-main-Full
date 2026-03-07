{{--
    RFID Wristband Enrollment Modal
    Usage: @include('admin.pages.management.students.partials.rfid_modal', ['student' => $student])

    Requires in the @section('js') that loaded the form:
      window.rfidEnrollmentStartUrl   — POST endpoint
      window.rfidEnrollmentPollUrl    — GET endpoint base (token appended)
      window.rfidAssignUrl            — POST endpoint
      window.rfidRemoveUrl            — DELETE endpoint (built per student)
--}}

<div class="modal fade" id="rfidEnrollmentModal" tabindex="-1" aria-labelledby="rfidModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-3">

            {{-- Header --}}
            <div class="modal-header bg-primary text-white rounded-top-3">
                <h5 class="modal-title fw-semibold" id="rfidModalLabel">
                    <i class="material-symbols-rounded me-2 align-middle" style="font-size:1.3rem">contactless</i>
                    Assign RFID Wristband
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                    onclick="rfidModal.cancel()"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body text-center py-4 px-4">

                {{-- Step 1: Waiting for scan --}}
                <div id="rfidStep_waiting">
                    <div class="mb-3">
                        <span class="rfid-pulse-icon">
                            <i class="material-symbols-rounded text-primary" style="font-size:4rem">contactless</i>
                        </span>
                    </div>
                    <p class="fw-semibold fs-5 mb-1">Place the wristband on the reader</p>
                    <p class="text-muted small mb-3">Make sure the RF reader is connected and running</p>
                    <div class="spinner-border text-primary" role="status" id="rfidSpinner">
                        <span class="visually-hidden">Waiting…</span>
                    </div>
                    <p class="text-muted small mt-2" id="rfidStatusText">Waiting for wristband…</p>
                </div>

                {{-- Step 2: Card detected --}}
                <div id="rfidStep_detected" class="d-none">
                    <div class="mb-3">
                        <i class="material-symbols-rounded text-success" style="font-size:4rem">nfc</i>
                    </div>
                    <p class="fw-semibold fs-5 mb-1 text-success">Wristband Detected!</p>
                    <p class="text-muted small mb-2">Card UID:</p>
                    <div class="alert alert-success py-2 mb-3">
                        <code id="rfidDetectedUid" class="fs-5 fw-bold"></code>
                    </div>
                    <p class="text-muted small">Click <strong>Confirm</strong> to assign this wristband to the student.
                    </p>
                </div>

                {{-- Step 3: Error --}}
                <div id="rfidStep_error" class="d-none">
                    <div class="mb-3">
                        <i class="material-symbols-rounded text-danger" style="font-size:4rem">error</i>
                    </div>
                    <p class="fw-semibold fs-5 text-danger mb-2">Something went wrong</p>
                    <p class="text-muted small" id="rfidErrorText"></p>
                </div>

            </div>

            {{-- Footer --}}
            <div class="modal-footer justify-content-center border-0 pt-0 pb-3">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal"
                    onclick="rfidModal.cancel()">
                    Cancel
                </button>
                <button type="button" class="btn btn-primary px-4 d-none" id="rfidConfirmBtn"
                    onclick="rfidModal.confirm()">
                    <i class="material-symbols-rounded me-1 align-middle" style="font-size:1.1rem">check_circle</i>
                    Confirm
                </button>
                <button type="button" class="btn btn-warning px-4 d-none" id="rfidRetryBtn"
                    onclick="rfidModal.retry()">
                    <i class="material-symbols-rounded me-1 align-middle" style="font-size:1.1rem">refresh</i>
                    Retry
                </button>
            </div>

        </div>
    </div>
</div>

{{-- Pulse animation --}}
<style>
    @keyframes rfid-pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }

        50% {
            transform: scale(1.15);
            opacity: 0.7;
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .rfid-pulse-icon {
        display: inline-block;
        animation: rfid-pulse 1.4s ease-in-out infinite;
    }
</style>

<script>
    const rfidModal = (() => {
        let _token = null;
        let _pollTimer = null;
        let _detectedUid = null;
        const POLL_MS = 1500;

        const el = id => document.getElementById(id);

        function showStep(step) {
            ['waiting', 'detected', 'error'].forEach(s =>
                el(`rfidStep_${s}`).classList.toggle('d-none', s !== step)
            );
            el('rfidConfirmBtn').classList.toggle('d-none', step !== 'detected');
            el('rfidRetryBtn').classList.toggle('d-none', step !== 'error');
        }

        async function start(studentId) {
            _detectedUid = null;
            _token = null;
            showStep('waiting');
            el('rfidStatusText').textContent = 'Starting enrollment session…';

            try {
                const resp = await fetch(window.rfidEnrollmentStartUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({}),
                });
                const data = await resp.json();
                if (!data.success) throw new Error(data.message ?? 'Failed to start enrollment');

                _token = data.token;
                el('rfidStatusText').textContent = 'Waiting for wristband…';
                _startPolling(studentId);
            } catch (err) {
                _showError(err.message);
            }
        }

        function _startPolling(studentId) {
            _pollTimer = setInterval(async () => {
                try {
                    const resp = await fetch(`${window.rfidEnrollmentPollUrl}/${_token}`);
                    const data = await resp.json();
                    if (data.found && data.uid) {
                        _stopPolling();
                        _detectedUid = data.uid;
                        el('rfidDetectedUid').textContent = data.uid;
                        showStep('detected');
                    }
                } catch (err) {
                    // Network blip — keep polling
                }
            }, POLL_MS);
        }

        function _stopPolling() {
            if (_pollTimer) {
                clearInterval(_pollTimer);
                _pollTimer = null;
            }
        }

        function _showError(msg) {
            _stopPolling();
            el('rfidErrorText').textContent = msg;
            showStep('error');
        }

        async function confirm_() {
            if (!_detectedUid) return;

            const studentId = document.getElementById('rfidHiddenStudentId').value;
            el('rfidConfirmBtn').disabled = true;
            el('rfidConfirmBtn').innerHTML =
                '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

            try {
                const resp = await fetch(window.rfidAssignUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        student_id: studentId,
                        rfid_uid: _detectedUid
                    }),
                });
                const data = await resp.json();

                if (data.success) {
                    // Update the displayed UID badge and hidden input in the form
                    const uidBadge = document.getElementById('rfidCurrentUidBadge');
                    const hiddenInput = document.getElementById('rfid_uid_input');
                    if (uidBadge) uidBadge.textContent = _detectedUid;
                    if (hiddenInput) hiddenInput.value = _detectedUid;

                    const rfidSection = document.getElementById('rfidAssignedSection');
                    const noRfidSection = document.getElementById('rfidNotAssignedSection');
                    if (rfidSection) rfidSection.classList.remove('d-none');
                    if (noRfidSection) noRfidSection.classList.add('d-none');

                    bootstrap.Modal.getInstance(document.getElementById('rfidEnrollmentModal')).hide();
                } else {
                    _showError(data.message ?? 'Failed to assign wristband');
                }
            } catch (err) {
                _showError('Network error: ' + err.message);
            } finally {
                el('rfidConfirmBtn').disabled = false;
                el('rfidConfirmBtn').innerHTML =
                    '<i class="material-symbols-rounded me-1 align-middle" style="font-size:1.1rem">check_circle</i>Confirm';
            }
        }

        function cancel() {
            _stopPolling();
        }

        function retry() {
            start(document.getElementById('rfidHiddenStudentId').value);
        }

        return {
            start,
            confirm: confirm_,
            cancel,
            retry
        };
    })();

    // Called from the "Assign Wristband" button in the form
    function openRfidModal(studentId) {
        document.getElementById('rfidHiddenStudentId').value = studentId;
        rfidModal.start(studentId);
        const modal = new bootstrap.Modal(document.getElementById('rfidEnrollmentModal'));
        modal.show();
    }

    // Called from the "Remove" link
    async function removeRfid(studentId, removeUrl) {
        if (!confirm('Remove RFID wristband from this student?')) return;
        try {
            const resp = await fetch(removeUrl, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
            });
            const data = await resp.json();
            if (data.success) {
                document.getElementById('rfidAssignedSection').classList.add('d-none');
                document.getElementById('rfidNotAssignedSection').classList.remove('d-none');
                const hiddenInput = document.getElementById('rfid_uid_input');
                if (hiddenInput) hiddenInput.value = '';
            } else {
                alert(data.message ?? 'Failed to remove wristband');
            }
        } catch (err) {
            alert('Network error: ' + err.message);
        }
    }
</script>
