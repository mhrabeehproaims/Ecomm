$(document).ready(function () {

    let table = $('#datatable').DataTable();

    $('#create_unit').on('click', function () {
        $("#unitModalLabel").text('Create New Unit');
        $("#unit_name").val('');
        $("#hidden_id").val('');
        $("#unit_status").val(1);
        $(".btn.btn-primary").text('Save');
        $('#UnitModal').modal('show');
    });

    $.ajaxSetup({
        headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $(document).on('click', '.editBtn', function () {
        let id = $(this).data('id');
        let name = $(this).data('unitname');
        let status = $(this).data('status');

        $("#hidden_id").val(id);
        $("#unit_name").val(name);
        $("#unit_status").val(status).change();
        $("#unitModalLabel").text('Update Unit');
        $(".btn.btn-primary").text('update');

        $("#UnitModal").modal('show');
    });

    $("#create-unit").submit(function (e) {
        e.preventDefault();

        let id = $("#hidden_id").val();
        let url = id ? "/units/" + id : "/units";

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
                    $("#unitModal").modal('hide');
                    // $(".btn.btn-primary").text("Save");
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function (xhr) {
                let response = JSON.parse(xhr.responseText);

                // console.log(response.message);

                console.log(response.errors.unit_name[0]);

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

        $("#DeleteUnitModal").modal('show');
    });

    $("#deleteUnitForm").on('submit', function (e) {
        e.preventDefault();
        let id = $('#hidden_id').val();

        $.ajax({
            method: 'DELETE',
            url: '/units/' + id,
            dataType: 'JSON',
            success: function (response) {
                console.log(response.status);
                if (response.status == "success") {
                    table.row(row).remove().draw(false);
                    $("#datatable").focus();
                    $("#DeleteUnitModal").modal('hide');
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
