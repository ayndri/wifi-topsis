<?php

namespace App\Http\Controllers;

use App\Models\PaketData;
use App\Models\Plan;
use App\Models\Perhitungan;
use App\Models\Perankingan;
use Illuminate\Http\Request;
use App\Models\Kriteria;
use App\Models\DetailKriteria;
use App\Models\Pembagi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Redirect;

class PaketDataController extends Controller
{
    public function show()
    {
        $data = PaketData::get();

        return view('pages.paket-data', ['data' => $data]);
    }

    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        PaketData::create([
            'name' => $request->name
        ]);

        return redirect('/paket-data');
    }

    public function edit($id)
    {
        $dataPaket = PaketData::where('id', $id)->first();

        $data = PaketData::get();

        return view('pages.edit-paket-data', ['data' => $data, 'dataPaket' => $dataPaket]);
    }

    public function update(Request $request, $id)
    {
        $dataPaket = PaketData::where('id', $id)->first();

        $dataPaket->name = $request->name;
        $dataPaket->save();

        return redirect('/paket-data');
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {

            $deletePlan = Plan::where('paket_id', $id)->get();

            foreach ($deletePlan as $d => $val) {
                Perhitungan::where('plan_id', $deletePlan[$d]->id)->delete();
                Perankingan::where('plan_id', $deletePlan[$d]->id)->delete();
            }

            $deletePlan->each->delete();

            PaketData::where('id', $id)->delete();

            //Memulai Perhitungan

            $kriteria = Kriteria::get();

            $columns = Schema::getColumnListing('plans');

            foreach ($kriteria as $i => $val) {

                foreach ($columns as $c => $val) {

                    if (
                        str_replace(array(' ', '/'), '_', strtolower($kriteria[$i]->nama)) == $columns[$c]
                        && $columns[$c] != 'created_at' && $columns[$c] != 'updated_at' && $columns[$c] != 'id'
                    ) {

                        //Mencari nilai pembagi

                        if ($columns[$c] == 'rekomendasi_perangkat') {
                            $plan = Plan::join('detail_kriterias as dk', 'dk.id', $columns[$c])
                                ->select(DB::raw("(sum(pow(dk.poin_optional,2))) AS amount"))
                                ->first();
                        } else {
                            echo 'tes';
                            $plan = Plan::join('detail_kriterias as dk', 'dk.id', $columns[$c])
                                // ->select($columns[$c], 'dk.poin')
                                ->select(DB::raw("(sum(pow(dk.poin,2))) AS amount"))
                                ->first();
                        }
                    }
                }

                $pembagi = Pembagi::where('kriteria_id', $kriteria[$i]->id)->first();

                if ($pembagi) {
                    $pembagi->nilai = (float)sqrt($plan->amount);
                    $pembagi->save();
                } else {
                    Pembagi::create([
                        'kriteria_id' => $kriteria[$i]->id,
                        'nilai' => (float)sqrt($plan->amount)
                    ]);
                }

                //Mencari nilai matriks ternormalisasi

                $namaCol = str_replace(array(' ', '/'), '_', strtolower($kriteria[$i]->nama));
                $allPlan = Plan::join('detail_kriterias as dk', 'dk.id', $namaCol)->select("$namaCol", 'plans.id', 'dk.poin', 'dk.poin_optional')->get();

                foreach ($allPlan as $a => $valu) {

                    //mencari nilai ternormalisasi & nilai ternormalisasi terbobot

                    $perhitungan = Perhitungan::where('plan_id', $allPlan[$a]->id)->where('kriteria_id', $kriteria[$i]->id)->first();

                    if ($perhitungan) {

                        if ($namaCol == 'rekomendasi_perangkat') {
                            $perhitungan->nilai_matriks_ternormalisasi = (float)$allPlan[$a]->poin_optional / sqrt($plan->amount);
                            $perhitungan->nilai_ternormalisasi_terbobot = ((float)$allPlan[$a]->poin_optional / sqrt($plan->amount)) * $kriteria[$i]->bobot;
                            $perhitungan->save();
                        } else {
                            $perhitungan->nilai_matriks_ternormalisasi = (float)$allPlan[$a]->poin / sqrt($plan->amount);
                            $perhitungan->nilai_ternormalisasi_terbobot = ((float)$allPlan[$a]->poin / sqrt($plan->amount)) * $kriteria[$i]->bobot;
                            $perhitungan->save();
                        }
                    } else {

                        if ($namaCol == 'rekomendasi_perangkat') {
                            Perhitungan::create([
                                'plan_id' => $allPlan[$a]->id,
                                'kriteria_id' => $kriteria[$i]->id,
                                'nilai_matriks_ternormalisasi' => (float)$allPlan[$a]->poin_optional / sqrt($plan->amount),
                                'nilai_ternormalisasi_terbobot' => ((float)$allPlan[$a]->poin_optional / sqrt($plan->amount)) * $kriteria[$i]->bobot,
                            ]);
                        } else {
                            Perhitungan::create([
                                'plan_id' => $allPlan[$a]->id,
                                'kriteria_id' => $kriteria[$i]->id,
                                'nilai_matriks_ternormalisasi' => (float)$allPlan[$a]->poin / sqrt($plan->amount),
                                'nilai_ternormalisasi_terbobot' => ((float)$allPlan[$a]->poin / sqrt($plan->amount)) * $kriteria[$i]->bobot,
                            ]);
                        }
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

                $nilaiPositif = (float)0;
                $nilaiNegatif = (float)0;


                foreach ($kriteria as $i => $val) {
                    $nilaiMin = (float)Perhitungan::where('kriteria_id', $kriteria[$i]->id)->min('nilai_ternormalisasi_terbobot');
                    $nilaiMax = (float)Perhitungan::where('kriteria_id', $kriteria[$i]->id)->max('nilai_ternormalisasi_terbobot');

                    $pos = Perhitungan::where('plan_id', $allPlan[$a]->id)->where('kriteria_id', $kriteria[$i]->id)->select(DB::raw("(pow(($nilaiMax - nilai_ternormalisasi_terbobot),2)) AS nilaiPos"))->first();
                    $neg = Perhitungan::where('plan_id', $allPlan[$a]->id)->where('kriteria_id', $kriteria[$i]->id)->select(DB::raw("(pow((nilai_ternormalisasi_terbobot - $nilaiMin),2)) AS nilaiNeg"))->first();

                    $nilaiPositif += (float)$pos->nilaiPos;
                    $nilaiNegatif += (float)$neg->nilaiNeg;
                }

                $ranking = Perankingan::where('plan_id', $allPlan[$a]->id)->first();

                if ($ranking) {
                    $ranking->nilai_solusi_positif = (float)sqrt($nilaiPositif);
                    $ranking->nilai_solusi_negatif = (float)sqrt($nilaiNegatif);
                    if ((float)sqrt($nilaiNegatif) != 0) {
                        $ranking->preferensi = (float)(sqrt($nilaiNegatif)) / (sqrt($nilaiNegatif) + sqrt($nilaiPositif));
                    }
                    $ranking->save();
                } else {
                    if ((float)sqrt($nilaiNegatif) != 0) {
                        Perankingan::create([
                            'plan_id' => $allPlan[$a]->id,
                            'nilai_solusi_positif' => (float)sqrt($nilaiPositif),
                            'nilai_solusi_negatif' => (float)sqrt($nilaiNegatif),
                            'preferensi' => (float)(sqrt($nilaiNegatif)) / (sqrt($nilaiNegatif) + sqrt($nilaiPositif)),
                        ]);
                    } else {
                        Perankingan::create([
                            'plan_id' => $allPlan[$a]->id,
                            'nilai_solusi_positif' => (float)sqrt($nilaiPositif),
                            'nilai_solusi_negatif' => (float)sqrt($nilaiNegatif)
                        ]);
                    }
                }

                $ranked = Perankingan::orderBy('preferensi', 'desc')->get();

                foreach ($ranked as $r => $val) {
                    $rank = Perankingan::where('id', $ranked[$r]->id)->first();
                    $rank->perangkingan = $r + 1;
                    $rank->save();
                }
            }

            DB::commit();

            return redirect('/paket-data');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $ex;
        }
    }
}
