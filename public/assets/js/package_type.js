$(document).ready(function () {

    let table = $('#datatable').DataTable();

    $('#create_package').on('click', function () {
        $("#packageModalLabel").text('Create New package');
        $("#package_name").val('');
        $("#hidden_id").val('');
        $("#package_status").val(1);
        $(".btn.btn-primary").text('Save');
        $('#packageModal').modal('show');
    });

    $.ajaxSetup({
        headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $(document).on('click', '.editBtn', function () {
        let id = $(this).data('id');
        let name = $(this).data('packagename');
        let status = $(this).data('status');

        $("#hidden_id").val(id);
        $("#package_name").val(name);
        $("#package_status").val(status).change();
        $("#packageModalLabel").text('Update package');
        $(".btn.btn-primary").text('update');

        $("#packageModal").modal('show');
    });

    $("#create-package_type").submit(function (e) {
        e.preventDefault();

        let id = $("#hidden_id").val();
        let url = id ? "/packages/" + id : "/packages";

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
                    $("#packageModal").modal('hide');
                    // $(".btn.btn-primary").text("Save");
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function (xhr) {
                let response = JSON.parse(xhr.responseText);

                // console.log(response.message);

                console.log(response.errors.package_name[0]);

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

        $("#DeletepackageModal").modal('show');
    });

    $("#deletepackageForm").on('submit', function (e) {
        e.preventDefault();
        let id = $('#hidden_id').val();

        $.ajax({
            method: 'DELETE',
            url: '/packages/' + id,
            dataType: 'JSON',
            success: function (response) {
                if (response.status == "success") {
                    table.row(row).remove().draw(false);
                    $("#datatable").focus();
                    $("#DeletepackageModal").modal('hide');
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
