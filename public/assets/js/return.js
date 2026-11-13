$(document).ready(function () {

    let table = $('#datatable').DataTable();
    $.ajaxSetup({
        headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
    let buttonText;
    let buttonText2;

    $(".statusBtn").on('click', function (e) {
        e.preventDefault();
        let currentstatus = $(this).data('curstatus');
        let return_type = $(this).data('return_type');
        let order_id = $(this).data('id');
        buttonText = $(this);
        $("#hidden_id").val(order_id);
        $("#statusSelect").val(currentstatus);

        //To make uppercase first letter
        currentstatus = currentstatus.toLowerCase().replace(/\b[a-z]/g, function (letter) {
            return letter.toUpperCase();
        });

        $('.info-value').text(currentstatus);
        $("#statusSelect option[value='refunded']").removeClass('d-none');
        $("#statusSelect option[value='replaced']").removeClass('d-none');

        if (return_type === 'replacement') {
            // hide "Refunded" if type is replacement
            $("#statusSelect option[value='refunded']").addClass('d-none');
        } else if (return_type === 'refund') {
            // hide "Replaced" if type is refund
            $("#statusSelect option[value='replaced']").addClass('d-none');
        }
        $("#showStatusModal").modal('show');
    });

    $("#changeStatusForm").on('submit', function (e) {
        e.preventDefault();
        let currentstatus = $("#statusSelect").val();
        let hidden_id = $("#hidden_id").val();

        $.ajax({
            method: 'PUT',
            url: '/update-return-status/' + hidden_id,
            dataType: 'json',
            data: {
                'status': currentstatus,
            },
            success: function (response) {
                if (response.status == "success") {
                    // $('.info-value').text(response.status2);
                    buttonText.data('curstatus', response.status2);
                    // $('.info-value').data('curstatus', response.status2);

                    buttonText2 = response.status2.toLowerCase().replace(/\b[a-z]/g, function (letter) {
                        return letter.toUpperCase();
                    });
                    buttonText.text(buttonText2);
                    $(".curStatus").text(buttonText2);
                    $("#showStatusModal").modal('hide');
                } else {
                    alert('Error occured during status update');
                }
            },
            error: function (xhr) {
                let response = JSON.parse(xhr.responseText);
                alert(response.status || 'Error occurred');
            }
        });
    });

    $(".changeStatus").on('change', function (e) {

        e.preventDefault();

        let id = $(this).data('id');
        let status = $(this).find(':selected').val();

        $.ajax({
            method: 'PUT',
            url: '/enquiries/update-status/',
            dataType: 'json',
            data: {
                'status': status,
                'id': id,
            },
            success: function (response) {
                if (response.status == "success") {
                    alert('Updated');
                } else {
                    alert('Error occured during status update');
                }
            },
            error: function (xhr) {
                let response = JSON.parse(xhr.responseText);
                alert(response.status || 'Error occurred');
            }
        });
    });

});

$(document).on('click', '.BtnDelete', function () {
    $("#hidden_id").val($(this).data('id'));
    $("#DeleteEnquiryModal").modal('show');
});

$("#delete-enquiry").on('submit', function (e) {
    e.preventDefault();

    let id = $("#hidden_id").val();

    $.ajax({
        method: 'DELETE',
        url: '/enquiries/delete/' + id,
        dataType: 'JSON',
        success: function (response) {
            if (response.status == "success") {
                $(".BtnDelete[data-id='" + id + "']")
                    .closest('tr')
                    .remove();

                $("#DeleteEnquiryModal").modal('hide');
            } else {
                alert(response);
            }
        },
        error: function (xhr) {
            let response = JSON.parse(xhr.responseText);
            alert(response.status || 'Error occurred');
        }
    });
});

$(document).ready(function () {
    // Define order of progress
    const statusOrder = [
        "requested",
        "accepted",
        "picked_up",
        "collected",
        "refunded",
        "replaced",
    ];

    // Whenever status changes
    $('#statusSelect').on('change', function () {
        const currentStatus = $(this).val();

        // Find index of current status
        const currentIndex = statusOrder.indexOf(currentStatus);

        // Loop through all options
        $('#statusSelect option').each(function (index) {
            // Disable all statuses before current
            if (index < currentIndex) {
                $(this).prop('disabled', true);
            } else {
                $(this).prop('disabled', false);
            }
        });
    });
});


