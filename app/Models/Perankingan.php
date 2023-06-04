<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perankingan extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'nilai_solusi_positif',
        'nilai_solusi_negatif',
        'preferensi',
        'perangkingan'
    ];
}
