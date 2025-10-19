<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
class Barang extends Model
{
    //
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama_barang',
        'merk',
        'kode_barang_bmn',
        'kategori',
        'lokasi',
        'kondisi',
        'jumlah',
        'tahun_pengadaan',
        'keterangan',
    ];
}
