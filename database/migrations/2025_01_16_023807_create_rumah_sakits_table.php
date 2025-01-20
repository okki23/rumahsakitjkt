<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRumahSakitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rumah_sakit', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_rumah_sakit');
            $table->string('kelas');
            $table->string('alamat')->nullable()->default(null);
            $table->string('email');
            $table->date('tanggal_pengiriman_data')->date();
            $table->string('lokasi')->nullable()->default(null);
            $table->string('organisasi_id');
            $table->string('kota_kab');
            $table->string('kode_rs');
            $table->string('status_briging_satusehat',50)->default(null);
            $table->integer('jumlah_pengiriman_data')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rumah_sakits');
    }
}
