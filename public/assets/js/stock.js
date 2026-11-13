let currentStock = 0;
let unit = '';
let NewTotal_stock = 0;
let new_stock = 0;

$(document).ready(function () {
    $('.addStock').on('click', function () {
        let id = $(this).data('id');
        let name = $(this).data('name');
        $('.new_stock').val(0);
        new_stock = $('.new_stock').val() || 0;
        currentStock = parseFloat($(this).data('stock')) || 0;
        unit = $(this).data('unit');

        $(".prod_name").text(name);
        $(".current_stock").val(currentStock);
        $("#hidden_id").val(id);
        $(".new_stock").val(new_stock);
        $(".stock_unit").text(unit);
        $(".hidden_unit").val(unit);
        $(".total_stock").text(currentStock + ' ' + unit);

        $("#updateStockModal").modal('show');
    });

    $(".current_stock").on('input', function () {
        currentStock = parseFloat($(this).val()) || 0;
        NewTotal_stock = parseFloat(new_stock) + currentStock;
        $(".total_stock").text(NewTotal_stock + ' ' + unit);
    });

    $(".new_stock").on('input', function () {
        new_stock = parseFloat($(this).val()) || 0;
        NewTotal_stock = parseFloat(new_stock + currentStock);
        $(".total_stock").text(NewTotal_stock + ' ' + unit);
    });

    $("#update-product-stock").submit(function (e) {
        e.preventDefault();
        let id = $("#hidden_id").val();
        let formData = new FormData(this);
        let unit = $(".unit").val();

        $.ajax({
            method: 'POST',
            url: "/stocks",
            data: formData,
            headers:
                { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            processData: false,
            contentType: false,
            beforsend: $(".btn.btn-primary").text('Please wait..'),
            dataType: 'json',
            success: function (response) {
                // console.log(response);
                if (response.status == "success") {
                    $("#updateStockModal").modal('hide');
                    // $(".showQuoantity-" + id).text(response.cur_stock + " " + response.unit);
                    location.reload();
                } else {
                    alert(response.message);
                    $(".btn.btn-primary").text('Update');
                }
            },
            error: function (xhr) {
                let response = JSON.parse(xhr.responseText);

                // console.log(response.message);

                console.log(response.errors.brand_name[0]);

                alert(response.message);

                if (id) {
                    $(".btn.btn-primary").text('Update');
                } else {
                    $(".btn.btn-primary").text('Save');
                }
            }
        });
    });


    // --------------------------------------------------------------------

    $('#category').on('change', function () {
        var categoryId = $(this).val();
        $('#subcategory').html('<option value="">Loading...</option>');
        loadSubcategory(categoryId);
    });

    $('#subcategory').on('change', function () {
        var subcategoryId = $(this).val();
        $('#product_name').html('<option value="">Loading...</option>');
        loadProducts(subcategoryId);
    });

    function loadSubcategory(categoryId) {
        if (categoryId) {
            $.ajax({
                url: '/get-subcategories/' + categoryId,
                type: 'GET',
                success: function (data) {
                    $('#subcategory').empty().append('<option value="">-- Select Subcategory --</option>');
                    if (data.data && data.data.length > 0) {
                        $.each(data.data, function (key, value) {
                            $('#subcategory').append(
                                `<option value="${value.subCat_id}" ${value.subCat_id == oldSubcategory ? 'selected' : ''}>
                ${value.subCat_name}
             </option>`
                            );
                        });
                    } else {
                        $('#subcategory').append('<option value="">No subcategories available</option>');
                    }

                }
            });
        } else {
            $('#subcategory').html('<option value="">-- Select Subcategory --</option>');
        }
    }

    function loadProducts(ProductId) {

        if (ProductId) {
            $.ajax({
                url: '/get-productBy-subcategories/' + ProductId,
                type: 'GET',
                success: function (data) {
                    $('#product_name').empty().append('<option value="">-- Select Product --</option>');
                    $.each(data, function (key, value) {
                        $('#product_name').append('<option value="' + value.product_id + '"' + (value.product_id == oldproduct_id ? 'selected' : '') + '>' + value.product_name + '</option>');
                    });
                }
            });
        } else {
            $('#subcategory').html('<option value="">-- Select Product --</option>');
        }
    }
});
