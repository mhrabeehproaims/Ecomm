$(document).ready(function () {

    let table = $('#datatable').DataTable();

    $('#create_categ').on('click', function () {
        $("#categModalLabel").text('New Category');
        $("#hidden_id").val('');
        $("#categ_name").val('');
        $("#old_image").val('');
        $("#return_days").val(0);
        $("#categ_desc").text('');
        $("#categ_status").val(1);
        $(".btn.btn-primary").text('Save');
        $('#categoryModal').modal('show');
    });

    $.ajaxSetup({
        headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $(document).on('click', '.editBtn', function () {
        let id = $(this).data('id');
        let name = $(this).data('cat_name');
        let desc = $(this).data('desc');
        let return_day = $(this).data('return_day');
        let status = $(this).data('status');
        let image = $(this).data('image');

        $("#hidden_id").val(id);
        $("#categ_name").val(name);
        $("#categ_desc").val(desc);
        $("#return_days").val(return_day);
        $("#categ_status").val(status).change();
        $("#old_image").val(image);
        $("#categModalLabel").text('Update Category');
        $(".btn.btn-primary").text('update');

        $("#categoryModal").modal('show');
    });

    $("#create-categ").submit(function (e) {
        e.preventDefault();

        let id = $("#hidden_id").val();
        let url = id ? "/categories/" + id : "/categories";
        // let method = id ? "PUT" : "POST";

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
                $(".btn.btn-primary").text('Please wait..');
            },
            success: function (response) {
                // console.log(response);
                if (response.message == "success") {
                    $("#categoryModal").modal('hide');
                    // $("#categoryForm")[0].reset();
                    // $(".btn.btn-primary").text("Save");
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function (xhr) {
                let response = JSON.parse(xhr.responseText);

                // console.log(response.message);

                console.log(response.errors.categ_name[0]);

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
        $("#hidden_id").val(id);
        $(".name").text(name);
        $("#DeleteCategoryModal").modal('show');
    });

    $("#delete-categ").on('submit', function (e) {
        e.preventDefault();
        let id = $("#hidden_id").val();

        $.ajax({
            method: 'DELETE',
            url: '/categories/' + id,
            dataType: 'JSON',
            success: function (response) {
                console.log(response.status);
                if (response.status == "success") {
                    table.row(row).remove().draw(false);
                    $("#datatable").focus();
                    $("#DeleteCategoryModal").modal('hide');
                } else {
                    alert(response.message);
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
