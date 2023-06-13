<?php

namespace App\Http\Controllers;

use App\Models\DetailKriteria;
use App\Models\Kriteria;
use App\Models\Pembagi;
use App\Models\Perankingan;
use App\Models\Perhitungan;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KriteriaController extends Controller
{
    public function show()
    {
        $data = Kriteria::get();

        return view('pages.kriteria.kriteria', ['data' => $data]);
    }

    public function formCreate()
    {
        return view('pages.kriteria.add-kriteria');
    }

    public function create(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'bobot' => 'required|numeric',
            'sifat' => 'required',
        ]);

        $getKriteria = Kriteria::orderBy('id', 'desc')->first();

        if ($getKriteria) {

            $lastIncreament = substr($getKriteria->kode, -3);
            $newKode = 'KRT' . str_pad($lastIncreament + 1, 3, 0, STR_PAD_LEFT);
        } else {

            $newKode = 'KRT001';
        }

        Kriteria::create([
            'kode' => $newKode,
            'nama' => $request->nama,
            'bobot' => $request->bobot,
            'sifat' => $request->sifat
        ]);

        return redirect('/kriteria');
    }

    public function delete($id)
    {
        DetailKriteria::where('kriteria_id', $id)->delete();

        Kriteria::where('id', $id)->delete();

        return redirect('/kriteria');
    }

    public function edit($id)
    {
        $data = Kriteria::where('id', $id)->first();

        return view('pages.kriteria.edit-kriteria', ['data' => $data]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $data = Kriteria::where('id', $id)->first();

            $data->nama = $request->nama;
            $data->bobot = $request->bobot;
            $data->sifat = $request->sifat;
            $data->save();

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

            return view('pages.kriteria.kriteria');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $ex;
        }
    }

    public function detail($id)
    {
        $data = Kriteria::where('id', $id)->first();

        $detail = DetailKriteria::where('kriteria_id', $id)->get();

        return view('pages.kriteria.detail-kriteria', ['data' => $data, 'detail' => $detail]);
    }

    public function addDetail(Request $request)
    {
        $request->validate([
            'poin' => 'required|numeric',
            'keterangan' => 'required',
        ]);

        DetailKriteria::create([
            'kriteria_id' => $request->kriteria_id,
            'poin' => $request->poin,
            'keterangan' => $request->keterangan,
            'poin_optional' => $request->poin_optional,
            'data_optional' => $request->data_optional
        ]);

        return redirect()->back();
    }

    public function editDetail($id)
    {
        $kriteria = DetailKriteria::where('id', $id)->first();

        $data = Kriteria::where('id', $kriteria->kriteria_id)->first();

        $detail = DetailKriteria::where('kriteria_id', $kriteria->kriteria_id)->get();

        return view('pages.kriteria.edit-detail-kriteria', ['data' => $data, 'detail' => $detail, 'kriteria' => $kriteria]);
    }

    public function updateDetail(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $data = DetailKriteria::where('id', $id)->first();

            $data->poin = $request->poin;
            $data->keterangan = $request->keterangan;
            $data->poin_optional = $request->poin_optional;
            $data->data_optional = $request->data_optional;
            $data->save();

            if ($request->poin || $request->poin_optional) {

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
            }

            DB::commit();


            return redirect()->route('kriteria.detail', $data->kriteria_id);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $ex;
        }
    }

    public function deleteDetail($id)
    {
        DB::beginTransaction();

        try {

            $detail = DetailKriteria::where('id', $id)->first();

            $kriteria = Kriteria::where('id', $detail->kriteria_id)->first();

            $checkData = Plan::join('detail_kriterias as dk', 'dk.id', str_replace(array(' ', '/'), '_', strtolower($kriteria->nama)))
                ->where(str_replace(array(' ', '/'), '_', strtolower($kriteria->nama)), $id)
                ->update([str_replace(array(' ', '/'), '_', strtolower($kriteria->nama)) => null]);

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

            $detail->delete();

            DB::commit();

            return redirect()->back();
        } catch (\Exception $ex) {
            DB::rollBack();
            return $ex;
        }
    }
}
