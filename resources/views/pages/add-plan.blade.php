@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Tambah Plan'])

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <form role="form" method="POST" action={{ route('plan.create') }} enctype="multipart/form-data">
                    @csrf
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            <p class="mb-0">Tambah Plan</p>
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
                                        @foreach ($data as $d)
                                        <option value='{{$d->id}}'>{{$d->name}}</option>
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
                                        <option data-optional="{{$k->data_optional}}" value="{{$k->poin}}">{{$k->poin}} - {{$k->keterangan}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">Rekomendasi Perangkat</label>
                                    <input id="rekom_perangkat" readonly class="form-control" type="text" name="rekomendasi_perangkat" value="">
                                </div>
                            </div>
                        </div>
                        @foreach ($kriteria as $k)
                        @if ($k->nama != 'Kecepatan' && $k->nama != 'Rekomendasi Perangkat')
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="example-text-input" class="form-control-label">{{$k->nama}}</label>
                                    <select id="{{str_replace(' ', '_', strtolower($k->nama))}}" class="form-select" name="{{str_replace(' ', '_', strtolower($k->nama))}}" aria-label="Default select example">
                                        <option selected>Pilih {{$k->nama}}</option>
                                        @foreach ($detailKriteria as $dk)
                                        @if ($dk->kriteria_id == $k->id)
                                        <option data-optional="{{$dk->data_optional}}" value="{{$dk->poin}}">{{$dk->poin}} - {{$dk->keterangan}}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        @endif
                        @endforeach
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