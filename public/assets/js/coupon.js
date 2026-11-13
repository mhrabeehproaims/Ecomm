$(document).ready(function () {

    let table = $('#datatable').DataTable();

    $('#create_coupon').on('click', function () {
        $("#CouponModalLabel").text('Create New Coupon');
        $("#coupon_name").val('');
        $("#hidden_id").val('');
        $("#coupon_status").val(1);
        $(".btn.btn-primary").text('Save');
        $('#CouponModal').modal('show');
    });

    $.ajaxSetup({
        headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $(document).on('click', '.editBtn', function () {
        let id = $(this).data('id');
        let code = $(this).data('code');
        let coupon_name = $(this).data('coupon_name');
        let type = $(this).data('type');
        let value = $(this).data('value');
        let min_order_amount = $(this).data('min_order_amount');
        let coupon_status = $(this).data('coupon_status');
        let max_uses = $(this).data('max_uses');
        let max_uses_per_user = $(this).data('max_uses_per_user');
        let start_date = $(this).data('start_date');
        let end_date = $(this).data('end_date');

        $("#hidden_id").val(id);
        $("#coupon_code").val(code);
        $("#coupon_name").val(coupon_name);
        $("#type").val(type).change();
        $("#value").val(value);
        $("#min_order_amount").val(min_order_amount);
        $("#coupon_status").val(coupon_status).change();
        $("#max_uses").val(max_uses);
        $("#max_uses_per_user").val(max_uses_per_user);
        $("#start_date").val(start_date);
        $("#end_date").val(end_date);

        $("#CouponModalLabel").text('Update Coupon');
        $(".btn.btn-primary").text('update');

        $("#CouponModal").modal('show');
    });

    $("#create-coupon").submit(function (e) {
        e.preventDefault();

        let id = $("#hidden_id").val();
        let url = id ? "/coupons/" + id : "/coupons";

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
                    $("#CouponModal").modal('hide');
                    $(".btn.btn-primary").text("Save Coupon");
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    // ✅ Validation error from Laravel
                    let errors = xhr.responseJSON.errors;

                    // Loop and show all errors (clean & dynamic)
                    let messages = '';
                    $.each(errors, function (key, value) {
                        messages += value[0] + '\n';
                    });

                    alert(messages);
                    $(".btn.btn-primary").text("Save Coupon");

                } else {
                    // Other errors
                    alert('Something went wrong! Please try again later.');
                    $(".btn.btn-primary").text("Save Coupon");

                }
            }
        });
    });

    let row;

    $(document).on('click', '.BtnDelete', function () {
        let id = $(this).data('id');
        let code = $(this).data('code');
        row = $(this).closest('tr');
        $(".name").text(code);
        $("#hidden_id").val(id);
        $(".btn.btn-primary").text('Confirm');

        $("#DeleteCouponModal").modal('show');
    });

    $("#deleteCouponForm").on('submit', function (e) {
        e.preventDefault();
        let id = $('#hidden_id').val();

        $.ajax({
            method: 'DELETE',
            url: '/coupons/' + id,
            dataType: 'JSON',
            success: function (response) {
                // console.log(response.status);
                if (response.status == "success") {
                    table.row(row).remove().draw(false);
                    $("#datatable").focus();
                    $("#DeleteCouponModal").modal('hide');
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
