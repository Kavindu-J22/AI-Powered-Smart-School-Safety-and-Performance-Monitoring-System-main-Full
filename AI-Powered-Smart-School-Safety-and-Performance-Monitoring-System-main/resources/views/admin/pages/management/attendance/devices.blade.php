@extends('admin.layouts.app')

@section('title', 'Attendance Devices')

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0">Attendance Devices</h5>
                                <p class="text-sm mb-0">Manage WiFi RFID attendance readers</p>
                            </div>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
                                <i class="fas fa-plus me-2"></i>Register New Device
                            </button>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Device</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Status</th>
                                        <th
                                            class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Location</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Last Seen</th>
                                        <th
                                            class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Today's Scans</th>
                                        <th class="text-secondary opacity-7"></th>
                                    </tr>
                                </thead>
                                <tbody id="devicesTableBody">
                                    <!-- Devices will be loaded here via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Sync Records -->
        <div class="row mt-4" id="pendingSyncSection" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0">Pending Sync Records</h5>
                                <p class="text-sm mb-0">Offline attendance records waiting to be synced</p>
                            </div>
                            <button class="btn btn-success btn-sm" onclick="syncAllDevices()">
                                <i class="fas fa-sync me-2"></i>Sync All
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="pendingRecordsList">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Device Configuration Modal -->
        <div class="modal fade" id="configModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Device Configuration</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="configForm">
                            <input type="hidden" id="configDeviceId">

                            <div class="mb-3">
                                <label class="form-label">WiFi SSID</label>
                                <input type="text" class="form-control" id="configWifiSsid" placeholder="Network Name">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">WiFi Password</label>
                                <input type="password" class="form-control" id="configWifiPassword"
                                    placeholder="Network Password">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Server URL</label>
                                <input type="text" class="form-control" id="configServerUrl"
                                    value="http://{{ request()->getHost() }}:{{ request()->getPort() }}/api/attendance/rfid-scan">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Device Location</label>
                                <input type="text" class="form-control" id="configLocation"
                                    placeholder="e.g., Main Entrance">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Scan Cooldown (seconds)</label>
                                <input type="number" class="form-control" id="configCooldown" value="3" min="1"
                                    max="10">
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Note:</strong> Generate configuration code to upload to your Arduino device.
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="generateConfig()">
                            <i class="fas fa-code me-2"></i>Generate Config Code
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Generated Config Code Modal -->
        <div class="modal fade" id="configCodeModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Arduino Configuration Code</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Copy this code and paste it into your Arduino sketch (lines 76-84):</p>
                        <pre id="generatedConfigCode" class="bg-dark text-white p-3 rounded" style="max-height: 400px; overflow-y: auto;"></pre>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="copyConfigCode()">
                            <i class="fas fa-copy me-2"></i>Copy to Clipboard
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Device Modal -->
        <div class="modal fade" id="addDeviceModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Register New Device</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addDeviceForm">
                            <div class="mb-3">
                                <label class="form-label">Device ID</label>
                                <input type="text" class="form-control" id="newDeviceId"
                                    placeholder="e.g., ATTENDANCE_READER_01" required>
                                <small class="text-muted">Unique identifier for this device</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Device Name</label>
                                <input type="text" class="form-control" id="newDeviceName"
                                    placeholder="e.g., Main Entrance Reader">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" id="newDeviceLocation"
                                    placeholder="e.g., Main Building Entrance">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="registerDevice()">
                            <i class="fas fa-save me-2"></i>Register Device
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            let devices = [];

            // Load devices on page load
            $(document).ready(function() {
                loadDevices();
                setInterval(loadDevices, 30000); // Refresh every 30 seconds
            });

            function loadDevices() {
                $.ajax({
                    url: '{{ route('admin.management.attendance.devices.list') }}',
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            devices = response.devices;
                            renderDevices();
                        }
                    },
                    error: function(xhr) {
                        console.error('Failed to load devices', xhr);
                    }
                });
            }

            function renderDevices() {
                const tbody = $('#devicesTableBody');
                tbody.empty();

                if (devices.length === 0) {
                    tbody.append(`
            <tr>
                <td colspan="6" class="text-center text-secondary py-4">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>No devices registered yet. Click "Register New Device" to add one.</p>
                </td>
            </tr>
        `);
                    return;
                }

                devices.forEach(device => {
                    const statusBadge = getStatusBadge(device.status, device.last_seen);
                    const lastSeen = device.last_seen ? moment(device.last_seen).fromNow() : 'Never';

                    tbody.append(`
            <tr>
                <td>
                    <div class="d-flex px-2 py-1">
                        <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">${device.device_name || device.device_id}</h6>
                            <p class="text-xs text-secondary mb-0">${device.device_id}</p>
                        </div>
                    </div>
                </td>
                <td>
                    ${statusBadge}
                </td>
                <td>
                    <p class="text-xs font-weight-bold mb-0">${device.location || 'Not set'}</p>
                </td>
                <td class="align-middle text-center">
                    <span class="text-secondary text-xs font-weight-bold">${lastSeen}</span>
                </td>
                <td class="align-middle text-center">
                    <span class="badge badge-sm bg-gradient-success">${device.today_scans || 0}</span>
                </td>
                <td class="align-middle">
                    <div class="dropdown">
                        <button class="btn btn-link text-secondary mb-0" data-bs-toggle="dropdown">
                            <i class="fa fa-ellipsis-v text-xs"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="showDeviceConfig('${device.device_id}')">
                                <i class="fas fa-cog me-2"></i>Configure
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="syncDevice('${device.device_id}')">
                                <i class="fas fa-sync me-2"></i>Sync Now
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="viewDeviceLogs('${device.device_id}')">
                                <i class="fas fa-file-alt me-2"></i>View Logs
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="removeDevice('${device.device_id}')">
                                <i class="fas fa-trash me-2"></i>Remove Device
                            </a></li>
                        </ul>
                    </div>
                </td>
            </tr>
        `);
                });
            }

            function getStatusBadge(status, lastSeen) {
                const now = moment();
                const lastSeenTime = moment(lastSeen);
                const minutesAgo = now.diff(lastSeenTime, 'minutes');

                if (!lastSeen || minutesAgo > 10) {
                    return '<span class="badge badge-sm bg-gradient-secondary">Offline</span>';
                } else if (minutesAgo < 2) {
                    return '<span class="badge badge-sm bg-gradient-success">Online</span>';
                } else {
                    return '<span class="badge badge-sm bg-gradient-warning">Idle</span>';
                }
            }

            function registerDevice() {
                const deviceId = $('#newDeviceId').val();
                const deviceName = $('#newDeviceName').val();
                const location = $('#newDeviceLocation').val();

                if (!deviceId) {
                    alert('Device ID is required');
                    return;
                }

                $.ajax({
                    url: '{{ route('admin.management.attendance.devices.register') }}',
                    method: 'POST',
                    data: {
                        device_id: deviceId,
                        device_name: deviceName,
                        location: location,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#addDeviceModal').modal('hide');
                            $('#addDeviceForm')[0].reset();
                            loadDevices();
                            showNotification('success', 'Device registered successfully');
                        }
                    },
                    error: function(xhr) {
                        showNotification('error', 'Failed to register device');
                    }
                });
            }

            function showDeviceConfig(deviceId) {
                $('#configDeviceId').val(deviceId);
                $('#configModal').modal('show');
            }

            function generateConfig() {
                const deviceId = $('#configDeviceId').val();
                const wifiSsid = $('#configWifiSsid').val();
                const wifiPassword = $('#configWifiPassword').val();
                const serverUrl = $('#configServerUrl').val();
                const location = $('#configLocation').val();
                const cooldown = $('#configCooldown').val();

                const configCode = `// WiFi Configuration
const char* WIFI_SSID = "${wifiSsid || 'YOUR_WIFI_SSID'}";
const char* WIFI_PASSWORD = "${wifiPassword || 'YOUR_WIFI_PASSWORD'}";

// Server Configuration
const char* SERVER_URL = "${serverUrl}";
const char* API_TOKEN = "";  // Optional

// Device Configuration
const char* DEVICE_ID = "${deviceId}";
const int SCAN_COOLDOWN = ${cooldown || 3}000;  // milliseconds`;

                $('#generatedConfigCode').text(configCode);
                $('#configModal').modal('hide');
                $('#configCodeModal').modal('show');
            }

            function copyConfigCode() {
                const code = $('#generatedConfigCode').text();
                navigator.clipboard.writeText(code).then(function() {
                    showNotification('success', 'Configuration copied to clipboard');
                }, function() {
                    showNotification('error', 'Failed to copy to clipboard');
                });
            }

            function syncDevice(deviceId) {
                if (!confirm('Sync pending records from this device?')) {
                    return;
                }

                $.ajax({
                    url: '{{ route('admin.management.attendance.devices.sync') }}',
                    method: 'POST',
                    data: {
                        device_id: deviceId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('success', response.message);
                            loadDevices();
                        } else {
                            showNotification('warning', response.message);
                        }
                    },
                    error: function(xhr) {
                        showNotification('error', 'Failed to sync device');
                    }
                });
            }

            function removeDevice(deviceId) {
                if (!confirm('Are you sure you want to remove this device?')) {
                    return;
                }

                $.ajax({
                    url: '{{ route('admin.management.attendance.devices.remove') }}',
                    method: 'DELETE',
                    data: {
                        device_id: deviceId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('success', 'Device removed successfully');
                            loadDevices();
                        }
                    },
                    error: function(xhr) {
                        showNotification('error', 'Failed to remove device');
                    }
                });
            }

            function showNotification(type, message) {
                const bgColor = type === 'success' ? 'bg-gradient-success' :
                    type === 'error' ? 'bg-gradient-danger' :
                    'bg-gradient-warning';

                $.notify({
                    icon: type === 'success' ? 'fas fa-check' : 'fas fa-exclamation-triangle',
                    message: message
                }, {
                    type: bgColor,
                    timer: 3000,
                    placement: {
                        from: 'top',
                        align: 'right'
                    }
                });
            }
        </script>
    @endpush
@endsection
