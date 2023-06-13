@extends('layouts.app')

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Nilai Ternormalisasi'])
<div class="row mt-4 mx-4">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex align-items-center">
                    <h6>Nilai Ternormalisasi</h6>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama</th>
                                <th class="text-uppercase text-center text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kecepatan
                                </th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Jumlah Perangkat</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Jenis IP</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Rekomendasi Perangkat</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Jenis Layanan</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Rasio Down/Up</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($plan) > 0)
                            @foreach ($plan as $p)
                            <tr>
                                <td>
                                    <div class="d-flex px-3 py-1">
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">{{$p->name}}</h6>
                                        </div>
                                    </div>
                                </td>
                                @foreach ($data as $da)
                                @if ($p->id == $da->plan_id)
                                @if ($da->nama == 'Kecepatan')
                                <td>
                                    <p class="text-sm text-center font-weight-bold mb-0">{{$da->nilai_matriks_ternormalisasi}}</p>
                                </td>
                                @endif
                                @endif
                                @if ($p->id == $da->plan_id)
                                @if ($da->nama == 'Jumlah Perangkat')
                                <td>
                                    <p class="text-sm text-center font-weight-bold mb-0">{{$da->nilai_matriks_ternormalisasi}}</p>
                                </td>
                                @endif
                                @endif
                                @if ($p->id == $da->plan_id)
                                @if ($da->nama == 'Jenis IP')
                                <td>
                                    <p class="text-sm text-center font-weight-bold mb-0">{{$da->nilai_matriks_ternormalisasi}}</p>
                                </td>
                                @endif
                                @endif
                                @if ($p->id == $da->plan_id)
                                @if ($da->nama == 'Rekomendasi Perangkat')
                                <td>
                                    <p class="text-sm text-center font-weight-bold mb-0">{{$da->nilai_matriks_ternormalisasi}}</p>
                                </td>
                                @endif
                                @endif
                                @if ($p->id == $da->plan_id)
                                @if ($da->nama == 'Jenis Layanan')
                                <td>
                                    <p class="text-sm text-center font-weight-bold mb-0">{{$da->nilai_matriks_ternormalisasi}}</p>
                                </td>
                                @endif
                                @endif
                                @if ($p->id == $da->plan_id)
                                @if ($da->nama == 'Rasio Down/Up')
                                <td>
                                    <p class="text-sm text-center font-weight-bold mb-0">{{$da->nilai_matriks_ternormalisasi}}</p>
                                </td>
                                @endif
                                @endif
                                @endforeach
                            </tr>
                            @endforeach
                            @else
                            <tr>
                                <td>
                                    <div class="d-flex px-3 py-1">
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">Not Found</h6>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection