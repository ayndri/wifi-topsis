@extends('layouts.app')

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Plan Wifi'])
<div class="row mt-4 mx-4">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex align-items-center">
                    <h6>Plan</h6>
                    <a href="{{route('plan.form')}}" class="btn btn-primary btn-sm ms-auto">Add New</a>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kecepatan
                                </th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Jumlah Perangkat</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Jenis IP</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Jenis Layanan</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Rekomendasi Perangkat</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Rasio Down/Up</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($data) > 0)
                            @foreach ($data as $d)
                            <tr>
                                <td>
                                    <div class="d-flex px-3 py-1">
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-sm">{{$d->nama}}</h6>
                                        </div>
                                    </div>
                                </td>
                                @foreach ($kecepatan as $k)
                                @if ($d->id == $k->id)
                                <td class="align-middle text-center text-sm">
                                    <p class="text-sm font-weight-bold mb-0">{{$k->poin}}</p>
                                </td>
                                @endif
                                @endforeach
                                @foreach ($jumlah_perangkat as $k)
                                @if ($d->id == $k->id)
                                <td class="align-middle text-center text-sm">
                                    <p class="text-sm font-weight-bold mb-0">{{$k->poin}}</p>
                                </td>
                                @endif
                                @endforeach
                                @foreach ($jenis_ip as $k)
                                @if ($d->id == $k->id)
                                <td class="align-middle text-center text-sm">
                                    <p class="text-sm font-weight-bold mb-0">{{$k->poin}}</p>
                                </td>
                                @endif
                                @endforeach
                                @foreach ($jenis_layanan as $k)
                                @if ($d->id == $k->id)
                                <td class="align-middle text-center text-sm">
                                    <p class="text-sm font-weight-bold mb-0">{{$k->poin}}</p>
                                </td>
                                @endif
                                @endforeach
                                @foreach ($rekomendasi_perangkat as $k)
                                @if ($d->id == $k->id)
                                <td class="align-middle text-center text-sm">
                                    <p class="text-sm font-weight-bold mb-0">{{$k->poin_optional}}</p>
                                </td>
                                @endif
                                @endforeach
                                @foreach ($rasio_down_up as $k)
                                @if ($d->id == $k->id)
                                <td class="align-middle text-center text-sm">
                                    <p class="text-sm font-weight-bold mb-0">{{$k->poin}}</p>
                                </td>
                                @endif
                                @endforeach
                                <td class="align-middle text-end">
                                    <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                        <p class="text-sm font-weight-bold mb-0"><a href="/plan/edit/{{ $d->id }}">Edit</a></p>
                                        <p class="text-sm font-weight-bold mb-0 ps-2"><a href="/plan/hapus/{{ $d->id }}">Delete</a></p>
                                    </div>
                                </td>
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