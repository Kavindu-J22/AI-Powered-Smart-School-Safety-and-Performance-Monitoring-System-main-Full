@extends('admin.layouts.app')

@section('css')
@vite(['resources/css/admin/audio-video-threat.css'])
@endsection

@section('content')
@include('admin.layouts.sidebar')

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    @include('admin.layouts.navbar')

    <div class="container-fluid py-4">

        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="mb-0">
                            <i class="material-symbols-rounded me-2">settings</i>
                            Classroom IoT Device Setup
                        </h4>
                        <p class="text-sm text-secondary mb-0">Configure camera and audio devices for each classroom</p>
                    </div>
                    <a href="{{ route('admin.management.audio-video-threat.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="material-symbols-rounded text-sm">arrow_back</i> Back to Detection
                    </a>
                </div>
            </div>
        </div>

        <!-- Info Banner -->
        <div class="alert alert-info d-flex align-items-center gap-2 mb-4 py-2">
            <i class="material-symbols-rounded">info</i>
            <span class="text-sm">Set the <strong>Camera IP / Port</strong> (ESP32-CAM) and <strong>Audio IP / Port</strong> (Separate wired or WiFi audio module) for each classroom. Toggle <strong>Camera Off</strong> or <strong>Mic Off</strong> to disable a device. Changes are saved per classroom.</span>
        </div>

        <!-- Classrooms Table Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header pb-0">
                <h6 class="mb-0"><i class="material-symbols-rounded text-sm me-1">meeting_room</i> All Classrooms</h6>
            </div>
            <div class="card-body px-0">
                <div class="table-responsive">
                    <table class="table align-items-center mb-0" id="classroomSetupTable">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs fw-bold ps-3">Classroom</th>
                                <th class="text-uppercase text-secondary text-xxs fw-bold">Grade / Section</th>
                                <th class="text-uppercase text-secondary text-xxs fw-bold">Room</th>
                                <th class="text-uppercase text-secondary text-xxs fw-bold">Camera IP / Port (ESP32-CAM)</th>
                                <th class="text-uppercase text-secondary text-xxs fw-bold text-center">Camera Off</th>
                                <th class="text-uppercase text-secondary text-xxs fw-bold">Audio IP / Port (Wired/WiFi Module)</th>
                                <th class="text-uppercase text-secondary text-xxs fw-bold text-center">Mic Off</th>
                                <th class="text-uppercase text-secondary text-xxs fw-bold text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($classrooms as $cls)
                            <tr data-classroom-id="{{ $cls->id }}">
                                <td class="ps-3">
                                    <span class="fw-bold text-sm text-dark">{{ $cls->class_name }}</span>
                                    @if($cls->status === 'inactive')
                                    <span class="badge bg-secondary ms-1" style="font-size:10px;">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-sm">Grade {{ $cls->grade_level }}{{ $cls->section ? ' – '.$cls->section : '' }}</span>
                                </td>
                                <td>
                                    <span class="text-sm text-secondary">{{ $cls->room_number ?: '—' }}</span>
                                </td>
                                <td style="min-width:210px;">
                                    <input type="text" class="form-control form-control-sm camera-ip-input mb-1"
                                        value="{{ $cls->camera_ip }}" placeholder="192.168.1.101"
                                        title="ESP32-CAM streaming IP">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text text-xs px-2">Port</span>
                                        <input type="number" class="form-control form-control-sm camera-port-input"
                                            value="{{ $cls->camera_port ?? '80' }}" placeholder="80" min="1" max="65535"
                                            title="Camera streaming port (default 80)">
                                    </div>
                                    <div class="text-xs text-muted mt-1">URL: <code>http://&lt;ip&gt;:&lt;port&gt;/stream</code></div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center mb-0">
                                        <input class="form-check-input camera-off-toggle" type="checkbox"
                                            {{ $cls->camera_off ? 'checked' : '' }}
                                            title="Disable camera for this classroom">
                                    </div>
                                </td>
                                <td style="min-width:210px;">
                                    <input type="text" class="form-control form-control-sm audio-ip-input mb-1"
                                        value="{{ $cls->audio_ip }}" placeholder="192.168.1.102"
                                        title="Wired or WiFi audio module IP">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text text-xs px-2">Port</span>
                                        <input type="number" class="form-control form-control-sm audio-port-input"
                                            value="{{ $cls->audio_port ?? '5002' }}" placeholder="5002" min="1" max="65535"
                                            title="Audio module port (default 5002)">
                                    </div>
                                    <div class="text-xs text-muted mt-1">Separate wired or WiFi audio module</div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center mb-0">
                                        <input class="form-check-input mic-off-toggle" type="checkbox"
                                            {{ $cls->mic_off ? 'checked' : '' }}
                                            title="Disable microphone for this classroom">
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-success btn-sm save-classroom-btn" data-id="{{ $cls->id }}">
                                        <i class="material-symbols-rounded text-sm">save</i> Save
                                    </button>
                                    <span class="save-status ms-1 text-xs" style="display:none;"></span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-secondary py-4">No classrooms found. Add classrooms first.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</main>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrf = '{{ csrf_token() }}';
        const saveUrl = '{{ route("admin.management.audio-video-threat.classroom-setup.save") }}';

        document.querySelectorAll('.save-classroom-btn').forEach(function(btn) {
            btn.addEventListener('click', async function() {
                const row = btn.closest('tr');
                const classroomId = btn.dataset.id;
                const cameraIp = row.querySelector('.camera-ip-input').value.trim();
                const cameraPort = row.querySelector('.camera-port-input').value.trim() || '80';
                const cameraOff = row.querySelector('.camera-off-toggle').checked;
                const audioIp = row.querySelector('.audio-ip-input').value.trim();
                const audioPort = row.querySelector('.audio-port-input').value.trim() || '5002';
                const micOff = row.querySelector('.mic-off-toggle').checked;
                const statusEl = row.querySelector('.save-status');

                btn.disabled = true;
                btn.innerHTML = '<i class="material-symbols-rounded text-sm">hourglass_empty</i> Saving…';

                try {
                    const resp = await fetch(saveUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        body: JSON.stringify({
                            classroom_id: classroomId,
                            camera_ip: cameraIp,
                            camera_port: cameraPort,
                            camera_off: cameraOff ? 1 : 0,
                            audio_ip: audioIp,
                            audio_port: audioPort,
                            mic_off: micOff ? 1 : 0,
                        })
                    });
                    const data = await resp.json();

                    if (data.success) {
                        statusEl.textContent = '✓ Saved';
                        statusEl.style.color = 'green';
                        statusEl.style.display = '';
                        setTimeout(() => {
                            statusEl.style.display = 'none';
                        }, 3000);
                    } else {
                        statusEl.textContent = '✗ Error';
                        statusEl.style.color = 'red';
                        statusEl.style.display = '';
                    }
                } catch (e) {
                    statusEl.textContent = '✗ Failed';
                    statusEl.style.color = 'red';
                    statusEl.style.display = '';
                }

                btn.disabled = false;
                btn.innerHTML = '<i class="material-symbols-rounded text-sm">save</i> Save';
            });
        });
    });
</script>
@endsection