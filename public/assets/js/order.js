$(document).ready(function () {

    let table = $('#datatable').DataTable();
    $.ajaxSetup({
        headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
    let buttonText;
    let buttonText2;
    $(".courier-partner-section").hide();

    $(document).on('click', '.statusBtn', function (e) {
        e.preventDefault();
        let currentstatus = $(this).data('curstatus');
        let order_id = $(this).data('id');
        buttonText = $(this);
        $("#hidden_id").val(order_id);
        $("#statusSelect").val(currentstatus);
        //To make uppercase first letter
        currentstatus = currentstatus.toLowerCase().replace(/\b[a-z]/g, function (letter) {
            return letter.toUpperCase();
        });

        $('.info-value').text(currentstatus);
        $("#showStatusModal").modal('show');
    });
    let statusClasses = {
        'ordered': 'btn-sm btn-secondary',
        'rejected': 'btn-sm btn-danger',
        'confirmed': 'btn-sm btn-primary',
        'processing': 'btn-sm btn-info',
        'despatched': 'btn-sm btn-warning',
        'ontransist': 'btn-sm btn-dark',
        'delivered': 'btn-sm btn-success',
        'reissued': 'btn-sm btn-primary'
    };

    $("#changeStatusForm").on('submit', function (e) {
        e.preventDefault();
        let currentstatus = $("#statusSelect").val();
        let hidden_id = $("#hidden_id").val();
        let selectAgent = $('#selectAgent').val();
        let unique_tracking_id = $('#unique_tracking_id').val();
        let approx_delivery_date = $('#approx_delivery_date').val();
        let approx_delivery_charge = $('#approx_delivery_charge').val();

        $.ajax({
            method: 'PUT',
            url: '/update-order-status/' + hidden_id,
            dataType: 'json',
            data: {
                'status': currentstatus,
                'selectAgent': selectAgent,
                'unique_tracking_id': unique_tracking_id,
                'approx_delivery_date': approx_delivery_date,
                'approx_delivery_charge': approx_delivery_charge,
            },
            beforeSend: function () {
                $("#confirmBtn").text('Please Wait...').prop('disabled', true);
            },
            success: function (response) {
                if (response.status == "success") {

                    if (currentstatus == 'despatched') {
                        window.location.reload();
                    }

                    $('.info-value').text(response.status2);
                    buttonText.data('curstatus', response.status2);

                    // Format text nicely
                    let newStatus = response.status2.toLowerCase();
                    let displayText = newStatus.replace(/\b[a-z]/g, function (letter) {
                        return letter.toUpperCase();
                    });

                    buttonText.text(displayText);

                    // --- UPDATE BUTTON COLOR ---
                    let cssClass = statusClasses[newStatus] ?? 'btn-light';

                    // Remove previous Bootstrap btn-* classes
                    buttonText.removeClass(function (index, className) {
                        return (className.match(/(^|\s)btn-\S+/g) || []).join(' ');
                    });

                    // Add the new one
                    buttonText.addClass(cssClass);

                    // Disable if delivered
                    if (newStatus === 'delivered') {
                        buttonText.prop('disabled', true);
                    } else {
                        buttonText.prop('disabled', false);
                    }

                    $("#showStatusModal").modal('hide');

                } else {
                    alert('Error occurred during status update');
                }
            },
            error: function (xhr) {
                let response = JSON.parse(xhr.responseText);
                alert(response.status || 'Error occurred');
            }
        });
    });

    // $(".changeStatus").on('change', function (e) {

    //     e.preventDefault();

    //     let id = $(this).data('id');
    //         let status = $(this).find(':selected').val();

    //         $.ajax({
    //             method: 'PUT',
    //             url: '/enquiries/update-status/',
    //             dataType: 'json',
    //             data: {
    //                 'status': status,
    //                 'id': id,
    //             },
    //             success: function (response) {
    //                 if (response.status == "success") {
    //                     alert('Updated');
    //                 } else {
    //                     alert('Error occured during status update');
    //                 }
    //             },
    //             error: function (xhr) {
    //                 let response = JSON.parse(xhr.responseText);
    //                 alert(response.status || 'Error occurred');
    //             }
    //         });
    //     });

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

$("#statusSelect").on('click', function () {
    if ($(this).val() == "despatched") {
        // $(".courier-partner-section").show();
        $(".courier-partner-section").css("display", "block");

        $("#unique_tracking_id").attr('required', true);
        $("#approx_delivery_date").attr('required', true);
        $("#selectAgent").attr('required', true);

    } else {
        $("#unique_tracking_id, #approx_delivery_date, #selectAgent").removeAttr('required');

        // Reset values
        $("#unique_tracking_id, #approx_delivery_date").val('');
        $("#selectAgent").val('').trigger('change'); // ✅ Important for Select2

        // Hide section
        $(".courier-partner-section").css("display", "none");
    }
});

$('#showStatusModal').on('shown.bs.modal', function () {
    $('#selectAgent').select2({
        dropdownParent: $('#showStatusModal'),
        width: '100%',
        placeholder: 'Select Delivery Agent',
        allowClear: false
    });
});

// $(document).ready(function () {
//     // Define order of progress
//     const statusOrder = [
//         "ordered",
//         "rejected",
//         "confirmed",
//         "processing",
//         "despatched",
//         "ontransist",
//         "delivered",
//     ];

//     // Whenever status changes
//     $('#statusSelect').on('change', function () {
//         const currentStatus = $(this).val();

//         // Find index of current status
//         const currentIndex = statusOrder.indexOf(currentStatus);

//         // Loop through all options
//         $('#statusSelect option').each(function (index) {
//             // Disable all statuses before current
//             if (index < currentIndex) {
//                 $(this).prop('disabled', true);
//             } else {
//                 $(this).prop('disabled', false);
//             }
//         });
//     });
// });



