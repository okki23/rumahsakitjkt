<?php

namespace App\Http\Controllers;

use App\RumahSakit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

class RumahSakitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $cacheKey = "rumah_sakit_page_{$page}_per_page_{$perPage}_kelas_{$request->kelas}_kota_kab_{$request->kota_kab}";

        $rumahSakit = \Cache::remember($cacheKey, 10, function () use ($request, $perPage) {
            $query = RumahSakit::query()
                ->select(
                    'nama_rumah_sakit',
                    'organisasi_id',
                    'kelas',
                    'status_briging_satusehat',
                    'jumlah_pengiriman_data',
                    'alamat',
                    'kota_kab',
                    'email'
                )
                ->orderBy('nama_rumah_sakit');

            if ($request->has('kelas')) {
                $query->where('kelas', $request->kelas);
            }

            if ($request->has('kota_kab')) {
                $query->where('kota_kab', $request->kota_kab);
            }

            return $query->paginate($perPage);
        });

        return response()->json([
            'informasi' => 'Daftar Rumah Sakit',
            'jumlah_data' => $rumahSakit->total(),
            'halaman' => $rumahSakit->currentPage(),
            'jumlah_data_perhalaman' => $rumahSakit->perPage(),
            'hasil' => $rumahSakit->items(),
        ]);
    }

    /**
     * Sync all required data.
     */
    public function sinkronisasi()
    {
        try {
        // Dispatch jobs
            dispatch(new SyncRumahSakitJob());
            dispatch(new UpdateAlamatRumahSakitJob());
            dispatch(new UpdateTransaksiDataSatusehatJob());

            return response()->json(['message' => 'Data synchronization jobs dispatched successfully.'], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'An error occurred during synchronization.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return error response.
     *
     * @param string $message
     * @param Exception|null $exception
     * @return \Illuminate\Http\JsonResponse
     */
    private function handleErrorResponse(string $message, Exception $exception = null)
    {
        return response()->json([
            'message' => $message,
            'error' => $exception ? $exception->getMessage() : null,
        ], 500);
    }
}
