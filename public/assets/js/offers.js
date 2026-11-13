$(document).ready(function () {
    $("#btnSelectOffer").on('click', function () {
        $("#offerSelectModal").modal('show');
    });
    $(".offer-item").on('click', function () {
        window.location = ($(this).data('url'));
    });

    $(".BtnDetails").on('click', function () {
        let id = $(this).data('id');

        $.ajax({
            type: 'GET',
            url: `/offers/${id}`,
            dataType: 'json',

            success: function (offer) {

                // Set simple text fields
                $("#d_title").text(offer.title ?? '');
                $("#d_offer_type").text(offer.offer_type ? formatText(offer.offer_type) : '');
                $("#d_start_date").text(formatDateTime(offer.start_date));
                $("#d_end_date").text(formatDateTime(offer.end_date));
                $("#discount_type").text((offer.discount_type ?? '').toUpperCase());
                $("#discount_from").text((offer.discount_from ?? '').toUpperCase());
                // Clear previous product rows
                $("#d_products_table").empty();

                // Append product rows
                if (offer.product_details && offer.product_details.length > 0) {
                    offer.product_details.forEach((p, index) => {

                        // find product name from offer.products list
                        let productName = '';
                        let product = offer.products.find(prod => prod.product_id == p.product_id);

                        if (product) {
                            productName = product.product_name;
                        }

                        $("#d_products_table").append(`
                            <tr>
                                <td>${index + 1}</td>
                                <td>${productName}</td> 
                                <td>₹${p.product_mrp}</td> 
                                <td>₹${p.product_srate}</td> 
                                <td>
                                    ${offer.discount_type === 'fixed'
                                ? `₹${p.discount_value}`
                                : `${p.discount_value}%`
                            }
                                </td>
                                <td>${p.discount_amount}</td>
                                <td>₹${p.final_price}</td>
                            </tr>
                        `);
                    });
                }

                // Open the modal
                let modal = new bootstrap.Modal(document.getElementById("offerDetailsModal"));
                modal.show();
            },

            error: function (xhr) {
                console.log(xhr.responseText);
            }
        });
    });
    function formatDateTime(datetime) {
        if (!datetime) return '';

        let date = new Date(datetime);

        // Extract parts
        let day = String(date.getDate()).padStart(2, '0');
        let month = String(date.getMonth() + 1).padStart(2, '0');
        let year = date.getFullYear();

        let hours = date.getHours();
        let minutes = String(date.getMinutes()).padStart(2, '0');
        let ampm = hours >= 12 ? 'PM' : 'AM';

        // Convert to 12-hour format
        hours = hours % 12;
        hours = hours ? hours : 12; // 0 → 12

        return `${day}-${month}-${year} ${hours}:${minutes} ${ampm}`;
    }
    function formatText(text) {
        return text
            .replace(/_/g, " ")          // replace underscores
            .toLowerCase()               // make all lower
            .replace(/\b\w/g, c => c.toUpperCase()); // capitalize first letter of every word
    }

});
