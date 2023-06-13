<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 " id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ route('home') }}" target="_blank">
            <img src="{{asset('img/logo-ct-dark.png')}}" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold">Metode Topsis Laravel</span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link {{ str_contains(request()->url(), 'dashboard') == true ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>
            @if (auth()->user()->role == 'admin' || auth()->user()->role == 'superadmin')
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Buat Data</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ str_contains(request()->url(), 'kriteria') == true ? 'active' : '' }}" href="{{ route('kriteria') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-box-2 text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Kriteria</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'paket-data' ? 'active' : '' }}" href="{{ route('paket-data') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-single-copy-04 text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Paket Data</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ str_contains(request()->url(), 'plan') == true ? 'active' : '' }}" href="{{ route('plan') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-world-2 text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Plan</span>
                </a>
            </li>
            @endif

            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Hasil Perhitungan</h6>
            </li>
            @if (auth()->user()->role == 'user')
            <li class="nav-item">
                <a class="nav-link {{ str_contains(request()->url(), 'plan') == true ? 'active' : '' }}" href="{{ route('plan') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-world-2 text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Plan</span>
                </a>
            </li>
            @endif
            <li class="nav-item">
                <a class="nav-link {{ str_contains(request()->url(), 'nilai-ternormalisasi') == true && str_contains(request()->url(), 'nilai-ternormalisasi-terbobot') == false ? 'active' : '' }}" href="{{ route('plan.normal') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-bullet-list-67 text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Nilai Ternormalisasi</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ str_contains(request()->url(), 'nilai-ternormalisasi-terbobot') == true ? 'active' : '' }}" href="{{ route('plan.bobot') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-bullet-list-67 text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Ternormalisasi Terbobot</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ str_contains(request()->url(), 'perankingan') == true ? 'active' : '' }}" href="{{ route('plan.rank') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-trophy text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Ranking</span>
                </a>
            </li>
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Akun</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ str_contains(request()->url(), 'profile') == true ? 'active' : '' }}" href="{{ route('profile') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-badge text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Profile</span>
                </a>
            </li>
            @if (auth()->user()->role == 'superadmin')
            <li class="nav-item">
                <a class="nav-link {{ str_contains(request()->url(), 'user') == true ? 'active' : '' }}" href="{{ route('user.all') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">User</span>
                </a>
            </li>
            <li class="nav-item mt-3">
                <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Settings</h6>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ str_contains(request()->url(), 'settings') == true ? 'active' : '' }}" href="{{ route('landing') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-settings-gear-65 text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Settings</span>
                </a>
            </li>
            @endif
        </ul>
    </div>

</aside>