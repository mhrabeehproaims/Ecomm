$(document).ready(function () {

    let table = $('#datatable').DataTable();
    $("#state").val(state).trigger('change');


    $.ajaxSetup({
        headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

});

let row;

$(document).on('click', '.BtnDelete', function () {
    let id = $(this).data('id');
    let name = $(this).data('name');
    row = $(this).closest('tr');
    $("#hidden_id").val(id);
    $(".name").text(name);
    $("#DeleteCustomerModal").modal('show');
});

$("#deleteCustomerForm").on('submit', function (e) {
    e.preventDefault();
    let id = $("#hidden_id").val();
    let $btn = $(this).find('button[type=submit]');
    $btn.attr('disabled', true);

    $.ajax({
        method: 'DELETE',
        url: '/customers/' + id,
        dataType: 'JSON',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.status === "success") {
                $("#DeleteCustomerModal").modal('hide');
                window.location.reload();
                // table.row(row).remove().draw(false);
            } else {
                alert(response.status || 'Something went wrong');
            }
        },
        error: function (xhr) {
            let message = 'Something went wrong';
            try {
                let response = JSON.parse(xhr.responseText);
                message = response.message || response.status || message;
            } catch (e) { }
            alert(message);
        },
        complete: function () {
            $btn.attr('disabled', false);
        }
    });
});




