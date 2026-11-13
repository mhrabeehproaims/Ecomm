@extends('layouts.app')
@push('styles')
    {{-- image view lib --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.css" />

    {{-- to adjust image viwer bg color --}}
    <style>
        .viewer-backdrop {
            background-color: rgba(0, 0, 0, 0.7) !important;
        }
    </style>
@endpush

@section('title', 'Vantara | Banner')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0 font-size-18">Banner Management</h4>

                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Banners</li>
                                </ol>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- end page title -->
                <div class="create-btn py-2">
                    <button class="btn btn-secondary" id="create_banner">Create Banner</button>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">

                                <table id="datatable" class="table table-bordered dt-responsive  nowrap w-100">
                                    <thead>
                                        <tr class="text-center">
                                            <th class="text-center">#</th>
                                            <th class="text-center">Banner Image</th>
                                            <th class="text-center">Title</th>
                                            <th class="text-center">Reference Type</th>
                                            <th class="text-center">Reference Name</th>
                                            <th class="text-center">View Order</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($banners as $index => $banner)
                                            <tr>
                                                <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                                <td class="text-center align-middle">
                                                    @if ($banner->banner_image)
                                                        <img class="catgimage rounded"
                                                            src="{{ asset('storage/BannerImage/' . $banner->banner_image) }}"
                                                            {{-- src="{{ asset('/vantara/storage/app/public/BannerImage/' . $banner->banner_image) }}" --}} alt="Banner image" srcset=""
                                                            width="70px">
                                                    @else
                                                        No Image
                                                    @endif
                                                </td>
                                                <td class="text-center align-middle">{{ $banner->banner_title }}</td>
                                                <td class="text-center align-middle">{{ ucwords($banner->reference_type) }}
                                                </td>
                                                @if ($banner->reference_type == 'category')
                                                    <td class="text-center align-middle">
                                                        {{ $banner->category->categ_name ?? '' }}
                                                    </td>
                                                @elseif ($banner->reference_type == 'subcategory')
                                                    <td class="text-center align-middle">
                                                        {{ $banner->subcategory->subCat_name ?? '' }}</td>
                                                @elseif ($banner->reference_type == 'brand')
                                                    <td class="text-center align-middle">
                                                        {{ $banner->brand->brand_name ?? '' }}
                                                    </td>
                                                @elseif ($banner->reference_type == 'product')
                                                    <td class="text-center align-middle">
                                                        {{ $banner->product->product_name ?? '' }}</td>
                                                @elseif ($banner->reference_type == 'link')
                                                    <td class="text-center align-middle">
                                                        @php
                                                            $link = $banner->button_link ?? '';
                                                            $displayLink =
                                                                strlen($link) > 10 ? substr($link, 0, 15) . '…' : $link;
                                                        @endphp
                                                        @if ($link)
                                                            <a href="{{ $link }}"
                                                                target="_blank">{{ $displayLink }}</a>
                                                        @endif
                                                    </td>
                                                @else
                                                    <td> </td>
                                                @endif
                                                <td class="text-center align-middle">{{ $banner->sort_order }}</td>
                                                <td class="text-center align-middle">{{ $banner->status_text }}</td>
                                                <td class="text-center align-middle">
                                                    <button class="btn editBtn m-0" data-id={{ $banner->banner_id }}
                                                        data-banner_title="{{ $banner->banner_title }}"
                                                        data-banner_image="{{ $banner->banner_image }}"
                                                        data-reference_type="{{ $banner->reference_type }}"
                                                        data-reference_id="{{ $banner->reference_id }}"
                                                        data-sort_order="{{ $banner->sort_order }}"
                                                        data-banner_subtitle="{{ $banner->banner_subtitle }}"
                                                        data-button_text="{{ $banner->button_text }}"
                                                        data-button_link="{{ $banner->button_link }}"
                                                        data-button_position_type="{{ $banner->button_position_type }}"
                                                        data-preset_position="{{ $banner->preset_position }}"
                                                        data-button_x_position="{{ $banner->button_x_position }}"
                                                        data-button_y_position="{{ $banner->button_y_position }}"
                                                        data-from_date="{{ $banner->from_date }}"
                                                        data-to_date="{{ $banner->to_date }}"
                                                        data-button_color="{{ $banner->button_color }}"
                                                        data-button_text_color="{{ $banner->button_text_color }}"
                                                        data-button_font_size="{{ $banner->button_font_size }}"
                                                        data-button_shape="{{ $banner->button_shape }}"
                                                        data-banner_status="{{ $banner->banner_status }}">
                                                        <i class="fas fa-pencil-alt"></i></button>
                                                    <button class="btn BtnDelete m-0" data-id="{{ $banner->banner_id }}"
                                                        data-name="{{ $banner->banner_title ?? '' }}">
                                                        <i class="fa fa-trash" aria-hidden="true"></i></button>
                                                </td>
                                            </tr>
                                        @empty
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div> <!-- end col -->
                </div> <!-- end row -->

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        @php
            $usedOrders = $banners->pluck('sort_order')->toArray();
        @endphp


        {{-- Create Banner modal start --}}
        <div class="modal fade modal-xl" id="bannerModal" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bannerModalLabel"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="create-banner" enctype="multipart/form-data">
                            <div class="col-12 row">
                                <div class="mb-3 col-12 col-md-6">
                                    <label for="recipient-name" class="col-form-label">Banner Image</label>
                                    <input type="file" name="banner_image" accept="image/*" class="form-control"
                                        id="banner_image" required>
                                    <div class="form-text">Recommended size: 1920x600px. Max size: 3MB</div>
                                </div>
                                <div class="mb-3 col-12 col-md-6">
                                    <label for="recipient-name" class="col-form-label">Banner Title</label>
                                    <input type="text" name="banner_title" class="form-control" id="banner_title"
                                        required>
                                </div>
                            </div>
                            <div class="col-12 row">
                                <div class="mb-3 col-12 col-md-6">
                                    <label for="subtitle" class="form-label">Banner Subtitle</label>
                                    <textarea class="form-control" id="subtitle" name="subtitle" rows="2"
                                        placeholder="e.g., Premium accessories for your everyday life"></textarea>
                                </div>
                                {{-- <div class="mb-3 col-md-6">
                                    <label for="navigation_type" class="form-label">Navigation Type <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control" id="navigation_type" name="navigation_type" required>
                                        <option value="link">Custom Link</option>
                                        <option value="category">Category Page</option>
                                        <option value="product">Specific Product</option>
                                    </select>
                                </div> --}}
                                <div class="mb-3 col-12 col-md-6">
                                    <label for="recipient-name" class="col-form-label">Reference Type</label>
                                    <select class="form-select" id="reference_type" name="reference_type" required>
                                        <option value="" selected>--Select Banner Reference--</option>
                                        <option value="category">Category</option>
                                        <option value="subcategory">Subcategory</option>
                                        <option value="brand">Brand</option>
                                        <option value="product">Product</option>
                                        <option value="link">Custom Link</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 row">
                                <div class="mb-3 col-12 col-md-6 ref-dev">
                                    <label for="select-reference" class="col-form-label">Select Reference</label>
                                    <select class="form-select select2" id="select-reference" name="select-reference">
                                        <option value="" selected>--Select Reference Type First--</option>
                                    </select>
                                </div>
                                <div class="mb-3 col-12 col-md-6" id="custom_link_field">
                                    <label for="button_link" class="form-label">Button Link</label>
                                    <input type="text" class="form-control" id="button_link" name="button_link"
                                        value="#new-arrivals" placeholder="e.g., #new-arrivals or /products">
                                </div>
                                <div class="mb-3 col-12 col-md-6">
                                    <label for="button_text" class="form-label">Button Text</label>
                                    <input type="text" class="form-control" id="button_text" name="button_text"
                                        value="Shop Now" required>
                                </div>
                                <input type="hidden" name="hidden_id" id="hidden_id">
                            </div>
                            <div class="col-12 row">
                                <div class="mb-3 col-12 col-md-6">
                                    <label for="button_position_type" class="form-label">Button Position Type</label>
                                    <select class="form-select" id="button_position_type" name="button_position_type">
                                        <option value="preset">Preset Position</option>
                                        <option value="custom">Custom Position</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 row" id="preset_position_field">
                                <div class="mb-3 col-12 col-md-6">
                                    <label for="preset_position" class="form-label">Preset Position</label>
                                    <select class="form-select" id="preset_position" name="preset_position">
                                        <option value="center">Center</option>
                                        <option value="left">Left</option>
                                        <option value="right">Right</option>
                                        <option value="top-left">Top Left</option>
                                        <option value="top-center">Top Center</option>
                                        <option value="top-right">Top Right</option>
                                        <option value="bottom-left">Bottom Left</option>
                                        <option value="bottom-center">Bottom Center</option>
                                        <option value="bottom-right">Bottom Right</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row" id="custom_position_fields" style="display: none;">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="button_x_position" class="form-label">X Position (%)</label>
                                        <input type="number" class="form-control" id="button_x_position"
                                            name="button_x_position" min="0" max="100" value="50"
                                            placeholder="0-100">
                                        <div class="form-text">Horizontal position (0% = left, 100% = right)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="button_y_position" class="form-label">Y Position (%)</label>
                                        <input type="number" class="form-control" id="button_y_position"
                                            name="button_y_position" min="0" max="100" value="50"
                                            placeholder="0-100">
                                        <div class="form-text">Vertical position (0% = top, 100% = bottom)</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Button Position Preview</label>
                                <div id="position_preview"
                                    style="width: 100%; height: 200px; border: 2px dashed #ccc; position: relative; background: #f8f9fa;">
                                    <div id="preview_button"
                                        style="position: absolute; background: #007bff; color: white; padding: 10px 20px; border-radius: 5px; transform: translate(-50%, -50%);">
                                        Button Preview
                                    </div>
                                </div>
                            </div>

                            <!-- Button Customization Fields -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="button_color" class="form-label">Button Background Color</label>
                                        <input type="color" class="form-control form-control-color" id="button_color"
                                            name="button_color" value="#ff6b00" title="Choose button background color">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="button_text_color" class="form-label">Button Text Color</label>
                                        <input type="color" class="form-control form-control-color"
                                            id="button_text_color" name="button_text_color" value="#ffffff"
                                            title="Choose button text color">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="button_font_size" class="form-label">Button Font Size
                                            (px)</label>
                                        <input type="number" class="form-control" id="button_font_size"
                                            name="button_font_size" value="16" min="10" max="48">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="button_shape" class="form-label">Button Shape</label>
                                        <select class="form-control" id="button_shape" name="button_shape">
                                            <option value="rounded">Rounded</option>
                                            <option value="pill">Pill</option>
                                            <option value="square">Square</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="f_date" class="form-label">From Date <span
                                                class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="f_date" name="f_date"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="t_date" class="form-label">To Date <span
                                                class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="t_date" name="t_date"
                                            required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <div class="col-6">
                                    <label for="recipient-name" class="col-form-label">Status</label>
                                    <select name="banner_status" id="banner_status" class="form-select">
                                        <option value=1>Active</option>
                                        <option value=0>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label for="order" class="col-form-label">Sort Order</label>
                                    <select name="banner_order" id="banner_order" class="form-select">
                                        @for ($i = 1; $i <= 10; $i++)
                                            :
                                            {{-- @if (!in_array($i, $usedOrders)) --}}
                                            <option value="{{ $i }}">{{ $i }}</option>
                                            {{-- @endif --}}
                                        @endfor
                                    </select>
                                </div>
                            </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary"></button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- end modal -->

    <x-delete-modal id="DeleteBannerModal" form_id="delete-banner" />

    <x-footer />
    </div>
@endsection

@push('page-scripts')
    {{-- image view script  --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.3/viewer.min.js"></script>

    <script>
        window.base_url = "{{ route('banner.index') }}";
        document.querySelectorAll('.catgimage').forEach(img => {
            new Viewer(img);
        });
    </script>

    <script src="{{ asset('assets/js/banner.js') }}"></script>
@endpush
