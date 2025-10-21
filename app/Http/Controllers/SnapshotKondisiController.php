<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class SnapshotKondisiController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $field = Schema::hasColumn('barangs', 'kondisi') ? 'kondisi' : 'status_barang';

        $totalItems = Barang::count();
        $baik = Barang::whereRaw("LOWER($field) = 'baik'")->count();
        $rusak = Barang::whereRaw("LOWER($field) = 'rusak'")->count();
        $perbaikan = Barang::whereRaw("LOWER($field) = 'perbaikan'")->count();

        $tercatat = $baik + $rusak + $perbaikan;
        $lainnya = max($totalItems - $tercatat, 0);

        $percent = static function (int $value) use ($totalItems): int {
            return $totalItems > 0 ? (int) round(($value / $totalItems) * 100) : 0;
        };

        return response()->json([
            'total' => $totalItems,
            'counts' => [
                'baik' => $baik,
                'rusak' => $rusak,
                'perbaikan' => $perbaikan,
                'lainnya' => $lainnya,
            ],
            'percentages' => [
                'baik' => $percent($baik),
                'rusak' => $percent($rusak),
                'perbaikan' => $percent($perbaikan),
            ],
        ]);
    }
}