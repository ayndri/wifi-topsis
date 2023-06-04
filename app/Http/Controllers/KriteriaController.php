<?php

namespace App\Http\Controllers;

use App\Models\DetailKriteria;
use App\Models\Kriteria;
use Illuminate\Http\Request;

class KriteriaController extends Controller
{
    public function show()
    {
        $data = Kriteria::get();

        return view('pages.kriteria', ['data' => $data]);
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
        Kriteria::where('id', $id)->delete();

        return redirect('/kriteria');
    }

    public function edit($id)
    {
        $data = Kriteria::where('id', $id)->first();

        return view('pages.edit-kriteria', ['data' => $data]);
    }

    public function update(Request $request, $id)
    {
        $data = Kriteria::where('id', $id)->first();

        $data->nama = $request->nama;
        $data->bobot = $request->bobot;
        $data->sifat = $request->sifat;
        $data->save();

        return redirect('/kriteria');
    }

    public function detail($id)
    {
        $data = Kriteria::where('id', $id)->first();

        $detail = DetailKriteria::where('kriteria_id', $id)->get();

        return view('pages.detail-kriteria', ['data' => $data, 'detail' => $detail]);
    }

    public function addDetail (Request $request)
    {
        $request->validate([
            'poin' => 'required|numeric',
            'keterangan' => 'required',
        ]);

        DetailKriteria::create([
            'kriteria_id' => $request->kriteria_id,
            'poin' => $request->poin,
            'keterangan' => $request->keterangan,
            'data_optional' => $request->data_optional
        ]);

        return redirect()->back();
    }

}
