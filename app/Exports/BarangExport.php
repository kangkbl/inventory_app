<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BarangExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\Barang>
     */
    protected Collection $items;

    protected int $rowNumber = 0;

    public function __construct(Collection $items)
    {
        $this->items = $items->values();
    }

    public function collection(): Collection
    {
        return $this->items;
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Barang',
            'Merk',
            'Kode Barang BMN',
            'Kategori',
            'Lokasi',
            'Kondisi',
            'Jumlah',
            'Tahun Pengadaan',
            'Keterangan',
            'Dibuat',
            'Diperbarui',
        ];
    }

    /**
     * @param  \App\Models\Barang  $barang
     */
    public function map($barang): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $barang->nama_barang,
            $barang->merk,
            $barang->kode_barang_bmn,
            $barang->kategori,
            $barang->lokasi,
            $barang->kondisi,
            $barang->jumlah,
            $barang->tahun_pengadaan,
            $barang->keterangan ?? '-',
            optional($barang->created_at)?->format('d-m-Y H:i'),
            optional($barang->updated_at)?->format('d-m-Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}