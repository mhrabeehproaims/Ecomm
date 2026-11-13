{{-- Delete modal component --}}
@props(['id', 'form_id'])

<div class="modal fade modal-md" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="{{ $form_id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="{{ $id }}Label">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                {{-- hidden input (inside form now) --}}
                <input type="hidden" name="hidden_id" id="hidden_id">

                <div class="modal-body">
                    <div class="container-fluid text-center">
                        <h4>Are you sure to delete <span class="name text-primary"></span>?</h4>
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
