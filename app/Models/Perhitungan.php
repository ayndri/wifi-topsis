<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perhitungan extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'kriteria_id',
        'nilai_matriks_ternormalisasi',
        'nilai_ternormalisasi_terbobot',
    ];
}
