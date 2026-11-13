$(document).ready(function () {

    let table = $('#datatable').DataTable();

    $('#create_size').on('click', function () {
        $("#SizeModalLabel").text('Create New Size');
        $("#size_name").val('');
        $("#hidden_id").val('');
        $("#size_status").val(1);
        $(".btn.btn-primary").text('Save');
        $('#SizeModal').modal('show');
    });

    $.ajaxSetup({
        headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $(document).on('click', '.editBtn', function () {
        let id = $(this).data('id');
        let name = $(this).data('sizename');
        let status = $(this).data('status');

        $("#hidden_id").val(id);
        $("#size_name").val(name);
        $("#size_status").val(status).change();
        $("#SizeModalLabel").text('Update Size');
        $(".btn.btn-primary").text('update');

        $("#SizeModal").modal('show');
    });

    $("#create-size").submit(function (e) {
        e.preventDefault();

        let id = $("#hidden_id").val();
        let url = id ? "/sizes/" + id : "/sizes";

        // let formData = new FormData(this);
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
                    $("#SizeModal").modal('hide');
                    // $(".btn.btn-primary").text("Save");
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function (xhr) {
                let response = JSON.parse(xhr.responseText);

                // console.log(response.message);

                console.log(response.errors.size_name[0]);

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

        $("#DeleteSizeModal").modal('show');
    });

    $("#deleteSizeForm").on('submit', function (e) {
        e.preventDefault();
        let id = $('#hidden_id').val();

        $.ajax({
            method: 'DELETE',
            url: '/sizes/' + id,
            dataType: 'JSON',
            success: function (response) {
                console.log(response.status);
                if (response.status == "success") {
                    table.row(row).remove().draw(false);
                    $("#datatable").focus();
                    $("#DeleteSizeModal").modal('hide');
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
