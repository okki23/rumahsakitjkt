<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RumahSakit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\DaftarRumahSakitResource;
class RumahSakitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        $perPage    = $request->get('per_page', 10);
        $page       = $request->get('page', 1);
        $cacheKey   = "rumah_sakit_page_{$page}_per_page_{$perPage}_kelas_{$request->kelas}_kota_kab_{$request->kota_kab}";
        $rumahSakit = \Cache::remember($cacheKey, 10, function () use ($request, $perPage) {
            $query = RumahSakit::select(
                'nama_rumah_sakit',
                'organisasi_id',
                'kelas',
                'status_briging_satusehat',
                'jumlah_pengiriman_data',
                'alamat',
                'kota_kab',
                'email'
            )->orderBy('nama_rumah_sakit');

            if ($request->has('kelas')) {
                $query->where('kelas', $request->kelas);
            }

            if ($request->has('kota_kab')) {
                $query->where('kota_kab', $request->kota_kab);
            }

            return $query->paginate($perPage);
        });

        return [
            'informasi' => 'Daftar Rumah Sakit',
            'jumlah_data' => $rumahSakit->total(),
            'halaman' => $rumahSakit->currentPage(),
            'jumlah_data_perhalaman' => $rumahSakit->perPage(),
            'hasil' => $rumahSakit->items(),
        ];
    }

    // MENDAPATKAN INFORMASI RUMAH SAKIT
    public function getDataRumahSakit(){
        try {
            $apiUrl = 'https://dinkes.jakarta.go.id/apps/jp-2024/all-rsud.json';
            $response = Http::get($apiUrl);
            if ($response->successful()) {
                $data = $response->json();
                foreach ($data as $item) {
                    if (!RumahSakit::where('nama_rumah_sakit', $item['nama'])->exists()) {
                        RumahSakit::create([
                            'id'                => \Str::uuid(),
                            'nama_rumah_sakit'  => $item['nama'],
                            'email'             => $item['email'],
                            'kelas'             => $item['kelas_rs'],
                            'organisasi_id'     => $item['organisasi_id'],
                            'kode_rs'           => $item['kode_rs'],
                            'kota_kab'          => $item['kota_kab']
                        ]);
                    }
                }
                return response()->json(['message' => 'Data synced successfully.'], 200);
            }

            return response()->json(['message' => 'Failed to fetch data from API.'], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred.', 'error' => $e->getMessage()], 500);
        }
    }


    // MENDAPATKAN INFORMASI LOKASI DAN ALAMAT FASKES
    public function lengkapiDataAlamatRumahSakit(){
        try {
            $apiUrl = 'https://dinkes.jakarta.go.id/apps/jp-2024/all-rs-terkoneksi.json';
            $response = Http::get($apiUrl);
            if ($response->successful()) {
                $data = $response->json();
                foreach ($data as $item) {
                    $rumahSakit = RumahSakit::where('nama_rumah_sakit', $item['nama'])->first();
                    if($rumahSakit){
                        $rumahSakit->update(['status_briging_satusehat'=>$item['status']=='terkoneksi'?'sudah':'belum','lokasi'=>$item['lokasi'],'alamat'=>$item['alamat']]);
                    }
                }
                return response()->json(['message' => 'Data synced successfully.'], 200);
            }

            return response()->json(['message' => 'Failed to fetch data from API.'], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred.', 'error' => $e->getMessage()], 500);
        }
    }

    // MENDAPATKAN INFORMASI JUMLAH PENGIRIMAN DATA SATUSEHAT
    public function transaksiPengirimanDataKeSatusehat(){
        try {
            $apiUrl = 'https://dinkes.jakarta.go.id/apps/jp-2024/transaksi-data-satusehat.json';
            $response = Http::get($apiUrl);
            if ($response->successful()) {
                $data = $response->json();
                foreach ($data as $item) {
                    $rumahSakit = RumahSakit::where('nama_rumah_sakit', $item['nama'])->first();
                    if($rumahSakit){
                        $rumahSakit->update(['jumlah_pengiriman_data'=>$item['jumlah_pengiriman_data'],'tanggal_pengiriman_data'=>Carbon::createFromFormat('d-m-Y', $item['transaction_date'])->format('Y-m-d')]);
                    }
                }
                return response()->json(['message' => 'Data synced successfully.'], 200);
            }

            return response()->json(['message' => 'Failed to fetch data from API.'], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred.', 'error' => $e->getMessage()], 500);
        }
    }



    // MENDAPATKAN SEMUA DATA YANG DIBUTUHKAN
    public function sinkronisasi(){
        try {
            $this->transaksiPengirimanDataKeSatusehat();
            $this->getDataRumahSakit();
            $this->lengkapiDataAlamatRumahSakit();
            return response()->json(['message' => 'Data synced successfully.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 200);
        }

    }
}
