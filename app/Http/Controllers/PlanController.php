<?php

namespace App\Http\Controllers;

use App\Models\Kriteria;
use App\Models\PaketData;
use App\Models\DetailKriteria;
use App\Models\Pembagi;
use App\Models\Perankingan;
use App\Models\Perhitungan;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlanController extends Controller
{
    public function show()
    {
        $data = Plan::join('paket_data as pd', 'pd.id', 'plans.paket_id')
            ->select('plans.id', 'pd.name as nama', 'kecepatan', 'jumlah_perangkat', 'harga', 'jenis_layanan', 'rekomendasi_perangkat')
            ->get();

        return view('pages.plan', ['data' => $data]);
    }

    public function createForm()
    {
        $data = PaketData::get();
        
        $kriteria = Kriteria::get();

        $detailKriteria = DetailKriteria::get();

        $kecepatan = Kriteria::join('detail_kriterias as dk', 'dk.kriteria_id', 'kriterias.id')
            ->where('kriterias.nama', 'Kecepatan')->get();

        return view('pages.add-plan', [
            'data' => $data, 'kriteria' => $kriteria, 'detailKriteria' => $detailKriteria, 'kecepatan' => $kecepatan
        ]);
    }

    public function create(Request $request)
    {

        DB::beginTransaction();

        try {

            // $request->validate([
            //     'paket_id' => 'required|exists:paket_data,id',
            //     'kecepatan' => 'required|numeric',
            //     'jml_perangkat' => 'required|numeric',
            //     'harga' => 'required|numeric',
            //     'jenis_layanan' => 'required|numeric',
            //     'perangkat' => 'required|numeric',
            // ]);


            Plan::create([
                'paket_id' => $request->paket_id,
                'kecepatan' => $request->kecepatan,
                'jumlah_perangkat' => $request->jumlah_perangkat,
                'harga' => $request->harga,
                'jenis_layanan' => $request->jenis_layanan,
                'rekomendasi_perangkat' => $request->rekomendasi_perangkat,
            ]);

            $kriteria = Kriteria::get();

            $columns = Schema::getColumnListing('plans');

            foreach ($kriteria as $i => $val) {
                foreach ($columns as $c => $val) {

                    if (
                        str_replace(' ', '_', strtolower($kriteria[$i]->nama)) == $columns[$c]
                        && $columns[$c] != 'created_at' && $columns[$c] != 'updated_at' && $columns[$c] != 'id'
                    ) {

                        //Mencari nilai pembagi

                        $plan = Plan::select(DB::raw("(sqrt(sum(pow($columns[$c],2)))) AS amount"))->first();

                        $pembagi = Pembagi::where('kriteria_id', $kriteria[$i]->id)->first();

                        if ($pembagi) {
                            $pembagi->nilai = (float)$plan->amount;
                            $pembagi->save();
                        } else {
                            Pembagi::create([
                                'kriteria_id' => $kriteria[$i]->id,
                                'nilai' => (float)$plan->amount
                            ]);
                        }
                    }
                }

                //Mencari nilai matriks ternormalisasi

                $namaCol = str_replace(' ', '_', strtolower($kriteria[$i]->nama));
                $allPlan = Plan::select("$namaCol", 'id')->get();

                foreach ($allPlan as $a => $valu) {

                    //mencari nilai ternormalisasi & nilai ternormalisasi terbobot

                    $perhitungan = Perhitungan::where('plan_id', $allPlan[$a]->id)->where('kriteria_id', $kriteria[$i]->id)->first();

                    if ($perhitungan) {

                        $perhitungan->nilai_matriks_ternormalisasi = (float)$allPlan[$a]->$namaCol / $plan->amount;
                        $perhitungan->nilai_ternormalisasi_terbobot = ((float)$allPlan[$a]->$namaCol / $plan->amount) * $kriteria[$i]->bobot;
                        $perhitungan->save();
                    } else {

                        Perhitungan::create([
                            'plan_id' => $allPlan[$a]->id,
                            'kriteria_id' => $kriteria[$i]->id,
                            'nilai_matriks_ternormalisasi' => (float)$allPlan[$a]->$namaCol / $plan->amount,
                            'nilai_ternormalisasi_terbobot' => ((float)$allPlan[$a]->$namaCol / $plan->amount) * $kriteria[$i]->bobot,
                        ]);
                    }

                    //Mencari nilai max dan nilai min

                    $addMaxMin = Pembagi::where('kriteria_id', $kriteria[$i]->id)->first();

                    if ($addMaxMin) {
                        $addMaxMin->nilai_min = (float)Perhitungan::where('kriteria_id', $kriteria[$i]->id)->min('nilai_ternormalisasi_terbobot');
                        $addMaxMin->nilai_max = (float)Perhitungan::where('kriteria_id', $kriteria[$i]->id)->max('nilai_ternormalisasi_terbobot');
                        $addMaxMin->save();
                    }
                }
            }

            $allPlan = Plan::get();

            foreach ($allPlan as $a => $val) {
                foreach ($kriteria as $i => $val) {
                    $nilaiMin = (float)Perhitungan::where('kriteria_id', $kriteria[$i]->id)->min('nilai_ternormalisasi_terbobot');
                    $nilaiMax = (float)Perhitungan::where('kriteria_id', $kriteria[$i]->id)->max('nilai_ternormalisasi_terbobot');

                    $solusiPositif = Perhitungan::where('plan_id', $allPlan[$a]->id)->where('kriteria_id', $kriteria[$i]->id)->select(DB::raw("(sqrt(sum(pow(($nilaiMax - nilai_ternormalisasi_terbobot),2)))) AS nilaiPos"))->first();
                    $solusiNegatif = Perhitungan::where('plan_id', $allPlan[$a]->id)->where('kriteria_id', $kriteria[$i]->id)->select(DB::raw("(sqrt(sum(pow((nilai_ternormalisasi_terbobot - $nilaiMin),2)))) AS nilaiNeg"))->first();

                    $ranking = Perankingan::where('plan_id', $allPlan[$a]->id)->first();

                    if ($ranking) {
                        $ranking->nilai_solusi_positif = (float)$solusiPositif->nilaiPos;
                        $ranking->nilai_solusi_negatif = (float)$solusiNegatif->nilaiNeg;
                        if ($solusiNegatif->nilaiNeg != 0 || $solusiNegatif->nilaiNeg != null) {
                            $ranking->preferensi = (float)$solusiNegatif->nilaiNeg / ($solusiNegatif->nilaiNeg + $solusiPositif->nilaiPos);
                        }
                        $ranking->save();
                    } else {
                        if ($solusiNegatif->nilaiNeg != 0 || $solusiNegatif->nilaiNeg != null) {
                            Perankingan::create([
                                'plan_id' => $allPlan[$a]->id,
                                'nilai_solusi_positif' => (float)$solusiPositif->nilaiPos,
                                'nilai_solusi_negatif' => (float)$solusiNegatif->nilaiNeg,
                                'preferensi' => (float)$solusiNegatif->nilaiNeg / ($solusiNegatif->nilaiNeg + $solusiPositif->nilaiPos),
                            ]);
                        } else {
                            Perankingan::create([
                                'plan_id' => $allPlan[$a]->id,
                                'nilai_solusi_positif' => (float)$solusiPositif->nilaiPos,
                                'nilai_solusi_negatif' => (float)$solusiNegatif->nilaiNeg,
                            ]);
                        }
                    }

                    $ranked = Perankingan::orderBy('preferensi', 'desc')->get();

                    $ranked->each(function ($user, $index) {
                        $ranking = Perankingan::where('plan_id', $user->id)->first();

                        if ($ranking) {
                            $ranking->perangkingan = (float)$index + 1;
                            $ranking->save();
                        }
                    });
                }
            }

            DB::commit();

            return redirect('/plan');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $ex;
        }
    }

    public function delete($id)
    {
        Plan::where('id', $id)->delete();

        return redirect('/plan');
    }

    public function edit($id)
    {
        $data = Plan::join('paket_data as pd', 'pd.id', 'plans.paket_id')
            ->select('plans.id', 'plans.paket_id', 'pd.name as nama', 'kecepatan', 'jml_perangkat', 'harga', 'jenis_layanan', 'perangkat')
            ->where('plans.id', $id)
            ->first();

        $dataPaket = PaketData::get();

        return view('pages.edit-plan', ['data' => $data, 'dataPaket' => $dataPaket]);
    }

    public function update(Request $request, $id)
    {
        $data = Plan::where('id', $id)->first();

        $data->paket_id = $request->paket_id;
        $data->kecepatan = $request->kecepatan;
        $data->jml_perangkat = $request->jml_perangkat;
        $data->harga = $request->harga;
        $data->jenis_layanan = $request->jenis_layanan;
        $data->perangkat = $request->perangkat;
        $data->save();

        return redirect('/plan');
    }

    public function nilaiTernormalisasi () 
    {
        $data = Perhitungan::join('plans', 'plans.id', 'perhitungans.plan_id')
            ->join('kriterias', 'kriterias.id', 'perhitungans.kriteria_id')
            ->get();

        return view('pages.perhitungan.nilai-ternormalisasi', ['data' => $data]);
    }

    public function nilaiTernormalisasiBobot () 
    {
        $data = Perhitungan::join('plans', 'plans.id', 'perhitungans.plan_id')
            ->join('kriterias', 'kriterias.id', 'perhitungans.kriteria_id')
            ->get();

        return view('pages.perhitungan.nilai-ternormalisasi-terbobot', ['data' => $data]);
    }
}
