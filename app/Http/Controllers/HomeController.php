<?php

namespace App\Http\Controllers;

use App\Models\Kriteria;
use App\Models\LandingPage;
use App\Models\Perankingan;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = User::where('role', 'user')->count();
        $plan = Plan::count();

        $topOne = Perankingan::join('plans', 'plans.id', 'perankingans.plan_id')
            ->join('paket_data as pd', 'pd.id', 'plans.paket_id')
            ->select('pd.name', 'nilai_solusi_negatif', 'nilai_solusi_positif', 'preferensi', 'perangkingan')
            ->first();

        $middle = Perankingan::join('plans', 'plans.id', 'perankingans.plan_id')
            ->join('paket_data as pd', 'pd.id', 'plans.paket_id')
            ->select('pd.name', 'nilai_solusi_negatif', 'nilai_solusi_positif', 'preferensi', 'perangkingan')
            ->where('perankingans.perangkingan', 1)
            ->first();

        if ($topOne) {

            $getMid = $topOne->perangkingan / 2;

            $middle = Perankingan::join('plans', 'plans.id', 'perankingans.plan_id')
                ->join('paket_data as pd', 'pd.id', 'plans.paket_id')
                ->select('pd.name', 'nilai_solusi_negatif', 'nilai_solusi_positif', 'preferensi', 'perangkingan')
                ->where('perankingans.perangkingan', $getMid)
                ->first();
        }

        $kriteria = Kriteria::get();

        $topFive = $topOne = Perankingan::join('plans', 'plans.id', 'perankingans.plan_id')
            ->join('paket_data as pd', 'pd.id', 'plans.paket_id')
            ->select('pd.name', 'nilai_solusi_negatif', 'nilai_solusi_positif', 'preferensi', 'perangkingan')
            ->orderBy('perangkingan', 'ASC')
            ->take(5)
            ->get();

        return view('pages.dashboard', ['topOne' => $topOne, 'user' => $user, 'plan' => $plan, 'middle' => $middle, 'kriteria' => $kriteria, 'topFive' => $topFive]);
    }

    public function home()
    {
        $data = Perankingan::join('plans', 'plans.id', 'perankingans.plan_id')
            ->join('paket_data as pd', 'pd.id', 'plans.paket_id')
            ->select('plans.id', 'pd.name', 'perangkingan')
            ->orderBy('perangkingan', 'ASC')
            ->take(5)
            ->get();

        $kecepatan = Plan::join('detail_kriterias as dk', 'dk.id', 'kecepatan')
            ->select('plans.id', 'dk.keterangan')->get();

        $jumlah_perangkat = Plan::join('detail_kriterias as dk', 'dk.id', 'jumlah_perangkat')
            ->select('plans.id', 'dk.keterangan')->get();

        $jenis_ip = Plan::join('detail_kriterias as dk', 'dk.id', 'jenis_ip')
            ->select('plans.id', 'dk.keterangan')->get();

        $jenis_layanan = Plan::join('detail_kriterias as dk', 'dk.id', 'jenis_layanan')
            ->select('plans.id', 'dk.keterangan')->get();

        $rekomendasi_perangkat = Plan::join('detail_kriterias as dk', 'dk.id', 'rekomendasi_perangkat')
            ->select('plans.id', 'dk.keterangan', 'dk.data_optional')->get();

        $rasio_down_up = Plan::join('detail_kriterias as dk', 'dk.id', 'rasio_down_up')
            ->select('plans.id', 'dk.keterangan')->get();

        $land = LandingPage::first();

        return view('pages.home', [
            'data' => $data, 'land' => $land, 'kecepatan' => $kecepatan,
            'jumlah_perangkat' => $jumlah_perangkat, 'jenis_ip' => $jenis_ip, 'jenis_layanan' => $jenis_layanan,
            'rekomendasi_perangkat' => $rekomendasi_perangkat, 'rasio_down_up' => $rasio_down_up
        ]);
    }

    public function formLanding()
    {
        $data = LandingPage::first();
        return view('pages.landing.landing', ['data' => $data]);
    }

    public function updateLanding(Request $request)
    {
        $data = LandingPage::first();
        $data->judul = $request->judul;
        $data->deskripsi = $request->deskripsi;
        $data->save();

        return redirect()->back();
    }
}
