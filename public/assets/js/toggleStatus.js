$(document).on('change', '.switchStatus', function () {
    let id = $(this).data('id');
    let url = $(this).data('url');
    let $switch = $(this);
    $.ajax({
        method: 'GET',
        url: url,
        dataType: 'json',
        success: function (response) {
            if (response.message == "success") {
                if (response.status == 1) {
                    $switch.prop('checked', true);
                    $("#ecommerceshare-" + id).removeClass('d-none');
                    $("#ecommerceview-" + id).removeClass('d-none');
                } else {
                    $switch.prop('checked', false);
                    $("#ecommerceshare-" + id).addClass('d-none');
                    $("#ecommerceview-" + id).addClass('d-none');
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
