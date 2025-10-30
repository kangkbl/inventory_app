<?php

namespace App\Services;

use Illuminate\Support\Collection;

class BarangPdfExporter
{
    public function build(Collection $items): string
    {
        $pdf = new SimplePdf(width: 842.0, height: 595.0);
        $pdf->addParagraph('Laporan Data Barang', 18, 26, wrap: null);
        $pdf->addParagraph('Dibuat pada: ' . now()->format('d-m-Y H:i'), 10, 14, wrap: null);
        $pdf->addLine('', 10, 12);

        foreach ($items->values() as $index => $barang) {
            $pdf->addParagraph('No: ' . ($index + 1), 12, 18, wrap: null);
            $pdf->addParagraph('Nama Barang: ' . ($barang->nama_barang ?? '-'), 11, 14);
            $pdf->addParagraph('Merk: ' . ($barang->merk ?? '-'), 11, 14);
            $pdf->addParagraph('Kode Barang BMN: ' . ($barang->kode_barang_bmn ?? '-'), 11, 14);
            $pdf->addParagraph('Kategori: ' . ($barang->kategori ?? '-'), 11, 14);
            $pdf->addParagraph('Lokasi: ' . ($barang->lokasi ?? '-'), 11, 14);
            $pdf->addParagraph('Kondisi: ' . ($barang->kondisi ?? '-'), 11, 14);
            $pdf->addParagraph('Jumlah: ' . ($barang->jumlah ?? '-'), 11, 14, wrap: null);
            $pdf->addParagraph('Tahun Pengadaan: ' . ($barang->tahun_pengadaan ?? '-'), 11, 14, wrap: null);
            $pdf->addParagraph('Keterangan: ' . (($barang->keterangan ?? '') !== '' ? $barang->keterangan : '-'), 11, 14, wrap: 120);
            $pdf->addParagraph('Dibuat: ' . ($barang->created_at ? $barang->created_at->format('d-m-Y H:i') : '-'), 10, 14, wrap: null);
            $pdf->addParagraph('Diperbarui: ' . ($barang->updated_at ? $barang->updated_at->format('d-m-Y H:i') : '-'), 10, 14, wrap: null);
            $pdf->addLine(str_repeat('-', 120), 9, 12, 40.0);
            $pdf->addLine('', 9, 12);
        }

        return $pdf->render();
    }
}