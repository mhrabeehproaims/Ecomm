$(document).ready(function () {
    document.getElementById('discount_type').addEventListener('change', function () {
        var symbol = this.value === 'percentage' ? '%' : '₹';
        document.querySelectorAll('.input-group-text, .input-gtext')
            .forEach(el => el.textContent = symbol);
    });

    $('.select2').select2({
        // placeholder: "Select options",
        allowClear: true
    });

    $.ajaxSetup({
        headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });
});

// Remove pill when clicking the close button
$(document).on('click', '.remove-product', function () {
    $(this).closest('.badge').remove();
});

// ------------------------------------ //-------------------------------------//----------------------

// Update checkboxes when selection changes
$('#category').on('select2:select select2:unselect', function () {
    let selectedCategories = $(this).val(); // array

    // Reset main subcategory dropdown before reloading
    $('#subcategory').removeData('loaded');
    $('#subcategory').empty().append('<option value="">Loading...</option>');

    if (selectedCategories && selectedCategories.length > 0) {
        selectedCategories.forEach(function (catId) {
            loadAllSubcategories();
        });
    } else {
        $('#subcategory').html('<option value="">--Select Subcategory--</option>');
    }
});

$('#category').on('change', function () {

    let selectedCategories = $(this).val() || [];

    if (selectedCategories.length === 0) {
        $('#subcategory').html('<option value="">--Select Subcategory--</option>');
        $('#subcategory').trigger('change');
        return;
    }

    $('#subcategory').html('<option>Loadingddd...</option>').trigger('change');

    let loadedSubcategories = [];
    let completedRequests = 0;

    selectedCategories.forEach(function (categoryId) {

        $.ajax({
            url: '/get-subcategories/' + categoryId,
            type: 'GET',

            success: function (data) {


                if (data.status === "subCategory" && data.data.length > 0) {

                    data.data.forEach(function (subcategory) {

                        // Use CORRECT FIELD NAMES
                        let subId = subcategory.subCat_id;
                        let subName = subcategory.subCat_name;

                        // Avoid duplicates
                        if (!loadedSubcategories.some(s => s.id === subId)) {

                            loadedSubcategories.push({
                                id: subId,
                                name: subName
                            });
                        }
                    });
                }
            },

            complete: function () {

                completedRequests++;

                if (completedRequests == selectedCategories.length) {
                    populateSubcategoryDropdown(loadedSubcategories);
                }
            }

        });

    });

});

// Populate subcategory dropdown with loaded data
function populateSubcategoryDropdown(subcategories) {
    $('#subcategory').empty();
    $('#subcategory').append('<option value="">--Select Subcategory--</option>');

    subcategories.forEach(function (sub) {
        $('#subcategory').append(
            `<option value="${sub.id}">${sub.name}</option>`
        );
    });

    $('#subcategory').trigger('change');
}

function disocunted_price(discount_type, discount_value, product_price) {

    product_price = parseFloat(product_price) || 0;
    discount_value = parseFloat(discount_value) || 0;

    let finalPrice = 0;
    let discountAmount = 0;

    if (discount_type === "percentage") {
        // percentage discount
        discountAmount = (product_price * discount_value) / 100;
        finalPrice = product_price - discountAmount;
    } else {
        // flat discount
        discountAmount = discount_value;
        finalPrice = product_price - discountAmount;
    }

    return {
        final_price: finalPrice.toFixed(2),
        discount_amount: discountAmount.toFixed(2)
    };
}

$(document).on('click', '.remove-row', function () {
    $(this).closest('tr').remove();
});
// ------------------------------------------
$(document).ready(function () {
    // Initialize Select2 for subcategory
    $('#subcategory').select2({
        closeOnSelect: false,
        templateResult: function (data) {
            // Just return text — no checkbox
            return data.text;
        },
        templateSelection: function (data) {
            return data.text;
        }
    });

    // Get Products button click
    $('#getProductsBtn').on('click', function () {
        let selectedCategories = $('#category').val() || [];
        let selectedSubcategories = $('#subcategory').val() || [];
        let selectedBrands = $('#brand_name').val() || [];
        var discount_type = $("#discount_type").val();
        var discount_value = $("#discount_value").val();
        var discount_from = $("#discount_from").val();
        $('#disc_type').html(discount_type == 'percentage' ? '(%) ' : '(₹) ');
        $('#price_type').text(discount_from == 'srp' ? 'SRP' : 'MRP');
        $('#product_name').html('<option value="">Loading...</option>');

        if (selectedCategories.length === 0) {
            alert('Please select at least one category');
            return;
        }

        $('#products-result').show();
        $('#products-result').html('<div class="alert alert-info">Loading products...</div>');

        $.ajax({
            url: '/get-offer-products',
            type: 'GET',
            data: {
                categories: selectedCategories,
                subcategories: selectedSubcategories,
                brands: selectedBrands
            },
            success: function (response) {
                // displayProducts(response.products);
                displayProducts(response.products, discount_type, discount_from, discount_value);

            },
            error: function () {
                $('#products-result').show();
                $('#products-result').html('<div class="alert alert-danger">Error loading products</div>');
            }
        });
    });

    function displayProducts(products, discount_type, discount_from, discount_value) {
        $("#product_table tbody").empty();

        if (!products || products.length == 0) {
            $('#products-result').show();
            $('#products-result').html('<div class="alert alert-warning">No products found</div>');
            return;
        }
        $('#products-result').hide();

        $.each(products, function (index, product) {

            // Determine base price (mrp or sp)
            let product_value = discount_from === 'mrp' ? (product.mrp ?? 0) : (product.special_price ?? 0);
            // product_value = discount_from === 'mrp' ? (value.mrp ?? 0) : (value.special_price ?? 0);
            let discountedValue = disocunted_price(discount_type, discount_value, product_value);

            let row = `
            <tr>
                 <td>${index + 1}</td>

                <td>${product.product_name}</td>
                <td>${product.mrp ?? ''}</td>
                <td>${product.special_price ?? ''}</td>
                <td hidden>
                    <input type="hidden" name="product_id[]" value="${product.product_id ?? ''}">
                </td>
                <td hidden>
                    <input type="hidden" name="product_mrp[]" value="${product.mrp ?? ''}">
                </td>
                <td hidden>
                    <input type="hidden" name="product_srate[]" value="${product.special_price ?? ''}">
                </td>
                <td>
                    <input 
                        type="number" 
                        name="discount_value[]" 
                        class="form-control w-75 discount-value-input" 
                        value="${discount_value ?? ''}" 
                        step="0.50"
                    />
                </td>

                <td>
                    <input 
                        type="number" 
                        name="discount_amount[]" 
                        class="form-control w-75 discount-amount-input" 
                        value="${discountedValue.discount_amount ?? ''}" 
                        step="0.50"
                    />
                </td>

                <td>
                    <input 
                        type="number" 
                        name="final_price[]" 
                        class="form-control w-75 final-price-input" 
                        value="${discountedValue.final_price ?? ''}" 
                        step="0.50"
                    />
                </td>

                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-row">
                         <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;

            $("#product_table tbody").append(row);
        });

        // Select All checkbox
        $("#selectAllProducts")
            .off("change")
            .on("change", function () {
                $(".product-checkbox").prop("checked", $(this).is(":checked"));
            });

        // Add Selected
        $("#addSelectedProducts")
            .off("click")
            .on("click", function () {
                let selected = [];
                $(".product-checkbox:checked").each(function () {
                    selected.push($(this).val());
                });

                if (selected.length === 0) {
                    alert("Please select at least one product");
                    return;
                }

                console.log("Selected Products:", selected);
                alert("Selected " + selected.length + " products");
            });
    }

});

$(document).ready(function () {
    // Initialize Select2 with checkbox template
    $('#category').select2({
        placeholder: "--Select Categories--",
        allowClear: true,
        closeOnSelect: false,
        width: '100%',
        templateSelection: formatCategorySelection
    });
    $('#subcategory').select2({
        placeholder: "--Select Sub Categories--",
        allowClear: true,
        closeOnSelect: false,
        width: '100%',
    });
    $('#brand_name').select2({
        placeholder: "--Select Brands--",
        allowClear: true,
        closeOnSelect: false,
        width: '100%',
    });

    // Format selected items (pills/badges)
    function formatCategorySelection(category) {
        return category.text;
    }

    // Handle checkbox click directly
    $(document).on('mousedown', '.select2-results__option .select2-checkbox', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $option = $(this).closest('.select2-results__option');
        var $select = $('#category');
        var value = $option.data('data').id;
        var currentValues = $select.val() || [];

        if ($(this).is(':checked')) {
            // Remove from selection
            currentValues = currentValues.filter(function (v) { return v != value; });
        } else {
            // Add to selection
            currentValues.push(value);
        }

        $select.val(currentValues).trigger('change');
    });
});



