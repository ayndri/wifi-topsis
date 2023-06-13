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
            ->select('plans.id', 'pd.name as nama', 'kecepatan', 'jumlah_perangkat', 'jenis_ip', 'jenis_layanan', 'rekomendasi_perangkat', 'rasio_down_up')
            ->get();

        $kecepatan = Plan::join('detail_kriterias as dk', 'dk.id', 'kecepatan')
            ->select('plans.id', 'dk.keterangan', 'poin')->get();

        $jumlah_perangkat = Plan::join('detail_kriterias as dk', 'dk.id', 'jumlah_perangkat')
            ->select('plans.id', 'dk.keterangan', 'poin')->get();

        $jenis_ip = Plan::join('detail_kriterias as dk', 'dk.id', 'jenis_ip')
            ->select('plans.id', 'dk.keterangan', 'poin')->get();

        $jenis_layanan = Plan::join('detail_kriterias as dk', 'dk.id', 'jenis_layanan')
            ->select('plans.id', 'dk.keterangan', 'poin')->get();

        $rekomendasi_perangkat = Plan::join('detail_kriterias as dk', 'dk.id', 'rekomendasi_perangkat')
            ->select('plans.id', 'dk.keterangan', 'dk.data_optional', 'poin_optional')->get();

        $rasio_down_up = Plan::join('detail_kriterias as dk', 'dk.id', 'rasio_down_up')
            ->select('plans.id', 'dk.keterangan', 'poin')->get();

        return view('pages.plan.plan', [
            'data' => $data, 'kecepatan' => $kecepatan,
            'jumlah_perangkat' => $jumlah_perangkat, 'jenis_ip' => $jenis_ip, 'jenis_layanan' => $jenis_layanan,
            'rekomendasi_perangkat' => $rekomendasi_perangkat, 'rasio_down_up' => $rasio_down_up
        ]);
    }

    public function createForm()
    {
        $data = PaketData::get();

        $kriteria = Kriteria::get();

        $detailKriteria = DetailKriteria::get();

        $kecepatan = Kriteria::join('detail_kriterias as dk', 'dk.kriteria_id', 'kriterias.id')
            ->where('kriterias.nama', 'Kecepatan')->get();

        return view('pages.plan.add-plan', [
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
                'jenis_ip' => $request->jenis_ip,
                'jenis_layanan' => $request->jenis_layanan,
                'rekomendasi_perangkat' => $request->rekomendasi_perangkat,
                'rasio_down_up' => $request->rasio_down_up
            ]);

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

            return redirect('/plan');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $ex;
        }
    }

    public function delete($id)
    {
        DB::beginTransaction();

        try {

            Perankingan::where('plan_id', $id)->delete();

            Perhitungan::where('plan_id', $id)->delete();

            Plan::where('id', $id)->delete();

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

            return redirect('/plan');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $ex;
        }
    }

    public function edit($id)
    {

        DB::beginTransaction();

        try {

            $data = Plan::join('paket_data as pd', 'pd.id', 'plans.paket_id')
                ->select('plans.id', 'plans.paket_id', 'pd.name as nama', 'kecepatan', 'jml_perangkat', 'harga', 'jenis_layanan', 'perangkat')
                ->where('plans.id', $id)
                ->first();

            $kecepatan = Kriteria::join('detail_kriterias as dk', 'dk.kriteria_id', 'kriterias.id')
                ->where('kriterias.nama', 'Kecepatan')->get();

            $dataPaket = PaketData::get();

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

            return view('pages.plan.edit-plan', ['data' => $data, 'dataPaket' => $dataPaket, 'kecepatan' => $kecepatan]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $ex;
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            $data = Plan::where('id', $id)->first();

            $data->paket_id = $request->paket_id;
            $data->kecepatan = $request->kecepatan;
            $data->jumlah_perangkat = $request->jumlah_perangkat;
            $data->jenis_ip = $request->jenis_ip;
            $data->jenis_layanan = $request->jenis_layanan;
            $data->rekomendasi_perangkat = $request->rekomendasi_perangkat;
            $data->rasio_down_up = $request->rasio_down_up;
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

            DB::rollBack();

            return redirect('/plan');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $ex;
        }
    }

    public function nilaiTernormalisasi()
    {
        $plan = Plan::join('paket_data as pd', 'pd.id', 'plans.paket_id')
            ->select('plans.id', 'pd.name')
            ->get();

        $data = Perhitungan::join('plans', 'plans.id', 'perhitungans.plan_id')
            ->join('kriterias', 'kriterias.id', 'perhitungans.kriteria_id')
            ->select('perhitungans.plan_id', 'kriterias.nama', 'nilai_matriks_ternormalisasi')
            ->get();

        return view('pages.perhitungan.nilai-ternormalisasi', ['plan' => $plan, 'data' => $data]);
    }

    public function nilaiTernormalisasiBobot()
    {
        $plan = Plan::join('paket_data as pd', 'pd.id', 'plans.paket_id')
            ->select('plans.id', 'pd.name')
            ->get();

        $data = Perhitungan::join('plans', 'plans.id', 'perhitungans.plan_id')
            ->join('kriterias', 'kriterias.id', 'perhitungans.kriteria_id')
            ->select('perhitungans.plan_id', 'kriterias.nama', 'nilai_ternormalisasi_terbobot')
            ->get();

        return view('pages.perhitungan.nilai-ternormalisasi-terbobot', ['plan' => $plan, 'data' => $data]);
    }

    public function perankingan()
    {
        $data = Perankingan::join('plans', 'plans.id', 'perankingans.plan_id')
            ->join('paket_data as pd', 'pd.id', 'plans.paket_id')
            ->select('pd.name', 'nilai_solusi_negatif', 'nilai_solusi_positif', 'preferensi', 'perangkingan')
            ->orderBy('perangkingan', 'ASC')
            ->get();

        return view('pages.perhitungan.perankingan', ['data' => $data]);
    }
}
