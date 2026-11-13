$(document).ready(function () {

    let table = $('#datatable').DataTable();

    $('#create_weight').on('click', function () {
        $("#weightModalLabel").text('Create New Weight');
        $("#weight_name").val('');
        $("#hidden_id").val('');
        $("#weight_status").val(1);
        $(".btn.btn-primary").text('Save');
        $('#WeightModal').modal('show');
    });

    $.ajaxSetup({
        headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $(document).on('click', '.editBtn', function () {
        let id = $(this).data('id');
        let name = $(this).data('weightname');
        let status = $(this).data('status');

        $("#hidden_id").val(id);
        $("#weight_name").val(name);
        $("#weight_status").val(status).change();
        $("#weightModalLabel").text('Update Weight');
        $(".btn.btn-primary").text('update');

        $("#WeightModal").modal('show');
    });

    $("#create-weight").submit(function (e) {
        e.preventDefault();

        let id = $("#hidden_id").val();
        let url = id ? "/weight/" + id : "/weight";

        let formData = $(this).serialize();
        if (id) {
            formData += "&_method=PUT";
        }

        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            dataType: 'json',
            beforsend: $(".btn.btn-primary").text('Please wait..'),
            success: function (response) {
                // console.log(response);
                if (response.message == "success") {
                    $("#weightModal").modal('hide');
                    // $(".btn.btn-primary").text("Save");
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function (xhr) {
                let response = JSON.parse(xhr.responseText);

                // console.log(response.message);

                console.log(response.errors.weight_name[0]);

                alert(response.message);

                if (id) {
                    $(".btn.btn-primary").text('Update');
                } else {
                    $(".btn.btn-primary").text('Save');
                }
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

        $("#DeleteWeightModal").modal('show');
    });

    $("#deleteWeightForm").on('submit', function (e) {
        e.preventDefault();
        let id = $('#hidden_id').val();

        $.ajax({
            method: 'DELETE',
            url: '/weight/' + id,
            dataType: 'JSON',
            success: function (response) {
                console.log(response);
                if (response.status == "success") {
                    table.row(row).remove().draw(false);
                    $("#datatable").focus();
                    $("#DeleteWeightModal").modal('hide');
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
