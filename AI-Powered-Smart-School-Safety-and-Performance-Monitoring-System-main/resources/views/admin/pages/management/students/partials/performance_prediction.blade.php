<!-- Performance Prediction Card -->
<div class="card mb-4" id="performancePredictionCard">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0 d-flex align-items-center">
            <i class="material-symbols-rounded me-2 icon-size-sm">trending_up</i>
            Performance Prediction (AI Powered)
        </h6>
        <span class="badge bg-gradient-info badge-sm">Live Prediction</span>
    </div>
    <div class="card-body">
        <!-- Loading State -->
        <div id="predictionLoading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading predictions...</span>
            </div>
            <p class="text-muted mt-3">Analyzing student performance data...</p>
        </div>

        <!-- Error State -->
        <div id="predictionError" class="alert alert-warning alert-dismissible fade show" role="alert"
            style="display: none;">
            <i class="material-symbols-rounded align-middle me-2">error</i>
            <span id="errorMessage"></span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <!-- Predictions Content -->
        <div id="predictionContent" style="display: none;">
            <!-- Overall Summary -->
            <div class="row mb-4" id="predictionSummary">
                <div class="col-md-12">
                    <div class="alert alert-gradient-info alert-with-icon" role="alert">
                        <span class="alert-icon text-white"><i class="material-symbols-rounded">info</i></span>
                        <span class="alert-text">
                            <strong>Prediction Summary:</strong> Based on current marks and attendance, the AI model
                            predicts student's performance trajectory for next term.
                        </span>
                    </div>
                </div>
            </div>

            <!-- Predictions Table -->
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Subject</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Current</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Predicted
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Improvement
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Confidence
                            </th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Trend</th>
                        </tr>
                    </thead>
                    <tbody id="predictionTableBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Detailed View -->
            <div class="row mt-4" id="detailedPredictions">
                <!-- Individual prediction cards populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

<style>
    .alert-gradient-info {
        background: linear-gradient(90deg, #3a86ff 0%, #5a9dff 100%);
        color: white;
        border: none;
    }

    .alert-gradient-info .alert-icon {
        background-color: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .prediction-card {
        border-left: 4px solid #3a86ff;
        transition: all 0.3s ease;
    }

    .prediction-card:hover {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .trend-badge {
        display: inline-block;
        padding: 0.35rem 0.65rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .trend-improving {
        background-color: #d4edda;
        color: #155724;
    }

    .trend-declining {
        background-color: #f8d7da;
        color: #721c24;
    }

    .trend-stable {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .confidence-bar {
        height: 6px;
        background-color: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
        margin-top: 0.5rem;
    }

    .confidence-fill {
        height: 100%;
        background: linear-gradient(90deg, #3a86ff, #5a9dff);
        transition: width 0.3s ease;
    }

    .performance-metric {
        text-align: center;
        padding: 1rem;
        border-radius: 0.5rem;
        background-color: #f7f8fa;
    }

    .performance-value {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0.5rem 0;
    }

    .improvement-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 500;
    }

    .improvement-positive {
        background-color: #d4edda;
        color: #155724;
    }

    .improvement-negative {
        background-color: #f8d7da;
        color: #721c24;
    }

    .improvement-neutral {
        background-color: #e2e3e5;
        color: #383d41;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadPerformancePrediction({{ $student->student_id }});
    });

    function loadPerformancePrediction(studentId) {
        const loadingEl = document.getElementById('predictionLoading');
        const errorEl = document.getElementById('predictionError');
        const contentEl = document.getElementById('predictionContent');

        // Show loading state
        loadingEl.style.display = 'block';
        errorEl.style.display = 'none';
        contentEl.style.display = 'none';

        // Fetch prediction from API
        fetch(`/api/students/${studentId}/prediction`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    displayPrediction(data.data);
                    loadingEl.style.display = 'none';
                    contentEl.style.display = 'block';
                } else if (data.status === 'no_data') {
                    loadingEl.style.display = 'none';
                    showError(data.message || 'No marks data available for this student.');
                } else {
                    showError(data.error || 'Failed to load prediction');
                }
            })
            .catch(error => {
                console.error('Error loading prediction:', error);
                showError(error.message ||
                    'Failed to connect to prediction service. Make sure the API is running on port 5002.');
            });
    }

    function displayPrediction(data) {
        if (!data.predictions || data.predictions.length === 0) {
            showError('No prediction data available for this student');
            return;
        }

        const tableBody = document.getElementById('predictionTableBody');
        const detailedContainer = document.getElementById('detailedPredictions');

        tableBody.innerHTML = '';
        detailedContainer.innerHTML = '';

        let totalImprovement = 0;
        let improvingSubjects = 0;

        data.predictions.forEach((pred, index) => {
            // Add table row
            const row = createTableRow(pred);
            tableBody.appendChild(row);

            // Add detailed card
            const card = createDetailCard(pred);
            detailedContainer.appendChild(card);

            // Calculate stats
            if (pred.improvement > 0) {
                improvingSubjects++;
            }
            totalImprovement += pred.improvement;
        });

        // Display summary stats
        displaySummaryStats(data, totalImprovement, improvingSubjects);
    }

    function createTableRow(prediction) {
        const row = document.createElement('tr');

        const improvementColor = prediction.improvement > 0 ? 'text-success' :
            prediction.improvement < 0 ? 'text-danger' : 'text-secondary';

        const trendColor = prediction.prediction_trend === 'improving' ? 'trend-improving' :
            prediction.prediction_trend === 'declining' ? 'trend-declining' : 'trend-stable';

        row.innerHTML = `
            <td>
                <span class="text-xs font-weight-bold">${prediction.subject}</span>
            </td>
            <td>
                <span class="text-xs">${prediction.current_performance.toFixed(1)}</span>
            </td>
            <td>
                <span class="text-xs font-weight-bold">${prediction.predicted_performance.toFixed(1)}</span>
            </td>
            <td>
                <span class="text-xs ${improvementColor} font-weight-bold">
                    ${prediction.improvement > 0 ? '+' : ''}${prediction.improvement.toFixed(1)}
                </span>
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <span class="text-xs me-2">${prediction.confidence.toFixed(0)}%</span>
                    <div class="confidence-bar" style="width: 60px;">
                        <div class="confidence-fill" style="width: ${Math.min(prediction.confidence, 100)}%"></div>
                    </div>
                </div>
            </td>
            <td>
                <span class="trend-badge ${trendColor}">
                    <i class="material-symbols-rounded align-middle" style="font-size: 0.9rem;">
                        ${prediction.prediction_trend === 'improving' ? 'trending_up' :
                          prediction.prediction_trend === 'declining' ? 'trending_down' : 'trending_flat'}
                    </i>
                    ${prediction.prediction_trend.charAt(0).toUpperCase() + prediction.prediction_trend.slice(1)}
                </span>
            </td>
        `;

        return row;
    }

    function createDetailCard(prediction) {
        const card = document.createElement('div');
        card.className = 'col-md-6 mb-3';

        const improvementClass = prediction.improvement > 0 ? 'improvement-positive' :
            prediction.improvement < 0 ? 'improvement-negative' : 'improvement-neutral';

        card.innerHTML = `
            <div class="card prediction-card">
                <div class="card-body p-3">
                    <h6 class="mb-2">${prediction.subject}</h6>

                    <div class="row g-2">
                        <div class="col-6">
                            <div class="performance-metric">
                                <small class="text-muted">Current</small>
                                <div class="performance-value text-primary">${prediction.current_performance.toFixed(1)}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="performance-metric">
                                <small class="text-muted">Predicted</small>
                                <div class="performance-value text-info">${prediction.predicted_performance.toFixed(1)}</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Improvement:</small>
                                <span class="improvement-badge ${improvementClass}">
                                    ${prediction.improvement > 0 ? '+' : ''}${prediction.improvement.toFixed(1)} points
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Confidence:</small>
                                <span class="text-sm font-weight-bold">${prediction.confidence.toFixed(0)}%</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Trend:</small>
                                <span class="trend-badge ${prediction.prediction_trend === 'improving' ? 'trend-improving' :
                                                          prediction.prediction_trend === 'declining' ? 'trend-declining' : 'trend-stable'}">
                                    ${prediction.prediction_trend.charAt(0).toUpperCase() + prediction.prediction_trend.slice(1)}
                                </span>
                            </div>
                        </div>
                        <div class="col-12 mt-2 p-2 bg-light rounded">
                            <small class="text-muted d-block mb-1">95% Confidence Range:</small>
                            <small class="text-dark font-weight-bold">
                                ${prediction.confidence_interval.lower_bound.toFixed(1)} - ${prediction.confidence_interval.upper_bound.toFixed(1)}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        `;

        return card;
    }

    function displaySummaryStats(data, totalImprovement, improvingSubjects) {
        const summary = document.getElementById('predictionSummary');
        const statsHtml = `
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="material-symbols-rounded text-primary mb-2" style="font-size: 2rem;">school</i>
                        <h6 class="mb-1">Total Subjects</h6>
                        <h3 class="text-primary">${data.total_subjects}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="material-symbols-rounded text-success mb-2" style="font-size: 2rem;">trending_up</i>
                        <h6 class="mb-1">Improving</h6>
                        <h3 class="text-success">${improvingSubjects}/${data.total_subjects}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="material-symbols-rounded text-info mb-2" style="font-size: 2rem;">average</i>
                        <h6 class="mb-1">Avg. Improvement</h6>
                        <h3 class="text-info">${(totalImprovement / data.total_subjects).toFixed(1)}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="material-symbols-rounded text-warning mb-2" style="font-size: 2rem;">psychology</i>
                        <h6 class="mb-1">AI Model</h6>
                        <h3 class="text-warning">v2.0</h3>
                    </div>
                </div>
            </div>
        `;

        // Insert after the info alert
        const infoAlert = summary.querySelector('.alert');
        infoAlert.insertAdjacentHTML('afterend', `<div class="row g-3 mt-3">${statsHtml}</div>`);
    }

    function showError(message) {
        const loadingEl = document.getElementById('predictionLoading');
        const errorEl = document.getElementById('predictionError');
        const contentEl = document.getElementById('predictionContent');
        const errorMessage = document.getElementById('errorMessage');

        loadingEl.style.display = 'none';
        contentEl.style.display = 'none';
        errorEl.style.display = 'block';
        errorMessage.textContent = message;
    }
</script>
