<?php

namespace App\Http\Controllers;

use App\Models\PaketData;
use App\Models\Plan;
use Illuminate\Http\Request;
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
        $deletePlan = Plan::where('paket_id', $id)->get();

        $deletePlan->each->delete();

        PaketData::where('id', $id)->delete();

        return redirect('/paket-data');

    }
}
