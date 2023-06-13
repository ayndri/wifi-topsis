@extends('layouts.app')

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Kriteria'])
<div class="row mt-4 mx-4">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex align-items-center">
                    <h6>Kriteria</h6>
                    <a href="{{ route('kriteria.form') }}" class="btn btn-primary btn-sm ms-auto">Add New</a>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kode</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Nama
                                </th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Bobot</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                Sifat</th>
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
                                            <h6 class="mb-0 text-sm">{{$d->kode}}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-sm font-weight-bold mb-0">{{$d->nama}}</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                    <p class="text-sm font-weight-bold mb-0">{{$d->bobot}}</p>
                                </td>
                                <td class="align-middle text-center text-sm">
                                    <p class="text-sm font-weight-bold mb-0">{{$d->sifat}}</p>
                                </td>
                                <td class="align-middle text-end">
                                    <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                                        <p class="text-sm font-weight-bold mb-0"><a href="/kriteria/edit/{{ $d->id }}">Edit</a></p>
                                        <!-- <p class="text-sm font-weight-bold mb-0 ps-2"><a href="/kriteria/hapus/{{ $d->id }}">Delete</a></p> -->
                                        <p class="text-sm font-weight-bold mb-0 ps-2"><a href="/kriteria/detail/{{ $d->id }}">Detail</a></p>
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