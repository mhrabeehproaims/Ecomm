
$(document).ready(function () {
    // Set default dates
    var today = new Date();
    var nextYear = new Date();
    nextYear.setFullYear(today.getFullYear() + 1);

    $('#f_date').val(today.toISOString().split('T')[0]);
    $('#t_date').val(nextYear.toISOString().split('T')[0]);

    // Load categories and products
    loadCategories();
    loadProducts();
    updatePreview();

    // Test notification function (for debugging)
    window.testNotification = function () {
        showNotification('This is a test success notification!', 'success');
        setTimeout(function () {
            showNotification('This is a test error notification!', 'error');
        }, 2000);
    };

    // Handle navigation type change
    $('#reference_type').change(function () {
        var type = $(this).val();

        // Hide all fields
        $('#custom_link_field').hide();

        // Show relevant field
        if (type === 'link') {
            $('.ref-dev').hide();
            $('.ref-dev').val('');
            $('#custom_link_field').show();
        } else {
            $('.ref-dev').show();
            $('#select-reference').val('');
        }
    });

    // Handle button position type change
    $('#button_position_type').change(function () {
        var type = $(this).val();

        if (type === 'preset') {
            $('#preset_position_field').show();
            $('#custom_position_fields').hide();
        } else if (type === 'custom') {
            $('#preset_position_field').hide();
            $('#custom_position_fields').show();
        }
    });

    // Handle preset position change
    $('#preset_position').change(function () {
        updatePreview();
    });

    // Handle custom position changes
    $('#button_x_position, #button_y_position').on('input', function () {
        updatePreview();
    });

    // Update button preview
    function updatePreview() {
        var positionType = $('#button_position_type').val();
        var x = 50,
            y = 50;

        if (positionType === 'preset') {
            var preset = $('#preset_position').val();
            switch (preset) {
                case 'center':
                    x = 50;
                    y = 50;
                    break;
                case 'left':
                    x = 20;
                    y = 50;
                    break;
                case 'right':
                    x = 80;
                    y = 50;
                    break;
                case 'top-left':
                    x = 20;
                    y = 20;
                    break;
                case 'top-center':
                    x = 50;
                    y = 20;
                    break;
                case 'top-right':
                    x = 80;
                    y = 20;
                    break;
                case 'bottom-left':
                    x = 20;
                    y = 80;
                    break;
                case 'bottom-center':
                    x = 50;
                    y = 80;
                    break;
                case 'bottom-right':
                    x = 80;
                    y = 80;
                    break;
            }
        } else if (positionType === 'custom') {
            x = parseInt($('#button_x_position').val()) || 50;
            y = parseInt($('#button_y_position').val()) || 50;
        }

        $('#preview_button').css({
            'left': x + '%',
            'top': y + '%'
        });
    }

    // Custom notification function
    function showNotification(message, type) {
        console.log('showNotification called:', message, type);

        // Create a simple, guaranteed-to-work notification
        var notificationHtml = '<div style="' +
            'background: ' + (type === 'success' ? '#28a745' : '#dc3545') + '; ' +
            'color: white; ' +
            'padding: 15px 20px; ' +
            'margin: 10px 0; ' +
            'border-radius: 5px; ' +
            'font-size: 16px; ' +
            'font-weight: bold; ' +
            'text-align: center; ' +
            'box-shadow: 0 4px 8px rgba(0,0,0,0.2); ' +
            'position: relative; ' +
            'z-index: 9999;' +
            '">' +
            '<span style="margin-right: 10px;">' + (type === 'success' ? '✓' : '✗') + '</span>' +
            message +
            '<button onclick="this.parentElement.remove()" style="' +
            'position: absolute; ' +
            'right: 10px; ' +
            'top: 50%; ' +
            'transform: translateY(-50%); ' +
            'background: none; ' +
            'border: none; ' +
            'color: white; ' +
            'font-size: 20px; ' +
            'cursor: pointer; ' +
            'font-weight: bold;' +
            '">&times;</button>' +
            '</div>';

        console.log('Notification HTML:', notificationHtml);

        // Try multiple methods to show notification
        if ($('#notification-area').length > 0) {
            $('#notification-area').html(notificationHtml);
            console.log('Notification added to notification-area');
        } else {
            // Create notification area if it doesn't exist
            $('body').prepend(
                '<div id="notification-area" style="position: fixed; top: 20px; right: 20px; z-index: 9999; width: 300px;"></div>'
            );
            $('#notification-area').html(notificationHtml);
            console.log('Created notification area and added notification');
        }

        // Also show as alert as backup
        // if (type === 'success') {
        //     alert('SUCCESS: ' + message);
        // } else {
        //     alert('ERROR: ' + message);
        // }

        // Auto-hide after 8 seconds
        setTimeout(function () {
            $('#notification-area div').fadeOut(500, function () {
                $(this).remove();
            });
        }, 8000);
    }

    // ...existing code...

    // Update preview button style on customization change
    $('#button_color, #button_text_color, #button_font_size, #button_shape').on('input change', function () {
        updateButtonStyle();
    });

    function updateButtonStyle() {
        var bg = $('#button_color').val() || '#ff6b00';
        var color = $('#button_text_color').val() || '#fff';
        var size = $('#button_font_size').val() || '16';
        var shape = $('#button_shape').val() || 'rounded';
        var borderRadius = '8px';
        if (shape === 'pill') borderRadius = '999px';
        if (shape === 'square') borderRadius = '0px';

        $('#preview_button').css({
            'background': bg,
            'color': color,
            'font-size': size + 'px',
            'border-radius': borderRadius
        });
    }

    // Call once on page load
    updateButtonStyle();
    // Load categories function
    function loadCategories() {
        $.ajax({
            url: base_url,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    var options = '<option value="">Select a category</option>';
                    $.each(response.categories, function (index, category) {
                        options += '<option value="' + category.category_id + '">' +
                            category.category_name + '</option>';
                    });
                    $('#category_id').html(options);
                }
            }
        });
    }

    // Load products function
    function loadProducts() {
        $.ajax({
            url: base_url,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success') {
                    var options = '<option value="">Select a product</option>';
                    $.each(response.products, function (index, product) {
                        options += '<option value="' + product.product_id + '">' + product
                            .product_name + '</option>';
                    });
                    $('#product_id').html(options);
                }
            }
        });
    }

    // Form submission
    // $('#addBannerForm').submit(function (e) {
    //     e.preventDefault();

    //     var formData = new FormData(this);

    //     $.ajax({
    //         url: '<?= base_url(); ?>Admin/insert_main_banner',
    //         type: 'POST',
    //         data: formData,
    //         processData: false,
    //         contentType: false,
    //         dataType: 'json',
    //         beforeSend: function () {
    //             $('button[type="submit"]').prop('disabled', true).html(
    //                 '<i class="fas fa-spinner fa-spin"></i> Adding...');
    //         },
    //         success: function (response) {
    //             if (response.status === 'success') {
    //                 // Try toastr first, fallback to custom notification
    //                 if (typeof toastr !== 'undefined') {
    //                     toastr.success('Banner added successfully');
    //                 } else {
    //                     showNotification('Banner added successfully!', 'success');
    //                 }
    //                 setTimeout(function () {
    //                     window.location.href = base_url;
    //                 }, 1500);
    //             } else {
    //                 if (typeof toastr !== 'undefined') {
    //                     toastr.error(response.message || 'Failed to add banner');
    //                 } else {
    //                     showNotification('Error: ' + (response.message ||
    //                         'Failed to add banner'), 'error');
    //                 }
    //             }
    //         },
    //         error: function () {
    //             if (typeof toastr !== 'undefined') {
    //                 toastr.error('An error occurred while adding banner');
    //             } else {
    //                 showNotification('An error occurred while adding banner', 'error');
    //             }
    //         },
    //         complete: function () {
    //             $('button[type="submit"]').prop('disabled', false).html(
    //                 '<i class="fas fa-save"></i> Add Banner');
    //         }
    //     });
    // });
    // ------------------------------------------------------------------------------------------------------------

    let table = $('#datatable').DataTable();

    $('#create_banner').on('click', function () {
        $("#bannerModalLabel").text('New Banner');
        $("#banner_name").val('');
        $("#hidden_id").val('');
        $("#banner_status").val(1);
        $(".btn.btn-primary").text('Save');
        $('#bannerModal').modal('show');
    });

    $.ajaxSetup({
        headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $(document).on('click', '.editBtn', function () {
        let id = $(this).data('id');
        let name = $(this).data('banner_title');
        let reference_type = $(this).data('reference_type');
        let reference_id = $(this).data('reference_id');
        let banner_subtitle = $(this).data('banner_subtitle');
        let button_text = $(this).data('button_text');
        let button_link = $(this).data('button_link');
        let button_position_type = $(this).data('button_position_type');
        let preset_position = $(this).data('preset_position');
        let button_x_position = $(this).data('button_x_position');
        let button_y_position = $(this).data('button_y_position');
        let from_date = $(this).data('from_date');
        let to_date = $(this).data('to_date');
        let button_color = $(this).data('button_color');
        let button_text_color = $(this).data('button_text_color');
        let button_font_size = $(this).data('button_font_size');
        let button_shape = $(this).data('button_shape');
        let sort_order = $(this).data('sort_order');
        let status = $(this).data('banner_status');
        $("#banner_image").attr('required', false);

        $("#hidden_id").val(id);
        $("#banner_title").val(name);

        $("#subtitle").val(banner_subtitle);
        $("#button_text").val(button_text);
        $("#button_link").val(button_link);
        $("#button_position_type").val(button_position_type).trigger('change');
        $("#preset_position").val(preset_position).change();
        $("#button_x_position").val(button_x_position);
        $("#button_y_position").val(button_y_position);
        $("#from_date").val(from_date);
        $("#to_date").val(to_date);
        $("#button_color").val(button_color);
        $("#button_text_color").val(button_text_color);
        $("#button_font_size").text(button_font_size);
        $("#button_shape").val(button_shape).change();

        $("#banner_order").val(sort_order).trigger('change');
        $("#banner_status").val(status).trigger('change');
        $("#bannerModalLabel").text('Update Banner');
        $(".btn.btn-primary").text('update');
        if (reference_type != "") {
            $("#reference_type").val(reference_type).trigger('change');
            getreferenceTypes(reference_type, reference_id);
        }
        updatePreview();
        $("#bannerModal").modal('show');
    });

    $("#create-banner").submit(function (e) {
        e.preventDefault();

        let id = $("#hidden_id").val();
        let url = id ? "/banner/" + id : "/banner";

        let formData = new FormData(this);

        // Ensure button position type is always included
        formData.set('button_position_type', $('#button_position_type').val() || 'preset');

        // Handle position fields based on selected type
        let positionType = $('#button_position_type').val();
        if (positionType === 'preset') {
            // Include preset position
            formData.set('preset_position', $('#preset_position').val() || 'center');
            // Remove custom position fields if they exist
            formData.delete('button_x_position');
            formData.delete('button_y_position');
        } else if (positionType === 'custom') {
            // Include custom position fields
            formData.set('button_x_position', $('#button_x_position').val() || '50');
            formData.set('button_y_position', $('#button_y_position').val() || '50');
            // Remove preset position if it exists
            formData.delete('preset_position');
        }

        // Handle reference type fields
        let referenceType = $('#reference_type').val();
        if (referenceType === 'link') {
            // Include button_link, remove select-reference
            formData.set('button_link', $('#button_link').val());
            formData.delete('select-reference');
        } else {
            // Include select-reference, remove button_link
            formData.set('select-reference', $('#select-reference').val());
            formData.delete('button_link');
        }

        if (id) {
            formData.append("_method", "PUT");
        }

        // Debug: Log all FormData entries (remove in production)
        console.log('FormData being sent:');
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        $.ajax({
            type: 'POST',
            url: url,
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            beforeSend: function () {
                $('.btn.btn-primary').prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> ' + (id ? 'Updating...' : 'Adding...')
                );
            },
            success: function (response) {
                if (response.status === 'success') {
                    // Show success message
                    if (typeof toastr !== 'undefined') {
                        toastr.success(response.message || (id ? 'Banner updated successfully' : 'Banner added successfully'));
                    } else {
                        showNotification(response.message || (id ? 'Banner updated successfully!' : 'Banner added successfully!'), 'success');
                    }

                    $("#bannerModal").modal('hide');

                    // Reset form
                    $("#create-banner")[0].reset();
                    $("#hidden_id").val('');

                    // Reload or update the banner list
                    setTimeout(function () {
                        location.reload(); // Or use a function to reload only the banner table
                    }, 500);
                } else {
                    // Handle validation or custom error responses
                    let errorMessage = '';

                    // Check if Laravel returned validation errors (object of errors)
                    if (response.errors) {
                        // Loop through all validation error messages
                        $.each(response.errors, function (field, messages) {
                            errorMessage += messages.join('<br>') + '<br>';
                        });
                    } else if (response.message) {
                        // Fallback to general error message
                        errorMessage = response.message;
                    } else {
                        errorMessage = id ? 'Failed to update banner' : 'Failed to add banner';
                    }

                    // Show all validation errors
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMessage);
                    } else {
                        alert(errorMessage);
                    }
                }
            },
            error: function (xhr, status, error) {
                let errorMessage = id ? 'An error occurred while updating banner' : 'An error occurred while adding banner';

                // Try to extract the exact error message
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        // Laravel usually sends a top-level "message"
                        errorMessage = xhr.responseJSON.message;
                    }

                    // Sometimes Laravel sends validation errors under "errors"
                    if (xhr.responseJSON.errors) {
                        let errorMessage = '';
                        $.each(xhr.responseJSON.errors, function (field, messages) {
                            errorMessage += messages.join('\n') + '\n';
                        });
                        alert(errorMessage.trim());
                    }

                } else if (xhr.responseText) {
                    // If no JSON, show raw text (for debugging)
                    errorMessage = xhr.responseText;
                } else if (error) {
                    // fallback to generic JS error
                    errorMessage = error;
                }

                // Show the actual error
                if (typeof toastr !== 'undefined') {
                    toastr.error(errorMessage);
                } else {
                    alert(errorMessage);
                }
            },
            complete: function () {
                let submitText = id ? '<i class="fas fa-save"></i> Update Banner' : '<i class="fas fa-save"></i> Add Banner';
                $('.btn.btn-primary').prop('disabled', false).html(submitText);
            }
        });
    });

    let row;

    $(document).on('click', '.BtnDelete', function () {
        let id = $(this).data('id');
        let name = $(this).data('name');
        row = $(this).closest('tr');
        $(".name").text(name);
        $("#hidden_id").val(id);
        $(".btn.btn-primary").text('Confirm');

        $("#DeleteBannerModal").modal('show');
    });

    $("#delete-banner").on('submit', function (e) {
        e.preventDefault();
        let id = $('#hidden_id').val();

        $.ajax({
            method: 'DELETE',
            url: '/banner/' + id,
            dataType: 'JSON',
            success: function (response) {
                console.log(response.status);
                if (response.status == "success") {
                    table.row(row).remove().draw(false);
                    $("#datatable").focus();
                    $("#DeleteBannerModal").modal('hide');
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

    $("#reference_type").on('change', function (e) {
        e.preventDefault();
        let type = $(this).val();
        let reference_id = null;
        getreferenceTypes(type, reference_id)
    });

});

function getreferenceTypes(type, reference_id) {
    let options = '<option value="">Select</option>';
    $.ajax({
        method: 'GET',
        url: '/banner/getBannerTypes/' + type,
        dataType: 'JSON',
        success: function (response) {
            // console.log(response.status);
            if (response.status == "success") {
                switch (type) {
                    case 'category':
                        response.data.forEach(item => {
                            options += `<option value="${item.categ_id}" ${parseInt(item.categ_id) === parseInt(reference_id) ? "selected" : ""}> ${item.categ_name} </option>`;
                        });
                        break;

                    case 'subcategory':
                        response.data.forEach(item => {
                            options += `<option value="${item.subCat_id}" ${parseInt(item.subCat_id) === parseInt(reference_id) ? "selected" : ""}> ${item.subCat_name} </option>`;
                        });
                        break;

                    case 'brand':
                        response.data.forEach(item => {
                            options += `<option value="${item.brand_id}" ${parseInt(item.brand_id) === parseInt(reference_id) ? "selected" : ""}> ${item.brand_name} </option>`;
                        });
                        break;

                    case 'product':
                        response.data.forEach(item => {
                            options += `<option value="${item.product_id}" ${parseInt(item.product_id) === parseInt(reference_id) ? "selected" : ""}> ${item.product_name} </option>`;
                        });

                        break;
                }
                $("#select-reference").html(options);
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

}
