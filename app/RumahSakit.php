<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RumahSakit extends Model
{
    protected $table="rumah_sakit";

    protected $fillable=['status_briging_satusehat','nama_rumah_sakit','tanggal_pengiriman_data','kelas','alamat','email','lokasi','organisasi_id','id','kota_kab','kode_rs','jumlah_pengiriman_data'];
}
