$(document).on('click', '.btnShowProductDetails', function (e) {
    e.preventDefault();
    let btn = $(this);
    $.ajax({
        method: 'GET',
        url: "/products/show/" + $(this).data('id'),
        dataType: 'json',
        beforeSend: function () {
            btn.addClass('d-none');
            $('.spinner' + btn.data('id')).removeClass('d-none');
        },
        success: function (response) {
            $('.spinner' + btn.data('id')).addClass('d-none');
            btn.removeClass('d-none');
            if (response.status == "success") {
                let images = response.data.images || [];
                let desc_images = response.data.desc_images || [];
                $('.product-image-container').empty(); // container holding all cards
                $('.description-image-container').empty(); // container holding all cards

                if (Array.isArray(images) && images.length > 0) {
                    images.forEach((img, index) => {
                        let imgUrl = img.image_url || (img.image_path ? '/storage/' + img.image_path.replace(/^\//, '').replace(/^storage\//, '') : '/images/placeholder.png');
                        let cardHtml = `
                            <div class="col-md-4 mb-3">
                                <div class="card product-image-card">
                                    <div class="card-body d-flex align-items-center justify-content-center product-image-placeholder p-1">
                                        <img src="${imgUrl}" class="img-fluid rounded" style="max-height:150px; object-fit:cover;" alt="Image ${index + 1}">
                                    </div>
                                    <div class="card-footer text-center">
                                        <small class="text-muted imageName">Image ${index + 1}</small>
                                    </div>
                                </div>
                            </div> `;
                        $('.product-image-container').append(cardHtml);
                    });
                } else {
                    $('.product-image-container').append('<div class="col-12 text-center text-muted py-3">No product images uploaded</div>');
                }

                // Description image
                if (Array.isArray(desc_images) && desc_images.length > 0) {
                    desc_images.forEach((img, index) => {
                        let imgUrl = img.image_url || (img.image_name ? '/storage/' + img.image_name.replace(/^\//, '').replace(/^storage\//, '') : '/images/placeholder.png');
                        let cardHtml2 = `
                            <div class="col-md-4 mb-3">
                                <div class="card product-image-card">
                                    <div class="card-body d-flex align-items-center justify-content-center product-image-placeholder p-1">
                                        <img src="${imgUrl}" class="img-fluid rounded" style="max-height:150px; object-fit:cover;" alt="Image ${index + 1}">
                                    </div>
                                    <div class="card-footer text-center">
                                        <small class="text-muted imageName">Image ${index + 1}</small>
                                    </div>
                                </div>
                            </div> `;
                        $('.description-image-container').append(cardHtml2);
                    });
                } else {
                    $('.description-image-container').append('<div class="col-12 text-center text-muted py-3">No description images uploaded</div>');
                }


                $("#product_name").text(response.data.name);
                $("#hsn_code").text(response.data.hsn_code);
                $("#product_category").text(response.data.category);
                $("#sub_category").text(response.data.sub_category);
                $("#product_desc").text(response.data.description);
                $("#product_health").text(response.data.health_benefit);
                $("#product_brand").text(response.data.brand_name);
                $("#product_size").text(response.data.size);
                $("#product_weight").text(response.data.product_weight);
                $("#product_stock").text(response.data.stock);
                $("#product_minStock").text(response.data.minimum_stock);
                $("#product_mrp").text(response.data.mrp);
                $("#product_retail").val(response.data.retail);
                $("#product_Wholesale").val(response.data.wholesale);
                $("#product_specialPrice").text(response.data.special_price);
                $("#product_defaultPrice").text(headline(response.data.primary_price));
                $("#tax_schedule").text(response.data.tax_schedule);
                $("#tax_percentage").text(response.data.tax_percentage);
                $("#product_igst").text(response.data.tax_igst);
                $("#product_cgst").text(response.data.tax_cgst);
                $("#product_sgst").text(response.data.tax_sgst);
                $("#product_status").text(response.data.status);
                $("#created_date").text(response.data.created_at);
                $("#showProductModal").modal('show');
            } else {
                alert(response.status);
            }
            // console.log(response);
        },
        error: function (err) {
            console.error("Error fetching product details:", err);
        }
    });
});

function headline(str) {
    if (!str) return '';
    return str
        .replace(/_/g, ' ')        // Replace underscores with spaces
        .replace(/\b\w/g, c => c.toUpperCase()); // Capitalize each word
}


var table = $('#datatable').DataTable();

let row;

$(document).on('click', '.BtnDelete', function () {
    let id = $(this).data('id');
    let name = $(this).data('name');

    row = table.row($(this).closest('tr'));

    $(".name").text(name);
    $("#hidden_id").val(id);
    $(".btn.btn-primary").text('Confirm');

    $("#DeleteProductModal").modal('show');
});


$("#deleteProductForm").on('submit', function (e) {
    e.preventDefault();
    let id = $('#hidden_id').val();

    $.ajax({
        method: 'DELETE',
        url: '/products/' + id,
        dataType: 'json',
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        beforsend: $(".btn.btn-primary").text('Please wait..'),
        success: function (response) {
            if (response.status === "success") {
                // Remove row from DataTable
                // row.remove().draw(false);

                // Close modal
                $("#DeleteProductModal").modal('hide');
                window.location.reload();
            } else {
                alert(response.message || response.status);
            }
        },
        error: function (xhr) {
            let response = xhr.responseJSON;
            alert(response?.message || 'Something went wrong!');
        }
    });
});


//Product Share
document.addEventListener('click', function (e) {
    if (e.target.closest('.share-btn')) {
        const btn = e.target.closest('.share-btn');
        const url = btn.getAttribute('data-url');

        if (navigator.share) {
            navigator.share({
                title: 'Check out this product!',
                text: 'Here’s a product you might like:',
                url: url
            }).catch(err => console.log('Share cancelled', err));
        } else {
            navigator.clipboard.writeText(url);
            alert('Link copied to clipboard!');
        }
    }
});


