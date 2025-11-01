<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class BarangPdfExporter
{
    private const IMAGE_COLUMN_X = 470.0;
    private const IMAGE_MAX_WIDTH = 220.0;
    private const IMAGE_MAX_HEIGHT = 140.0;
    private const BLOCK_MIN_HEIGHT = 170.0;

    public function build(Collection $items): string
    {
        $pdf = new SimplePdf(width: 842.0, height: 595.0);
        $pdf->addParagraph('Laporan Data Barang', 18, 26, wrap: null);
        $pdf->addParagraph('Dibuat pada: ' . now()->format('d-m-Y H:i'), 10, 14, wrap: null);
        $pdf->addLine('', 10, 12);

        $disk = Storage::disk('public');
        foreach ($items->values() as $index => $barang) {
            $pdf->ensureBlockSpace(self::BLOCK_MIN_HEIGHT);

            $blockTop = $pdf->getCursorY();

            $pdf->addParagraph('No: ' . ($index + 1), 12, 18, wrap: null);
            $pdf->addParagraph('Nama Barang: ' . ($barang->nama_barang ?? '-'), 11, 14, wrap: 80);
            $pdf->addParagraph('Merk: ' . ($barang->merk ?? '-'), 11, 14, wrap: 80);
            $pdf->addParagraph('Kode Barang BMN: ' . ($barang->kode_barang_bmn ?? '-'), 11, 14, wrap: 80);
            $pdf->addParagraph('Kategori: ' . ($barang->kategori ?? '-'), 11, 14, wrap: 80);
            $pdf->addParagraph('Lokasi: ' . ($barang->lokasi ?? '-'), 11, 14, wrap: 80);
            $pdf->addParagraph('Kondisi: ' . ($barang->kondisi ?? '-'), 11, 14, wrap: 80);
            $pdf->addParagraph('Jumlah: ' . ($barang->jumlah ?? '-'), 11, 14, wrap: null);
            $pdf->addParagraph('Tahun Pengadaan: ' . ($barang->tahun_pengadaan ?? '-'), 11, 14, wrap: null);
            $pdf->addParagraph('Keterangan: ' . (($barang->keterangan ?? '') !== '' ? $barang->keterangan : '-'), 11, 14, wrap: 80);
            $pdf->addParagraph('Dibuat: ' . ($barang->created_at ? $barang->created_at->format('d-m-Y H:i') : '-'), 10, 14, wrap: null);
            $pdf->addParagraph('Diperbarui: ' . ($barang->updated_at ? $barang->updated_at->format('d-m-Y H:i') : '-'), 10, 14, wrap: null);

            $photoHeight = null;

            if ($barang->photo_path && ! filter_var($barang->photo_path, FILTER_VALIDATE_URL) && $disk->exists($barang->photo_path)) {
                $photoPath = $disk->path($barang->photo_path);
                $photoHeight = $pdf->drawImageFromPath(
                    $photoPath,
                    $blockTop,
                    self::IMAGE_COLUMN_X,
                    self::IMAGE_MAX_WIDTH,
                    self::IMAGE_MAX_HEIGHT,
                );
            }

            if ($photoHeight === null) {
                $pdf->addParagraph('Foto: ' . ($barang->photo_url ?? '-'), 10, 14, wrap: 80, x: self::IMAGE_COLUMN_X);
            }

            $textBottom = $pdf->getCursorY();
            $photoBottom = $photoHeight !== null && $photoHeight > 0.0 ? $blockTop - $photoHeight : null;
            $targetY = $photoBottom !== null ? min($textBottom, $photoBottom) : $textBottom;

            $pdf->moveCursorTo($targetY);
            $pdf->addSpacing(8);
            $pdf->addLine(str_repeat('-', 120), 9, 12, 40.0);
            $pdf->addSpacing(8);
        }

        return $pdf->render();
    }
}