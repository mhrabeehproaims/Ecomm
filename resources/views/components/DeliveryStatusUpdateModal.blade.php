<div class="modal fade" id="addFollowupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="followupForm">
                @csrf
                <input type="hidden" name="user_email" id="user_email">
                <input type="hidden" name="order_id" id="order_id">

                <div class="modal-header">
                    <h5 class="modal-title">Add Delivery Follow-up</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Status</label>
                        <select name="delivery_status" id="delivery_status" class="delivery_status form-select">
                            <option value="call">Call</option>
                            <option value="called">Called</option>
                            <option value="follow-up">Follow-up</option>
                            <option value="delivered">Delivered</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Follow-up Note</label>
                        <textarea name="note" class="form-control note" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Insert Follow-up</button>
                </div>
            </form>
        </div>
    </div>
</div>
