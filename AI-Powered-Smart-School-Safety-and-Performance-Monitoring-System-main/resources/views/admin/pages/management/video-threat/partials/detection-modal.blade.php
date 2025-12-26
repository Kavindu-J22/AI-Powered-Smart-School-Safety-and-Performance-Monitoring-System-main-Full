<!-- Detection Alert Modal -->
<div class="modal fade" id="detectionModal" tabindex="-1" aria-labelledby="detectionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detectionModalLabel">
                    <i class="material-symbols-rounded me-2" id="modalIcon">warning</i>
                    <span id="modalTitle">Detection Alert</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalContent">
                    <!-- Dynamic content will be inserted here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="acknowledgeBtn">Acknowledge</button>
            </div>
        </div>
    </div>
</div>

