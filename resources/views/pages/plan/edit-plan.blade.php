@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Edit Plan'])

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <form role="form" method="POST" action="{{ route('plan.update', $data->id) }}">
                    @csrf
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            <p class="mb-0">Edit Plan</p>
                            <button type="submit" class="btn btn-primary btn-sm ms-auto">Save</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">Paket Data</label>
                                    <select class="form-select" name="paket_id" aria-label="Default select example">
                                        <option selected>Pilih paket data</option>
                                        @foreach ($dataPaket as $dp)
                                            <option <?= ($data->paket_id == $dp->id) ? 'selected' : '' ?>
                                            value='{{$dp->id}}'>{{$dp->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">Kecepatan</label>
                                    <select id="kecepatan" class="form-select" name="kecepatan" aria-label="Default select example">
                                        <option selected>Pilih Kecepatan</option>
                                        @foreach ($kecepatan as $k)
                                        <option @if ($data->kecepatan == $k->id)
                                            selected
                                        @endif data-optional="{{$k->data_optional}}" data-poin="{{$k->id}}" value="{{$k->id}}">{{$k->poin}} - {{$k->keterangan}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">Kecepatan</label>
                                    <input class="form-control" type="number" step="0.01" name="kecepatan" value='{{$data->kecepatan}}'>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">Jumlah Perangkat</label>
                                    <input class="form-control" type="number" step="0.01" name="jml_perangkat" value='{{$data->jml_perangkat}}'>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">Harga</label>
                                    <input class="form-control" type="number" step="0.01" name="harga" value='{{$data->harga}}'>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">Jenis Layanan</label>
                                    <input class="form-control" type="number" step="0.01" name="jenis_layanan" value='{{$data->jenis_layanan}}'>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">Rekomendasi Perangkat</label>
                                    <input class="form-control" type="number" step="0.01" name="perangkat" value='{{$data->perangkat}}'>
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