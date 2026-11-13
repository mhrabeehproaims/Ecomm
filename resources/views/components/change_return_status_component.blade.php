<div class="modal fade modal-md" id="showStatusModal" tabindex="-1" aria-labelledby="showStatusModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="changeStatusForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="showStatusModalLabel">Update Sales Return Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- hidden input (inside form now) --}}
                <input type="hidden" name="hidden_id" id="hidden_id">

                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <span class="fw-semibold">Current Status:</span>
                        <span class="ms-2 info-value"></span>
                    </div>

                    <div class="mb-3">
                        <label for="statusSelect" class="form-label">Change Status</label>
                        <select name="statusSelect" id="statusSelect" class="form-select">
                            <option value="requested">Requested</option>
                            <option value="accepted">Accepted</option>
                            {{-- <option value="rejected">Rejected</option> --}}
                            <option value="picked_up">Picked_up</option>
                            <option value="collected">Collected</option>
                            <option value="refunded">Refunded</option>
                            <option value="replaced">Reissued</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>
