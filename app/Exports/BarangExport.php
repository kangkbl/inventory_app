<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BarangExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithDrawings, WithEvents
{
    /**
     * @var \Illuminate\Support\Collection<int, \App\Models\Barang>
     */
    protected Collection $items;

    protected int $rowNumber = 0;

    /**
     * @var list<array{row:int,path:string}>
     */
    protected array $photoDrawings = [];

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
            'Foto',
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

        $sheetRow = $this->rowNumber + 1; // account for headings row

        $photoCellValue = $this->queuePhotoDrawing($barang->photo_path, $sheetRow)
            ? null
            : ($barang->photo_url ?? '-');

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
            $barang->photo_url ?? '-',
            $photoCellValue,
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

    public function drawings(): array
    {
        $drawings = [];

        foreach ($this->photoDrawings as $index => $photo) {
            $drawing = new Drawing();
            $drawing->setName('Foto Barang '.($index + 1));
            $drawing->setDescription('Foto Barang');
            $drawing->setPath($photo['path']);
            $drawing->setHeight(80);
            $drawing->setCoordinates('K'.$photo['row']);
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);

            $drawings[] = $drawing;
        }

        return $drawings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                if (empty($this->photoDrawings)) {
                    return;
                }

                $event->sheet->getColumnDimension('K')->setWidth(18);

                foreach ($this->photoDrawings as $photo) {
                    $event->sheet->getRowDimension($photo['row'])->setRowHeight(80);
                }
            },
        ];
    }

    protected function queuePhotoDrawing(?string $photoPath, int $sheetRow): bool
    {
        if (! $photoPath || filter_var($photoPath, FILTER_VALIDATE_URL)) {
            return false;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($photoPath)) {
            return false;
        }

        $this->photoDrawings[] = [
            'row' => $sheetRow,
            'path' => $disk->path($photoPath),
        ];

        return true;
    }
}