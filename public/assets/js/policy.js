$(document).ready(function () {

    let table = $('#datatable').DataTable();

    $('#create_policy').on('click', function () {
        $("#policyModalLabel").text('New Policy');


        $("#hidden_id").val('');
        $("#policy_title").val('');
        $("#return_days").val(7);
        $("#return_type").val('refund_or_exchange').change();
        $("#policy_status").val(1);
        $("#policy_conditions").val('');
        $(".btn.btn-primary").text('Save');
        $('#policyModal').modal('show');
    });

    $.ajaxSetup({
        headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $(document).on('click', '.editBtn', function () {
        let id = $(this).data('id');
        let title = $(this).data('title');
        let return_days = $(this).data('return_days');
        let return_type = $(this).data('return_type');
        let status = $(this).data('status');
        let conditions = $(this).data('conditions');

        $("#hidden_id").val(id);
        $("#policy_title").val(title);
        $("#return_days").val(return_days);
        $("#return_type").val(return_type).change();
        $("#policy_status").val(status).change();
        $("#policy_conditions").val(conditions);
        $("#policyModalLabel").text('Update Policy');
        $(".btn.btn-primary").text('update');


        $("#policyModal").modal('show');
    });

    $("#create-policy").submit(function (e) {
        e.preventDefault();

        let id = $("#hidden_id").val();
        let url = id ? "/policies/" + id : "/policies";

        let formData = new FormData(this);
        if (id) {
            formData.append("_method", "PUT");
        }

        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function () {
                $(".btn.btn-primary").text('Please wait...');
            },
            success: function (response) {
                if (response.message === "success") {
                    $("#policyModal").modal('hide');
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function (xhr) {
                let response = xhr.responseJSON;
                let errors = response.errors;
                let firstError = '';

                if (errors) {
                    for (let key in errors) {
                        if (errors[key].length > 0) {
                            firstError = errors[key][0];
                            break;
                        }
                    }
                }

                alert(firstError || response.message || 'Something went wrong');
                $(".btn.btn-primary").text($("#hidden_id").val() ? 'Update' : 'Save');
            }
        });
    });


    let row;

    $(document).on('click', '.BtnDelete', function () {
        let id = $(this).data('id');
        let name = $(this).data('name');
        row = $(this).closest('tr');
        $(".name").text(name);
        $("#hidden_id").val(id);
        $(".btn.btn-primary").text('Confirm');

        $("#DeletePolicyModal").modal('show');
    });

    $("#delete-policy").on('submit', function (e) {
        e.preventDefault();
        let id = $('#hidden_id').val();

        $.ajax({
            method: 'DELETE',
            url: '/policies/' + id,
            dataType: 'JSON',
            success: function (response) {
                console.log(response.status);
                if (response.status == "success") {
                    // table.row(row).remove().draw(false);
                    // $("#datatable").focus();
                    $("#DeletePolicyModal").modal('hide');
                    window.location.reload();
                } else {
                    alert(response.status);
                }
            },
            error: function (xhr) {
                let response = JSON.parse(xhr.responseText);

                // console.log(response.status);
                alert(response.status);
            }
        });
    });

});

