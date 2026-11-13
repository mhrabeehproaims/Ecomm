$(document).ready(function () {

    // get sub category start
    $('#category').on('change', function () {
        var categoryId = $(this).val();
        $('#subcategory').html('<option value="">Loading...</option>');
        loadSubcategory(categoryId);
    });

    // get sub category end

    // Other calcs start

    $('#special_price').on('input', function () {
        let specialPrice = parseFloat($(this).val()) || 0;
        let mrp = parseFloat($('#product_mrp').val()) || 0;

        // ✅ Update related fields
        $('#product_netPrice').val(specialPrice);
        $('#total_price').val(specialPrice);

        // ✅ Hide old Laravel validation message (if any)
        $("#sp-price-error").hide();

        // ✅ Remove any previous dynamic message
        $(this).next('.text-danger.price-error').remove();

        // ✅ Validation check
        if (specialPrice > mrp && mrp > 0) {
            $(this).after('<span class="text-danger price-error">Special price cannot be greater than MRP.</span>');
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // ✅ Also re-check when MRP changes
    $('#product_mrp').on('input', function () {
        $('#special_price').trigger('input');
    });



    function calculateNetPrice() {
        let price = parseFloat($("#product_mrp").val()) || 0;
        let discount = parseFloat($("#product_discount").val()) || 0;
        let discount_type = $("#discount_type").val();
        let net_price = 0.00;

        if (price > 0) {
            if (discount_type === "rupee") {
                if (discount <= price) {
                    net_price = price - discount;
                } else {
                    alert('Discount should not be greater than product price!');
                    net_price = price;
                    $(this).val('');
                }
            } else if (discount_type === "percentage") {
                if (discount >= 0 && discount <= 100) {
                    let percentage = (discount / 100) * price;
                    net_price = price - percentage;
                } else {
                    alert('Percentage discount must be between 0 and 100!');
                    net_price = price;
                    $(this).val('');
                }
            }

            $("#product_netPrice").val(net_price.toFixed(2));
            $("#total_price").val(net_price.toFixed(2));
        } else {
            alert('Enter Product Price First!');
            $(this).val('');
            $("#product_netPrice").val('');
            $("#total_price").val('');
        }
    };

    $("#product_discount").on('input', calculateNetPrice);

    $("#discount_type").on('change', calculateNetPrice);


    let unit_selected = $("#product_unit option:selected").data('name');
    if (unit_selected != "") {
        $("#showUnit").text(unit_selected);
    }

    let category = $("#category").val();
    if (category != "") {
        loadSubcategory(category);
    }

    $('#productForm').on('submit', function (e) {
        let tax_schedule = $("#tax_schedule").val();

        // If tax_schedule is empty
        if (tax_schedule == "") {
            e.preventDefault(); // stop immediate submit
            if (confirm('Do you want to save this product without tax?')) {
                this.submit(); // manually submit if confirmed
            } else {
                return false; // stop submission if cancelled
            }
        }

        // else (if tax_schedule not empty), let it submit normally
    });

});


function loadSubcategory(categoryId) {

    if (categoryId) {
        $.ajax({
            url: '/get-subcategories/' + categoryId,
            type: 'GET',
            success: function (data) {
                $('#subcategory').empty().append('<option value="">-- Select Subcategory --</option>');
                if (data.status == "subCategory") {
                    $.each(data.data, function (key, value) {
                        $('#subcategory').append('<option value="' + value.subCat_id + '"' + (value.subCat_id == oldSubcategory ? 'selected' : '') + '>' + value.subCat_name + '(Return: ' + value.return_days + ' day(s))</option>');
                    });
                }
            }
        });
    } else {
        $('#subcategory').html('<option value="">-- Select Subcategory --</option>');
    }
}
function calculateTotalPrice() {
    let selectedOption = $(this).find(':selected');
    let selectedValue = selectedOption.val();
    let product_netPrice = parseFloat($("#product_netPrice").val());

    if (selectedValue) {
        let cgst = selectedOption.data('cgst');
        let sgst = selectedOption.data('sgst');
        let igst = selectedOption.data('igst');

        if (product_netPrice > 0) {
            let cgst_amount = (parseFloat(cgst) / 100) * product_netPrice;
            let igst_amount = (parseFloat(igst) / 100) * product_netPrice;

            $("#product_cgst").val(cgst_amount.toFixed(2));
            $("#product_sgst").val(cgst_amount.toFixed(2)); //sgst is same as cgst amount
            $("#product_igst").val(igst_amount.toFixed(2));

            // Percent labels
            $("#igst_percent").text("(" + igst + "%)");
            $("#cgst_percent").text("(" + cgst + "%)");
            $("#sgst_percent").text("(" + sgst + "%)");

            // $("#total_price").val(parseFloat(product_netPrice + igst_amount).toFixed(2));
        }
    } else {

        $("#product_cgst").val("");
        $("#product_sgst").val(""); //sgst is same as cgst amount
        $("#product_igst").val("");

        // Percent labels
        $("#igst_percent").text("");
        $("#cgst_percent").text("");
        $("#sgst_percent").text("");
        // $("#total_price").val(product_netPrice)

    }
};
$("#tax_schedule").on('change', calculateTotalPrice);



$("#product_unit").on('change', function () {
    let name = $(this).find(':selected').data('name');
    if (name) {
        $("#showUnit").text(name);
    } else {
        $("#showUnit").text("");
    }
})

$("#primary_price").on('change', function () {
    let value = $(this).val();
    let primary = 0.00;

    switch (value) {
        case 'mrp':
            primary = parseFloat($("#product_mrp").val()) || 0.00;
            break;
        case 'retail':
            primary = parseFloat($("#retail_price").val()) || 0.00;
            break;
        case 'wholesale':
            primary = parseFloat($("#wholesale_price").val()) || 0.00;
            break;
        case 'special_price':
            primary = parseFloat($("#special_price").val()) || 0.00;
            break;
        default:
            primary = 0.00;
            break;
    }

    $("#product_netPrice").val(primary);
    $("#total_price").val(primary);
});

