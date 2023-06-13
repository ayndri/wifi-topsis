@extends('layouts.app')

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Perankingan'])
<div class="row mt-4 mx-4">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header pb-0">
                <div class="d-flex align-items-center">
                    <h6>Perankingan</h6>
                </div>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Ranking</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Preferensi</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Solusi Ideal Negatif</th>
                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                    Solusi Ideal Positif</th>
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
                                <td>
                                    <div class="px-3 py-1">
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-center text-sm">{{$p->preferensi}}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="px-3 py-1">
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-center text-sm">{{$p->nilai_solusi_negatif}}</h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="px-3 py-1">
                                        <div class="d-flex flex-column justify-content-center">
                                            <h6 class="mb-0 text-center text-sm">{{$p->nilai_solusi_positif}}</h6>
                                        </div>
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