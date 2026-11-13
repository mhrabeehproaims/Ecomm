$(document).ready(function () {

    let table = $('#datatable').DataTable();

    $('#create_brand').on('click', function () {
        $("#brandModalLabel").text('New Brand');
        $("#brand_name").val('');
        $("#hidden_id").val('');
        $("#brand_status").val(1);
        $(".btn.btn-primary").text('Save');
        $('#brandModal').modal('show');
    });

    $.ajaxSetup({
        headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $(document).on('click', '.editBtn', function () {
        let id = $(this).data('id');
        let name = $(this).data('cat_name');
        let status = $(this).data('status');

        $("#hidden_id").val(id);
        $("#brand_name").val(name);
        $("#brand_status").val(status).change();
        $("#brandModalLabel").text('Update Brand');
        $(".btn.btn-primary").text('update');

        $("#brandModal").modal('show');
    });

    $("#create-brand").submit(function (e) {
        e.preventDefault();

        let id = $("#hidden_id").val();
        let url = id ? "/brands/" + id : "/brands";

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
            beforsend: $(".btn.btn-primary").text('Please wait..'),
            dataType: 'json',
            success: function (response) {
                // console.log(response);
                if (response.message == "success") {
                    $("#brandModal").modal('hide');
                    // $(".btn.btn-primary").text("Save");
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function (xhr) {
                let response = JSON.parse(xhr.responseText);

                // console.log(response.message);

                console.log(response.errors.brand_name[0]);

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

        $("#DeleteBrandModal").modal('show');
    });

    $("#delete-brand").on('submit', function (e) {
        e.preventDefault();
        let id = $('#hidden_id').val();

        $.ajax({
            method: 'DELETE',
            url: '/brands/' + id,
            dataType: 'JSON',
            success: function (response) {
                console.log(response.status);
                if (response.status == "success") {
                    table.row(row).remove().draw(false);
                    $("#datatable").focus();
                    $("#DeleteBrandModal").modal('hide');
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
