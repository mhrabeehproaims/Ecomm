    @push('styles')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

        <style>
            /* Product Modal Specific Styles */
            .product-modal-dialog {
                max-width: 80vw;
                margin: 2rem auto;
            }

            .product-modal-content {
                border: none;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
                overflow: hidden;
            }

            .product-modal-header {
                background: linear-gradient(135deg, #764ba2 0%, #d5bcef 100%);
                color: white;
                padding: 2rem;
                border: none;
            }

            .product-modal-title {
                font-weight: 500;
                font-size: 1.7rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .product-modal-title::before {
                content: '\f02d';
                font-family: 'Font Awesome 6 Free';
                font-weight: 700;
                font-size: 1.5rem;
            }

            .product-modal-close:hover {
                opacity: 1;
                transform: scale(1.1);
            }

            .product-modal-body {
                padding: 0;
                background: #f8f9fa;
            }

            .product-details-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 0;
            }

            .product-details-section {
                background: white;
                padding: 2rem;
            }

            .product-details-section.basic-info {
                border-right: 3px solid #e9ecef;
            }

            .product-section-header {
                background: linear-gradient(135deg, #667eea20, #764ba220);
                margin: -2rem -2rem 2rem -2rem;
                padding: 1.5rem 2rem;
                color: #495057;
                font-weight: 500;
                font-size: 1.2rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }

            .product-detail-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem 0;
                border-bottom: 1px solid #f1f3f4;
                transition: all 0.3s ease;
                border-radius: 8px;
                margin-bottom: 0.5rem;
            }

            .product-detail-row:hover {
                background: linear-gradient(135deg, #667eea10, #764ba210);
                padding-left: 1rem;
                margin-left: -1rem;
                margin-right: -1rem;
                padding-right: 1rem;
            }

            .product-detail-label {
                font-weight: 600;
                color: #6c757d;
                font-size: 0.95rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                flex: 0 0 50%;
            }

            .product-detail-value {
                color: #212529;
                font-size: 16px;
                font-weight: 500;
                text-align: right;
                flex: 1;
                word-wrap: break-word;
            }

            /* Special styling for different value types */
            .product-price-value {
                color: #28a745;
                font-weight: 700;
                font-size: 1.1rem;
            }

            .product-stock-value {
                font-weight: 600;
            }

            .product-stock-low {
                color: #dc3545;
            }

            .product-stock-good {
                color: #28a745;
            }

            .product-status-badge {
                padding: 0.4rem 1rem;
                border-radius: 50px;
                font-size: 0.85rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .product-status-active {
                background: linear-gradient(135deg, #28a745, #20c997);
                color: white;
            }

            .product-status-inactive {
                background: linear-gradient(135deg, #dc3545, #fd7e14);
                color: white;
            }

            .product-tax-section {
                background: linear-gradient(135deg, #ffeaa720, #ffcd3920);
                border-radius: 12px;
                padding: 1.5rem;
                margin: 1rem 0;
            }

            .product-tax-header {
                margin: -1.5rem -1.5rem 1.5rem -1.5rem;
                padding: 1rem 1.5rem;
                background: linear-gradient(135deg, #ffc10720, #ff851020);
            }

            .product-modal-footer {
                background: white;
                padding: 1.5rem 2rem;
                border-top: 3px solid #e9ecef;
                gap: 1rem;
            }

            .product-modal-btn {
                padding: 0.75rem 2rem;
                border-radius: 50px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                transition: all 0.3s ease;
            }

            .product-modal-btn.secondary {
                background: linear-gradient(135deg, #6c757d, #495057);
                border: none;
                color: white;
            }

            .product-modal-btn.primary {
                background: linear-gradient(135deg, #667eea, #764ba2);
                border: none;
                color: white;
            }

            .product-modal-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            }

            .product-images-section, .description-images-section {
                grid-column: span 2;
                background: linear-gradient(135deg, #f8f9fa, #e9ecef);
                padding: 2rem;
                text-align: center;
            }

            .product-image-card {
                height: 100%;
            }

            .product-image-placeholder {
                min-height: 150px;
                background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            }

            /* Icon classes */
            .icon-basic-info::before {
                content: '\f05a';
                font-family: 'Font Awesome 6 Free';
                font-weight: 700;
            }

            .icon-pricing::before {
                content: '\f155';
                font-family: 'Font Awesome 6 Free';
                font-weight: 700;
            }

            .icon-tax-info::before {
                content: '\f571';
                font-family: 'Font Awesome 6 Free';
                font-weight: 700;
            }

            .icon-product-images::before {
                content: '\f03e';
                font-family: 'Font Awesome 6 Free';
                font-weight: 700;
            }

            .imageName {
                font-size: 14px;
                font-weight: 500;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .product-details-grid {
                    grid-template-columns: 1fr;
                }

                .product-details-section.basic-info {
                    border-right: none;
                    border-bottom: 3px solid #e9ecef;
                }

                .product-detail-row {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 0.5rem;
                }

                .product-detail-value {
                    text-align: left;
                }
            }
        </style>
    @endpush

    <!-- Product Modal -->
    <div class="modal fade" id="showProductModal" tabindex="-1" aria-labelledby="ProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl product-modal-dialog">
            <div class="modal-content product-modal-content">
                <div class="modal-header product-modal-header">
                    <h5 class="modal-title product-modal-title" id="ProductModalLabel">Product Details</h5>
                    <button type="button" class="btn-close product-modal-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body product-modal-body">
                    <div class="product-details-grid">
                        <!-- Basic Information Section -->
                        <div class="product-details-section basic-info">
                            <div class="product-section-header icon-basic-info">
                                Basic Information
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">Product Name</span>
                                <span class="product-detail-value" id="product_name"></span>
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">HSN Code</span>
                                <span class="product-detail-value" id="hsn_code"></span>
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">Category</span>
                                <span class="product-detail-value" id="product_category"></span>
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">Subcategory</span>
                                <span class="product-detail-value" id="sub_category"></span>
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">Description</span>
                                <span class="product-detail-value" id="product_desc"></span>
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">Health Benefits</span>
                                <span class="product-detail-value" id="product_health"></span>
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">Brand</span>
                                <span class="product-detail-value" id="product_brand"></span>
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">Product Size</span>
                                <span class="product-detail-value" id="product_size"></span>
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">Weight</span>
                                <span class="product-detail-value" id="product_weight"></span>
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">Package Type</span>
                                <span class="product-detail-value" id="package_type"></span>
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">Status</span>
                                <span class="product-detail-value">
                                    <span class="product-status-badge product-status-active" id="product_status"></span>
                                </span>
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">Created On</span>
                                <span class="product-detail-value" id="created_date"></span>
                            </div>
                        </div>

                        <!-- Stock & Pricing Section -->
                        <div class="product-details-section pricing-info">
                            <div class="product-section-header icon-pricing">
                                Stock & Pricing
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">Current Stock</span>
                                <span class="product-detail-value product-stock-value product-stock-good"
                                    id="product_stock"></span>
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">Minimum Stock</span>
                                <span class="product-detail-value product-stock-value" id="product_minStock"></span>
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">MRP</span>
                                <span class="product-detail-value product-price-value" id="product_mrp"></span>
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">Retail Price</span>
                                <span class="product-detail-value product-price-value" id="product_retail"></span>
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">Wholesale Price</span>
                                <span class="product-detail-value product-price-value" id="product_Wholesale"></span>
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">Special Price</span>
                                <span class="product-detail-value product-price-value" id="product_specialPrice"></span>
                            </div>

                            <div class="product-detail-row">
                                <span class="product-detail-label">E-Commerce Default Price</span>
                                <span class="product-detail-value product-price-value" id="product_defaultPrice"></span>
                            </div>

                            <div class="product-tax-section">
                                <div class="product-section-header icon-tax-info product-tax-header">
                                    Tax Information
                                </div>

                                <div class="product-detail-row">
                                    <span class="product-detail-label">Tax Schedule</span>
                                    <span class="product-detail-value" id="tax_schedule"></span>
                                </div>

                                <div class="product-detail-row">
                                    <span class="product-detail-label">Tax Percentage</span>
                                    <span class="product-detail-value" id="tax_percentage"></span>
                                </div>

                                <div class="product-detail-row">
                                    <span class="product-detail-label">IGST</span>
                                    <span class="product-detail-value" id="product_igst"></span>
                                </div>

                                <div class="product-detail-row">
                                    <span class="product-detail-label">CGST</span>
                                    <span class="product-detail-value" id="product_cgst"></span>
                                </div>

                                <div class="product-detail-row">
                                    <span class="product-detail-label">SGST</span>
                                    <span class="product-detail-value" id="product_sgst"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Images Section -->
                        <div class="description-images-section">
                            <div class="product-section-header icon-product-images">
                                Description Images
                            </div>
                            <div class="row description-image-container">
                            </div>
                        </div>

                        <div class="product-images-section">
                            <div class="product-section-header icon-product-images">
                                Product Images
                            </div>
                            <div class="row product-image-container">
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer product-modal-footer py-3">
                    <button type="button" class="btn btn-outline-secondary px-2" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Close
                    </button>
                </div>
            </div>
        </div>
    </div>
