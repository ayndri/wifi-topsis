@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Edit Kriteria'])

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <form role="form" method="POST" action="{{ route('kriteria.update', $data->id) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            <p class="mb-0">Edit Kriteria</p>
                            <button type="submit" class="btn btn-primary btn-sm ms-auto">Save</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">Nama Kriteria</label>
                                    <input class="form-control" type="text" name="nama" value="{{$data->nama}}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">Bobot</label>
                                    <input class="form-control" type="number" name="bobot" value="{{$data->bobot}}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">Sifat Kriteria</label>
                                    <select class="form-select" name="sifat" aria-label="Default select example">
                                        <option disabled>Pilih Sifat Kriteria</option>
                                        <option  value="Benefit"  @if ($data->sifat == "Benefit") selected @endif>Benefit</option>
                                        <option value="Cost" @if ($data->sifat == "Cost") selected @endif>Cost</option>
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