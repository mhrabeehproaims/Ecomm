@extends('layouts.app')
@section('title', 'Vantara | Products')
@section('content')
    <div class="main-content">
        <div class="page-content">
            <div class="container-fluid">
                <div class="container-fluid">
                    @if (session()->has('message'))
                        <div class="alert alert-warning alert-dismissible fade show col-5" role="alert">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    <!-- start page title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0 font-size-18">Profile</h4>

                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Profile</li>
                                    </ol>
                                </div>

                            </div>
                        </div>
                    </div>
                    <!-- end page title -->

                    <div class="row">
                        <div class="col-xl-12 col-lg-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm order-2 order-sm-1">
                                            <div class="d-flex align-items-start mt-3 mt-sm-0">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar-xl me-3">
                                                        <img src="{{ asset('assets/images/users/user-dummy-img.jpg') }}"
                                                            alt="" class="img-fluid rounded-circle d-block">
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div>
                                                        <h5 class="font-size-16 mb-1">{{ auth()->user()->name }}</h5>
                                                        <p class="text-muted font-size-13">Vantara, Wayanad, Kerala</p>

                                                        <div
                                                            class="d-flex flex-wrap align-items-start gap-2 gap-lg-3 text-muted font-size-13">
                                                            {{-- <div><i
                                                        class="mdi mdi-circle-medium me-1 text-success align-middle"></i>Development
                                                </div> --}}
                                                            {{-- <div><i
                                                        class="mdi mdi-circle-medium me-1 text-success align-middle"></i>phyllisgatlin@minia.com
                                                </div> --}}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- <div class="col-sm-auto order-1 order-sm-2">
                                <div class="d-flex align-items-start justify-content-end gap-2">

                                    <div>
                                        <div class="dropdown">
                                            <button class="btn btn-link font-size-16 shadow-none text-muted dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bx bx-dots-horizontal-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="#">Action</a>
                                                </li>
                                                <li><a class="dropdown-item" href="#">Another
                                                        action</a></li>
                                                <li><a class="dropdown-item" href="#">Something
                                                        else here</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                                    </div>

                                    <ul class="nav nav-tabs-custom card-header-tabs border-top mt-4" id="pills-tab"
                                        role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link px-3 active" data-bs-toggle="tab" href="#overview"
                                                role="tab">Overview</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link px-3" data-bs-toggle="tab" href="#edit"
                                                role="tab">Edit</a>
                                        </li>
                                    </ul>
                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->

                            <div class="tab-content">
                                <div class="tab-pane active" id="overview" role="tabpanel">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">About Head Office</h5>
                                        </div>
                                        <div class="card-body">
                                            <div>
                                                <div class="">
                                                    <div class="row">
                                                        <div class="col-xl-2">
                                                            <div>
                                                                <h5 class="font-size-15">Name :</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl">
                                                            <div class="text-muted col-3">
                                                                <p>{{ auth()->user()->name }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="py-3">
                                                    <div class="row">
                                                        <div class="col-xl-2">
                                                            <div>
                                                                <h5 class="font-size-15">Email :</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl">
                                                            <div class="text-muted">
                                                                <p>{{ auth()->user()->email }}</p>

                                                                {{-- <ul class="list-unstyled mb-0">
                                                        <li class="py-1"><i
                                                                class="mdi mdi-circle-medium me-1 text-success align-middle"></i>Donec
                                                            vitae sapien ut libero venenatis faucibus
                                                        </li>
                                                        <li class="py-1"><i
                                                                class="mdi mdi-circle-medium me-1 text-success align-middle"></i>Quisque
                                                            rutrum aenean imperdiet</li>
                                                        <li class="py-1"><i
                                                                class="mdi mdi-circle-medium me-1 text-success align-middle"></i>Integer
                                                            ante a consectetuer eget</li>
                                                        <li class="py-1"><i
                                                                class="mdi mdi-circle-medium me-1 text-success align-middle"></i>Phasellus
                                                            nec sem in justo pellentesque</li>
                                                    </ul> --}}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="pb-3">
                                                    <div class="row">
                                                        <div class="col-xl-2">
                                                            <div>
                                                                <h5 class="font-size-15">Status :</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl">
                                                            <div class="text-muted">
                                                                <p>Active</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- end card body -->
                                    </div>
                                    <!-- end card -->
                                </div>

                                <div class="tab-pane" id="edit" role="tabpanel">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="card-title mb-0">Edit Head Office</h5>
                                        </div>
                                        <div class="card-body">
                                            <div>
                                                <div class="">
                                                    <form action="{{ route('profile.update') }}" method="post">
                                                        @method('PUT')
                                                        @csrf
                                                        <div class="row">
                                                            <div class="col-xl-2">
                                                                <div>
                                                                    <h5 class="font-size-15">Name :</h5>
                                                                </div>
                                                            </div>
                                                            <div class="col-xl">
                                                                <div class="text-muted col-md-6 col-lg-3">
                                                                    <input type="text" name="user_name"
                                                                        class="form-control border"
                                                                        value="{{ old('user_name', auth()->user()->name) }}">
                                                                </div>
                                                            </div>
                                                            @error('user_name')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                </div>

                                                <div class="py-3">
                                                    <div class="row">
                                                        <div class="col-xl-2">
                                                            <div>
                                                                <h5 class="font-size-15">Email :</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl">
                                                            <div class="text-muted col-md-6 col-lg-3">
                                                                <input type="text" name="user_email"
                                                                    class="form-control border"
                                                                    value="{{ old('user_email', auth()->user()->email) }}">
                                                            </div>
                                                            @error('user_email')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="pb-3">
                                                    <div class="row">
                                                        <div class="col-xl-2">
                                                            <div>
                                                                <h5 class="font-size-15">New Password :</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl">
                                                            <div class="text-muted col-md-6 col-lg-3">
                                                                <input type="password" class="form-control border"
                                                                    name="newPassword">
                                                            </div>
                                                            @error('newPassword')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="pb-3">
                                                    <div class="row">
                                                        <div class="col-xl-2">
                                                            <div>
                                                                <h5 class="font-size-15">Old Password <span
                                                                        class="text-danger">*</span>:</h5>
                                                            </div>
                                                        </div>
                                                        <div class="col-xl">
                                                            <div class="text-muted col-md-6 col-lg-3">
                                                                <input type="password" class="form-control border"
                                                                    name="oldPassword">
                                                            </div>
                                                            @error('oldPassword')
                                                                <span class="text-danger">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="pb-3">
                                                    <div class="row">
                                                        <div class="col-xl-2">

                                                        </div>
                                                        <div class="col-xl">
                                                            <div
                                                                class="text-muted col-md-6 col-lg-3 d-flex justify-content-end">
                                                                <button type="button" class="btn btn-sm btn-light me-2"
                                                                    onclick="history.back();">Cancel</button>
                                                                <button type="submit"
                                                                    class="btn btn-sm btn-light">Update</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end card body -->
                                </div>
                                <!-- end card -->
                            </div>
                            <!-- end col -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
