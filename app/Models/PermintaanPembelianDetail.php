<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermintaanPembelianDetail extends Model
{
    use HasFactory;

    protected $table = 'permintaan_pembelian_details';
    protected $primaryKey = 'id_permintaan_pembelian_detail';
    protected $guarded = [];

    public function produk()
    {
        return $this->hasOne(Produk::class, 'id_produk', 'id_produk');
    }
}
