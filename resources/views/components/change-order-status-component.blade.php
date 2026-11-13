<div class="modal fade modal-md" id="showStatusModal" tabindex="-1" aria-labelledby="showStatusModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="changeStatusForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="showStatusModalLabel">Update Order Status</h5>
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
                            <option value="ordered">Ordered</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="processing">Processing</option>
                            <option value="despatched">Despatched</option>
                            <option value="ontransist" hidden>On-Transist</option>
                            <option value="delivered">Delivered</option>
                            <option value="reissued" disabled>Reissued</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="courier-partner-section" style="display:none">
                        <div class="mb-3">
                            <label for="statusSelect" class="form-label">Select Courier
                                Partner</label>
                            <select name="selectAgent" id="selectAgent" class="form-select" required>

                                @forelse ($deliveryAgents as $agent)
                                    <option value="{{ $agent->partner_id }}">
                                        {{ $agent->partner_name }}
                                    </option>
                                @empty
                                    <option value="">No Courier Partner Found</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="statusSelect" class="form-label">Unique Tracking
                                ID</label>
                            <input type="text" class="form-control" id="unique_tracking_id" name="unique_tracking_id"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="statusSelect" class="form-label">Approx. Delivery
                                Date</label>
                            <input type="date" class="form-control" id="approx_delivery_date"
                                name="approx_delivery_date" required>
                        </div>
                        <div class="mb-3">
                            <label for="statusSelect" class="form-label">Delivery
                                Charge</label>
                            <input type="number" class="form-control" id="approx_delivery_charge"
                                name="approx_delivery_charge">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="confirmBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>