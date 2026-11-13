$(document).ready(function () {
    $('#datatable').DataTable().destroy();

    $.ajaxSetup({
        headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

});

$("#create_delivery_partner").on('click', function () {
    $("#hidden_id").val(0);
    // $("partner_name").val('');
    // $("contact_person").val('');
    // $("contact_email").val('');
    // $("contact_phone").val('');
    // $("address_line").val('');
    // $("city").val('');
    // $("state").val('');
    // $("country").val('');
    // $("pincode").val('');
    // $("tracking_url").val('');
    // $("per_km_rate").val('');
    // $("base_charge").val('');
    // $("parnter_status").val('');
    // $("website").val('');
    $('#createPartnerForm')[0].reset();

    $("#partnerModalLabel").text('Create Partner');
    $(".btn.btn-primary").text('Create');
    $("#createPartnerModal").modal('show');
});

let row;

$(document).on('click', '.BtnDelete', function () {
    let id = $(this).data('id');
    let name = $(this).data('name');
    row = $(this).closest('tr');
    $("#hidden_id").val(id);
    $(".name").text(name);
    $("#DeleteDeliveryPartnerModal").modal('show');
});

$("#createPartnerForm").on('submit', function (e) {
    e.preventDefault();
    var data = $(this).serialize();
    var id = $("#hidden_id").val();
    let method;
    let url;
    if (id == null || id == 0) {
        method = 'POST';
        url = '/delivery_partners';
    } else {
        method = 'PUT';
        url = '/delivery_partners' + id;
    }
    $.ajax({
        method: method,
        url: url,
        dataType: 'JSON',
        data: data,
        success: function (response) {
            if (response.status === "success") {
                $("#createPartnerModal").modal('hide');
                window.location.reload();
            } else {
                alert(response.error || 'Something went wrong');
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
    });
});

$(document).on('click', '.editBtn', function () {
    let id = $(this).data('id');
    let partner_name = $(this).data('partner_name');
    let contact_person = $(this).data('contact_person');
    let contact_email = $(this).data('contact_email');
    let contact_phone = $(this).data('contact_phone');
    let address_line = $(this).data('address_line');
    let city = $(this).data('city');
    let state = $(this).data('state');
    let country = $(this).data('country');
    let pincode = $(this).data('pincode');
    let tracking_url = $(this).data('tracking_url');
    let per_km_rate = $(this).data('per_km_rate');
    let base_charge = $(this).data('base_charge');
    let parnter_status = $(this).data('parnter_status');
    let website = $(this).data('website');

    $("#hidden_id").val(id);
    $("#partner_name").val(partner_name);
    $("#contact_person").val(contact_person);
    $("#contact_email").val(contact_email);
    $("#contact_phone").val(contact_phone);
    $("#address_line").val(address_line);
    $("#city").val(city);
    $("#state").val(state);
    $("#country").val(country);
    $("#pincode").val(pincode);
    $("#tracking_url").val(tracking_url);
    $("#per_km_rate").val(per_km_rate);
    $("#base_charge").val(base_charge);
    $("#parnter_status").val(parnter_status).change();
    $("#website").val(website);
    $("#partnerModalLabel").text('Update Partner');
    $(".btn.btn-primary").text('Update');

    $("#createPartnerModal").modal('show');
});

$("#deleteDeliveryPartnerForm").on('submit', function (e) {
    e.preventDefault();
    let id = $("#hidden_id").val();
    let $btn = $(this).find('button[type=submit]');
    $btn.attr('disabled', true);

    $.ajax({
        method: 'DELETE',
        url: '/delivery_partners/' + id,
        dataType: 'JSON',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.status === "success") {
                $("#DeleteDeliveryPartnerModal").modal('hide');
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

$(document).on('change', '.switchStatus', function () {
    let id = $(this).data('id');
    let $switch = $(this);
    let status = $switch.is(":checked")? 'active' : 'inactive'; 
    $.ajax({
        method: 'PATCH',
        url: '/delivery_partners/switchStatus/' + id,
        dataType: 'json',
        data: { 'partner_status': status },
        success: function (response) {
            if (response.status == "success") {
                if (response.message == 'active') {
                    $switch.prop('checked', true);
                } else {
                    $switch.prop('checked', false);
                }
            }
        },
        error: function () {
            alert("Server error, try again.");
            // revert switch back
            $switch.prop('checked', !$switch.prop('checked'));
        }
    });
});

$('#datatable').DataTable({
    autoWidth: false,
    scrollX: true,
    columnDefs: [
        { width: "5%", targets: 0 },   // #
        { width: "15%", targets: 1 },  // Name
        { width: "10%", targets: 2 },  // Phone
        { width: "10%", targets: 3 },  // Contact Person
        { width: "8%", targets: 4 },   // City
        { width: "8%", targets: 5 },   // State
        { width: "6%", targets: 6 },   // Pin
        { width: "15%", targets: 7 },  // Email
        { width: "18%", targets: 8 },  // Address
        { width: "7%", targets: 9 },   // Status
        { width: "10%", targets: 10 }, // Action
    ],
});

// $(document).ready(function () {
//     $('#datatable').DataTable({
//         searching: false,   // 🚫 disables search box
//         lengthChange: false, // 🚫 removes "Show X entries" dropdown
//         // pageLength: 10,     // optional: default number of rows per page
//     });
// });





