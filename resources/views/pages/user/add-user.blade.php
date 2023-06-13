@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Tambah User'])

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <form role="form" method="POST" action={{ route('register.new') }} enctype="multipart/form-data">
                    @csrf
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            <p class="mb-0">Tambah User</p>
                            <button type="submit" class="btn btn-primary btn-sm ms-auto">Save</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label id="name" for="example-text-input" class="form-control-label">Nama</label>
                                    <input id="name" class="form-control" type="text" name="name" value="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label id="username" for="example-text-input" class="form-control-label">Username</label>
                                    <input id="username" class="form-control" type="text" name="username" value="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label id="email" for="example-text-input" class="form-control-label">Email</label>
                                    <input id="email" class="form-control" type="email" name="email" value="">
                                    @error('email') <p class="text-danger text-xs pt-1"> {{$message}} </p>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">Role</label>
                                    <select id="role" class="form-select" name="role" aria-label="Default select example">
                                        <option disabled selected>Pilih Role</option>
                                        <option value="superadmin">Superadmin</option>
                                        <option value="admin">Admin</option>
                                        <option value="user">User</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @include('layouts.footers.auth.footer')
</div>
@endsection
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script>
    $(document).on('change', '#kecepatan', function(e) {
        e.preventDefault();
        var poin = $('#kecepatan').val();
        var rekomen = $('#kecepatan').find(':selected').data("optional");
        $('#rekom_perangkat').val(rekomen)
    });
</script>