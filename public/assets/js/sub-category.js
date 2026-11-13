$(document).ready(function () {

    let table = $('#datatable').DataTable();


    $('#create_subCateg').on('click', function () {
        $("#subCategModalLabel").text('New Sub Category');
        $("#category").val('');
        $("#subCateg_name").val('');
        $("#subCateg_status").val(1);
        $("#return_days").val(0);
        $("#SubCateg_desc").val('');
        $(".btn.btn-primary").text('Save');
        $('#subCategoryModal').modal('show');
    });

    $.ajaxSetup({
        headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $(document).on('click', '.editBtn', function () {
        let id = $(this).data('id');
        let name = $(this).data('catname');
        let SubCat = $(this).data('subcat');
        let status = $(this).data('status');
        let desc = $(this).data('desc');
        let return_day = $(this).data('return_day');

        $("#hidden_id").val(id);
        $("#category").val(name).trigger('change');
        $("#subCateg_name").val(SubCat);
        $("#subCateg_status").val(status).change();
        $("#return_days").val(return_day);
        $("#SubCateg_desc").val(desc);
        $("#subCategModalLabel").text('Update Subcategory');
        $(".btn.btn-primary").text('update');

        $("#subCategoryModal").modal('show');
    });


    $("#create-subCateg").submit(function (e) {
        e.preventDefault();

        let id = $("#hidden_id").val();
        let url = id ? "/sub-categories/" + id : "/sub-categories";

        let formData = new FormData(this);
        if (id) {
            formData.append("_method", "PUT");
        }

        $.ajax({
            method: 'POST',
            url: url,
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function () {
                $(".btn.btn-primary").text('Please wait..');
            },
            success: function (response) {
                console.log(response);
                if (response.message == "success") {
                    $("#subCategoryModal").modal('hide');
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
            url: '/sub-categories/' + id,
            dataType: 'JSON',
            success: function (response) {
                // console.log(response.status);
                if (response.status == "success") {
                    table.row(row).remove().draw(false);
                    $("#datatable").focus();
                    $("#DeleteCategoryModal").modal('hide');
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
