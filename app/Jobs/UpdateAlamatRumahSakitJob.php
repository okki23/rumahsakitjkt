<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\RumahSakit;
use Illuminate\Support\Facades\Http;
use Exception;

class UpdateAlamatRumahSakitJob implements ShouldQueue
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
        $apiUrl = 'https://dinkes.jakarta.go.id/apps/jp-2024/all-rs-terkoneksi.json';

        try {
            $response = Http::get($apiUrl);

            if (!$response->successful()) {
                throw new Exception('Failed to fetch data from API.');
            }

            $data = $response->json();
            foreach ($data as $item) {
                $rumahSakit = RumahSakit::where('nama_rumah_sakit', $item['nama'])->first();
                if ($rumahSakit) {
                    $status = $item['status'] === 'terkoneksi' ? 'sudah' : 'belum';
                    $rumahSakit->update([
                        'status_briging_satusehat' => $status,
                        'lokasi' => $item['lokasi'],
                        'alamat' => $item['alamat'],
                    ]);
                }
            }
        } catch (Exception $e) {
            // Log error
        }
    }
}
