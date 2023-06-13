@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.guest.navbar', ['title' => 'Dashboard'])
<div class="container position-sticky z-index-sticky top-0">
    <div class="row">
        <div class="col-12">

        </div>
    </div>
</div>
<main class="main-content  mt-0">
    <div class="page-header align-items-start min-vh-50 pt-5 pb-11 m-3 border-radius-lg" style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/signin-ill.jpg');
              background-size: cover;">
        <span class="mask bg-gradient-dark opacity-6"></span>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 text-center mx-auto">
                    <h1 class="text-white mb-2 mt-5">{{$land->judul}}</h1>
                    <p class="text-lead text-white">{{$land->deskripsi}}</p>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row mt-lg-n10 mt-md-n11 mt-n10 justify-content-center">
            <div class="col-xl-12 col-lg-5 col-md-7 mx-auto">
                <div class="card z-index-0">
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Ranking</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Kecepatan</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Jumlah Perangkat</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Jenis IP</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Jenis Layanan</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Rekomendasi Perangkat</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Download / Upload</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($data) > 0)
                                    @foreach ($data as $p)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-center text-sm">{{$p->name}}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-center text-sm">{{$p->perangkingan}}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        @foreach ($kecepatan as $k)
                                        @if ($p->id == $k->id)
                                        <td>
                                            <div class="px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-center text-sm">{{$k->keterangan}}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        @endif
                                        @endforeach
                                        @foreach ($jumlah_perangkat as $k)
                                        @if ($p->id == $k->id)
                                        <td>
                                            <div class="px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-center text-sm">{{$k->keterangan}}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        @endif
                                        @endforeach
                                        @foreach ($jenis_ip as $k)
                                        @if ($p->id == $k->id)
                                        <td>
                                            <div class="px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-center text-sm">{{$k->keterangan}}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        @endif
                                        @endforeach
                                        @foreach ($jenis_layanan as $k)
                                        @if ($p->id == $k->id)
                                        <td>
                                            <div class="px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-center text-sm">{{$k->keterangan}}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        @endif
                                        @endforeach
                                        @foreach ($rekomendasi_perangkat as $k)
                                        @if ($p->id == $k->id)
                                        <td>
                                            <div class="px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-center text-sm">{{$k->data_optional}}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        @endif
                                        @endforeach
                                        @foreach ($rasio_down_up as $k)
                                        @if ($p->id == $k->id)
                                        <td>
                                            <div class="px-3 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-center text-sm">{{$k->keterangan}}</h6>
                                                </div>
                                            </div>
                                        </td>
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
    </div>
</main>
@endsection