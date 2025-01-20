<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\RumahSakit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;


class SyncRumahSakitJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $apiUrl = 'https://dinkes.jakarta.go.id/apps/jp-2024/all-rsud.json';

        try {
            $response = Http::get($apiUrl);

            if (!$response->successful()) {
                throw new Exception('Failed to fetch data from API.');
            }

            $data = $response->json();
            foreach ($data as $item) {
                if (!RumahSakit::where('nama_rumah_sakit', $item['nama'])->exists()) {
                    RumahSakit::create([
                        'id'                => Str::uuid(),
                        'nama_rumah_sakit'  => $item['nama'],
                        'email'             => $item['email'],
                        'kelas'             => $item['kelas_rs'],
                        'organisasi_id'     => $item['organisasi_id'],
                        'kode_rs'           => $item['kode_rs'],
                        'kota_kab'          => $item['kota_kab'],
                    ]);
                }
            }
        } catch (Exception $e) {
            // Log error
        }
    }
}
