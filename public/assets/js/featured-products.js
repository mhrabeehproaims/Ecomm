$(document).ready(function () {

    let table = $('#datatable').DataTable();


    $('#create_subCateg').on('click', function () {
        $("#subCategModalLabel").text('New Sub Category');
        $("#category").val('');
        $("#subCateg_name").val('');
        $("#subCateg_status").val(1);
        $("#return_days").val(0);
        $("#SubCateg_desc").val('');
        $(".btn.btn-primary").text('Save');
        $('#FeaturedModal').modal('show');
    });

    $.ajaxSetup({
        headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });


    $("#create-featured").submit(function (e) {
        e.preventDefault();

        let formData = $(this).serialize(); // collects selected_products[] etc.

        let $btn = $(this).find(".btn.btn-primary");
        $btn.text('Please wait...');

        $.ajax({
            method: 'POST',
            url: "featured-products", // for store
            data: formData,
            dataType: 'json',
            success: function (response) {
                console.log(response);

                if (response.message === "success") {
                    $("#FeaturedModal").modal('hide');

                    // Loop through returned featured products
                    $.each(response.data, function (index, item) {
                        let statusText = item.status == 1 ? 'Active' : 'Inactive';
                        let serial = $('#datatable tbody tr').length + 1; // auto increment #

                        // Build the table row
                        let row = `
                <tr>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input featured-items" data-id="featured-${item.featured_id}">
                        &nbsp; ${serial}
                    </td>
                    <td class="text-center">${item.product.category?.categ_name ?? ''}</td>
                    <td class="text-center">${item.product.sub_category?.subCat_name ?? ''}</td>
                    <td class="text-center">${item.product.product_name}</td>
                    <td class="text-center">${statusText}</td>
                </tr>
            `;

                        // Append row to table body
                        $("#datatable tbody").append(row);
                    });

                    // Re-initialize any select2 or plugins if needed
                    // $(".select2").select2();
                }
            },
            error: function (xhr) {
                let response = JSON.parse(xhr.responseText);
                alert(response.message);
                $btn.text('Save');
            }
        });
    });




    $('#product_name').on('change', function () {
        let productId = $(this).val();
        let productText = $("#product_name option:selected").text();

        if (productId) {
            // Check if already added
            if ($('#selected-products input[value="' + productId + '"]').length === 0) {
                // Create a tag/pill with hidden input
                let pill = `
                    <div class="badge bg-secondary p-2 me-2 d-flex align-items-center">
                        ${productText}
                        <button type="button" class="btn-close btn-close-white ms-2 remove-product" aria-label="Remove"></button>
                        <input type="hidden" name="selected_products[]" value="${productId}">
                    </div>
                `;
                $('#selected-products').append(pill);
            }

            // Reset dropdown
            $(this).val('').trigger('change.select2');
        }
    });

    // Remove product when "x" clicked
    $(document).on('click', '.remove-product', function () {
        $(this).closest('div.badge').remove();
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
                $('#product_name').empty().append('<option value="">-- Select Product --</option>');

                if (data.status == "subCategory") {
                    $.each(data.data, function (key, value) {
                        $('#subcategory').append('<option value="' + value.subCat_id + '"' + (value.subCat_id == oldSubcategory ? 'selected' : '') + '>' + value.subCat_name + '</option>');
                    });
                } else {
                    $.each(data.data, function (key, value) {
                        $('#product_name').append('<option value="' + value.product_id + '"' + (value.product_id == oldproduct_id ? 'selected' : '') + '>' + value.product_name + '</option>');
                    });
                }

            }
        });
    } else {
        $('#subcategory').html('<option value="">-- Select Subcategory --</option>');
    }
}

function loadProducts(subcategoryId) {

    if (subcategoryId) {
        $.ajax({
            url: '/get-productBy-subcategories/' + subcategoryId,
            type: 'GET',
            success: function (data) {
                $('#product_name').empty().append('<option value="">-- Select Product --</option>');
                $.each(data, function (key, value) {
                    $('#product_name').append('<option value="' + value.product_id + '"' + (value.product_id == oldproduct_id ? 'selected' : '') + '>' + value.product_name + '</option>');
                });
            }
        });
    }
}

let checkedIds = [];

$(".featured-items").on("change", function () {
    let id = $(this).data("id").toString(); // normalize to string

    if ($(this).is(":checked")) {
        if (!checkedIds.includes(id)) {
            checkedIds.push(id);
        }
    } else {
        checkedIds = checkedIds.filter(item => item !== id);
    }

    console.log("Checked IDs:", checkedIds);
});


$(document).on('click', '#remove_items', function () {
    if (checkedIds.length > 0) {
        $("#hidden_id").val(JSON.stringify(checkedIds)); // store as JSON array
        $("#DeleteFeaturedModal").modal('show');
    } else {
        alert('Select Product First');
    }
});

$("#delete-categ").on('submit', function (e) {
    e.preventDefault();

    let ids = JSON.parse($("#hidden_id").val());

    $.ajax({
        method: 'DELETE',
        url: '/featured-products/delete-multiple',
        data: { ids: ids },
        dataType: 'JSON',
        success: function (response) {
            if (response.status === "success") {
                // Remove deleted rows from datatable
                ids.forEach(id => {
                    $(".featured-items[data-id='" + id + "']")
                        .closest('tr')
                        .remove();
                });

                $("#DeleteFeaturedModal").modal('hide');
                checkedIds = []; // reset selection
            } else {
                alert(response.status);
            }
        },
        error: function (xhr) {
            let response = JSON.parse(xhr.responseText);
            alert(response.status || 'Error occurred');
        }
    });
});


