$(document).ready(function () {

    let table = $('#datatable').DataTable();

    $('#create_tax').on('click', function () {
        $("#TaxModalLabel").text('Create New Tax');
        $("#tax_name").val('');
        $("#hidden_id").val('');
        $("#tax_status").val(1);
        $("#tax_percent").val('');
        $("#tax_igst").val('');
        $("#tax_cgst").val('');
        $("#tax_sgst").val('');
        $(".btn.btn-primary").text('Save');
        $('#TaxModal').modal('show');
    });

    $.ajaxSetup({
        headers:
            { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $(document).on('click', '.editBtn', function () {
        let id = $(this).data('id');
        let name = $(this).data('taxname');
        let status = $(this).data('status');
        let taxprcnt = $(this).data('taxprcnt');
        let tax_igst = $(this).data('igst');
        let tax_cgst = $(this).data('cgst');
        let tax_sgst = $(this).data('sgst');

        $("#tax_name").val(name);
        $("#tax_percent").val(taxprcnt);
        $("#tax_igst").val(tax_igst);
        $("#tax_cgst").val(tax_cgst);
        $("#tax_sgst").val(tax_sgst);

        $("#hidden_id").val(id);
        $("#tax_status").val(status).change();
        $("#TaxModalLabel").text('Update Tax');
        $(".btn.btn-primary").text('update');


        $("#TaxModal").modal('show');
    });

    $("#tax_percent").on('input', function () {
        let value = parseFloat($(this).val());
        if (value >= 0) {
            $("#tax_igst").val(value);
            $("#tax_cgst").val(value / 2);
            $("#tax_sgst").val(value / 2);
        } else {
            $("#tax_igst").val('');
            $("#tax_cgst").val('');
            $("#tax_sgst").val('');
        }

    })

    $("#create-tax").submit(function (e) {
        e.preventDefault();

        let id = $("#hidden_id").val();
        let url = id ? "/tax-master/" + id : "/tax-master";

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
                    $("#TaxModal").modal('hide');
                    // $(".btn.btn-primary").text("Save");
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function (xhr) {
                let response = JSON.parse(xhr.responseText);

                // Show the main message
                // alert(response.message);

                // OR show the first specific error
                if (response.errors) {
                    let firstErrorKey = Object.keys(response.errors)[0];
                    let firstErrorMessage = response.errors[firstErrorKey][0];

                    alert(firstErrorMessage); 
                }

                if (id) {
                    $(".btn.btn-primary").text('Update');
                } else {
                    $(".btn.btn-primary").text('Save');
                }
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

        $("#DeleteTaxModal").modal('show');
    });

    $("#deleteTaxForm").on('submit', function (e) {
        e.preventDefault();
        let id = $('#hidden_id').val();

        $.ajax({
            method: 'DELETE',
            url: '/tax-master/' + id,
            dataType: 'JSON',
            success: function (response) {
                console.log(response.status);
                if (response.status == "success") {
                    table.row(row).remove().draw(false);
                    $("#datatable").focus();
                    $("#DeleteTaxModal").modal('hide');
                } else {
                    alert(response.status);
                }
            },
            error: function (xhr) {
                        let response = JSON.parse(xhr.responseText);

                // console.log(response.message);

                console.log(response.errors.size_name[0]);

                alert(response.message);

              
            }
        });
    });
});
