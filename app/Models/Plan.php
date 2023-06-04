<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'paket_id',
        'kecepatan',
        'jumlah_perangkat',
        'jenis_ip',
        'jenis_layanan',
        'rekomendasi_perangkat',
        'rasio_down_up'
    ];
}
