<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembagi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'kriteria_id',
        'nilai',
        'nilai_max',
        'nilai_min'
    ];
}
