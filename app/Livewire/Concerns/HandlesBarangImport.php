<?php

namespace App\Livewire\Concerns;

use App\Models\Barang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

trait HandlesBarangImport
{
    public bool $showImportModal = false;

    /**
     * @var TemporaryUploadedFile|null
     */
    public $importFile;

    public ?array $importSummary = null;

    public function openImportModal(): void
    {
        $this->resetImportState();
        $this->showImportModal = true;
    }

    public function cancelImport(): void
    {
        $this->showImportModal = false;
        $this->resetImportState();
    }

    public function updatedImportFile(): void
    {
        $this->resetErrorBag('importFile');
        $this->importSummary = null;
    }

    protected function resetImportState(): void
    {
        $this->reset(['importFile']);
        $this->importSummary = null;
        $this->resetErrorBag('importFile');
    }

    public function import(): void
    {
        $this->resetErrorBag('importFile');

        $this->validate($this->importFileRules(), $this->importFileMessages());

        $realPath = $this->importFile instanceof TemporaryUploadedFile
            ? $this->importFile->getRealPath()
            : null;

        if (! $realPath || ! is_readable($realPath)) {
            $this->addError('importFile', 'Berkas CSV tidak dapat dibaca.');

            return;
        }

        $handle = fopen($realPath, 'rb');

        if ($handle === false) {
            $this->addError('importFile', 'Berkas CSV tidak dapat dibuka.');

            return;
        }

        $requiredColumns = $this->importColumns();
        $columnIndexes = [];
        $lineNumber = 0;
        $processed = 0;
        $created = 0;
        $skipped = 0;
        $errors = [];
        $headerValid = true;

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;

                if ($lineNumber === 1) {
                    $normalizedHeader = $this->normalizeHeaderRow($row);

                    foreach ($requiredColumns as $column) {
                        $index = array_search($column, $normalizedHeader, true);

                        if ($index === false) {
                            $headerValid = false;
                            break;
                        }

                        $columnIndexes[$column] = $index;
                    }

                    if (! $headerValid) {
                        break;
                    }

                    continue;
                }

                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $processed++;

                $payload = $this->extractRowValues($row, $columnIndexes);

                $validator = Validator::make($payload, $this->importRowRules(), $this->importRowMessages());

                if ($validator->fails()) {
                    $skipped++;
                    $errors[] = 'Baris ' . $lineNumber . ': ' . implode(' ', $validator->errors()->all());

                    continue;
                }

                $data = $this->normaliseRow($validator->validated());

                $exists = Barang::query()
                    ->where('kode_barang_bmn', $data['kode_barang_bmn'])
                    ->exists();

                if ($exists) {
                    $skipped++;
                    $errors[] = 'Baris ' . $lineNumber . ': Kode Barang BMN ' . $data['kode_barang_bmn'] . ' sudah terdaftar.';

                    continue;
                }

                try {
                    DB::transaction(function () use ($data): void {
                        $barang = Barang::create([
                            'nama_barang'      => $data['nama_barang'],
                            'merk'             => $data['merk'],
                            'kode_barang_bmn'  => $data['kode_barang_bmn'],
                            'kategori'         => $data['kategori'],
                            'lokasi'           => $data['lokasi'],
                            'kondisi'          => $data['kondisi'],
                            'jumlah'           => $data['jumlah'],
                            'tahun_pengadaan'  => $data['tahun_pengadaan'],
                            'keterangan'       => $data['keterangan'],
                            'updated_by'       => Auth::id(),
                        ]);

                        $this->recordHistory($barang, 'created', $this->compileCreationChanges($barang));
                    });

                    $created++;
                } catch (\Throwable $exception) {
                    report($exception);

                    $skipped++;
                    $errors[] = 'Baris ' . $lineNumber . ': ' . $exception->getMessage();
                }
            }
        } finally {
            fclose($handle);
        }

        if (! $headerValid) {
            $this->addError('importFile', 'Format header tidak valid. Pastikan kolom: ' . implode(', ', $requiredColumns));
            $this->importSummary = null;

            return;
        }

        $this->reset('importFile');

        $this->importSummary = [
            'processed' => $processed,
            'created'   => $created,
            'skipped'   => $skipped,
            'errors'    => $errors,
        ];

        if ($created > 0) {
            $this->dispatch('refresh-table');
            $this->refreshSnapshots();
        }

        $message = "Impor selesai: {$processed} baris diproses, {$created} berhasil, {$skipped} dilewati.";

        if (! empty($errors)) {
            $message .= ' Periksa ringkasan impor untuk detail baris yang dilewati.';
        } elseif ($processed === 0) {
            $message = 'Tidak ada data baru yang ditemukan pada berkas CSV.';
        }

        $this->dispatch('notify', body: $message);
    }

    protected function importFileRules(): array
    {
        return [
            'importFile' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ];
    }

    protected function importFileMessages(): array
    {
        return [
            'importFile.required' => 'Silakan pilih berkas CSV.',
            'importFile.file'     => 'Berkas CSV tidak valid.',
            'importFile.mimes'    => 'Berkas harus berformat CSV.',
            'importFile.max'      => 'Ukuran berkas maksimal 5MB.',
        ];
    }

    protected function importColumns(): array
    {
        return [
            'nama_barang',
            'merk',
            'kode_barang_bmn',
            'kategori',
            'lokasi',
            'kondisi',
            'jumlah',
            'tahun_pengadaan',
            'keterangan',
        ];
    }

    protected function importRowRules(): array
    {
        $currentYear = (int) now()->year;

        return [
            'nama_barang'     => ['required', 'string', 'min:2'],
            'merk'            => ['required', 'string', 'min:2'],
            'kode_barang_bmn' => ['required', 'string', 'max:50'],
            'kategori'        => ['required', 'string', 'min:2'],
            'lokasi'          => ['required', 'string', 'min:2'],
            'kondisi'         => ['required', 'string', 'min:2'],
            'jumlah'          => ['required', 'integer', 'min:1'],
            'tahun_pengadaan' => ['required', 'integer', 'between:1900,' . $currentYear],
            'keterangan'      => ['nullable', 'string'],
        ];
    }

    protected function importRowMessages(): array
    {
        return [
            'nama_barang.required'     => 'Nama barang wajib diisi.',
            'nama_barang.min'          => 'Nama barang minimal 2 karakter.',
            'merk.required'            => 'Merk wajib diisi.',
            'merk.min'                 => 'Merk minimal 2 karakter.',
            'kode_barang_bmn.required' => 'Kode barang BMN wajib diisi.',
            'kode_barang_bmn.max'      => 'Kode barang BMN maksimal 50 karakter.',
            'kategori.required'        => 'Kategori wajib diisi.',
            'kategori.min'             => 'Kategori minimal 2 karakter.',
            'lokasi.required'          => 'Lokasi wajib diisi.',
            'lokasi.min'               => 'Lokasi minimal 2 karakter.',
            'kondisi.required'         => 'Kondisi wajib diisi.',
            'kondisi.min'              => 'Kondisi minimal 2 karakter.',
            'jumlah.required'          => 'Jumlah wajib diisi.',
            'jumlah.integer'           => 'Jumlah harus berupa angka.',
            'jumlah.min'               => 'Jumlah minimal 1.',
            'tahun_pengadaan.required' => 'Tahun pengadaan wajib diisi.',
            'tahun_pengadaan.integer'  => 'Tahun pengadaan harus berupa angka.',
            'tahun_pengadaan.between'  => 'Tahun pengadaan harus antara 1900 hingga tahun sekarang.',
            'keterangan.string'        => 'Keterangan harus berupa teks.',
        ];
    }

    protected function normalizeHeaderRow(array $row): array
    {
        $normalized = [];

        foreach ($row as $index => $value) {
            $value = (string) ($value ?? '');

            if ($index === 0) {
                $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
            }

            $normalized[] = strtolower(trim($value));
        }

        return $normalized;
    }

    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function extractRowValues(array $row, array $indexes): array
    {
        $payload = [];

        foreach ($indexes as $column => $index) {
            $payload[$column] = isset($row[$index]) ? trim((string) $row[$index]) : null;
        }

        return $payload;
    }

    protected function normaliseRow(array $row): array
    {
        $row['nama_barang'] = trim((string) $row['nama_barang']);
        $row['merk'] = trim((string) $row['merk']);
        $row['kode_barang_bmn'] = strtoupper(trim((string) $row['kode_barang_bmn']));
        $row['kategori'] = trim((string) $row['kategori']);
        $row['lokasi'] = trim((string) $row['lokasi']);
        $row['kondisi'] = trim((string) $row['kondisi']);
        $row['jumlah'] = (int) $row['jumlah'];
        $row['tahun_pengadaan'] = (int) $row['tahun_pengadaan'];
        $row['keterangan'] = isset($row['keterangan']) && trim((string) $row['keterangan']) !== ''
            ? trim((string) $row['keterangan'])
            : null;

        return $row;
    }

    protected function refreshSnapshots(): void
    {
        $this->dispatch('snapshot-refresh');

        if (method_exists($this, 'dispatchSnapshotRefresh')) {
            $this->dispatchSnapshotRefresh();
        }
    }
}