<?php

namespace App\Livewire\Superadmin\Barang;

use App\Livewire\Concerns\HandlesBarangImport;
use App\Models\Barang;
use App\Exports\BarangExport;
use App\Services\BarangPdfExporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;
    use HandlesBarangImport;

    protected $paginateTheme = 'tailwind';
    protected const MAX_COMPRESSED_BYTES = 2097152; // 2MB

    public $paginate = '10';
    public $search = '';

    public string $namaBarang = '';
    public string $merk = '';
    public string $kodeBarangBmn = '';
    public string $kategori = '';
    public string $lokasi = '';
    public string $kondisi = '';
    public string $jumlah = '';
    public string $tahunPengadaan = '';
    public string $keterangan = '';

    public $photo;
    public ?string $photoPreviewUrl = null;


    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showDetailModal = false;
    public ?int $editingId = null;
    public array $detailBarang = [];
    public array $historyRecords = [];

    public array $kondisiOptions = [
        'Baik',
        'Perbaikan',
        'Rusak',
    ];

    public string $iconPath = 'M5 2a1 1 0 0 0-1 1v1H3a1 1 0 1 0 0 2h1v1H3a1 1 0 1 0 0 2h1v1H3a1 1 0 1 0 0 2h1v1a1 1 0 0 0 1 1h1v1a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-1h1a1 1 0 0 0 1-1v-1h1a1 1 0 1 0 0-2h-1v-1h1a1 1 0 1 0 0-2h-1V6h1a1 1 0 0 0 0-2h-1V3a1 1 0 0 0-1-1H5Zm2 3h6a1 1 0 0 1 1 1v6h-1v1H7v-1H6V6a1 1 0 0 1 1-1Zm1 2v2h2V7H8Zm0 3v2h2v-2H8Zm3-3v2h2V7h-2Zm0 3v2h2v-2h-2Z';
    
    public ?string $selectedCategory = null;
    
    
    protected array $fieldLabels = [
        'nama_barang'     => 'Nama Barang',
        'merk'            => 'Merk',
        'kode_barang_bmn' => 'Kode Barang BMN',
        'kategori'        => 'Kategori',
        'lokasi'          => 'Lokasi',
        'kondisi'         => 'Kondisi',
        'jumlah'          => 'Jumlah',
        'tahun_pengadaan' => 'Tahun Pengadaan',
        'keterangan'      => 'Keterangan',
        'photo_path'      => 'Foto Barang',
    ];
    
    public function render()
    {
        $query = $this->baseQuery();

        $barang = (clone $query)
            ->orderBy('nama_barang', 'ASC')
            ->paginate($this->paginate);

        $previewItems = $this->selectedCategory
            ? Barang::query()
                ->where('kategori', $this->selectedCategory)
                ->orderByDesc('updated_at')
                ->take(4)
                ->get()
            : collect();

        $categories = Barang::select('kategori')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('kategori')
            ->orderBy('kategori')
            ->get();

        return view('livewire.superadmin.barang.index', [
            'title'            => 'Item Management',
            'addbarang'        => 'Tambahkan Barang',
            'iconPath'         => $this->iconPath,
            'barang'           => $barang,
            'categories'       => $categories,
            'previewItems'     => $previewItems,
            'selectedCategory' => $this->selectedCategory,
        ]);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }


public function updatedPaginate(): void
    {
        $this->resetPage();
    }

    public function selectCategory(?string $category = null): void
    {
        $normalized = $category !== null ? trim($category) : null;

        if ($normalized === '') {
            $normalized = null;
        }

        $this->selectedCategory = $this->selectedCategory === $normalized ? null : $normalized;
        $this->resetPage();
    }

    protected function baseQuery(): Builder
    {
        $query = Barang::query();

        $term = trim($this->search);

        if ($term !== '') {
            $query->where(function (Builder $builder) use ($term) {
                $builder
                    ->where('nama_barang', 'like', "%{$term}%")
                    ->orWhere('merk', 'like', "%{$term}%")
                    ->orWhere('kategori', 'like', "%{$term}%");
            });
        }

        if ($this->selectedCategory) {
            $query->where('kategori', $this->selectedCategory);
        }

        return $query;
    }

    public function export(string $format)
    {
        $format = strtolower(trim($format));

        $items = $this->baseQuery()
            ->orderBy('nama_barang')
            ->get($this->exportColumns());

        if ($items->isEmpty()) {
            $this->dispatch('notify', body: 'Tidak ada data barang untuk diekspor.');

            return null;
        }

        $fileName = 'data-barang_' . now()->format('Ymd_His');

        if ($format === 'excel') {
            return Excel::download(new BarangExport($items), $fileName . '.xlsx');
        }

        if ($format === 'pdf') {
            $exporter = new BarangPdfExporter();
            $pdfContent = $exporter->build($items);

            return response()->streamDownload(
                static function () use ($pdfContent): void {
                    echo $pdfContent;
                },
                $fileName . '.pdf',
                ['Content-Type' => 'application/pdf']
            );
        }

        $this->dispatch('notify', body: 'Format export tidak dikenali.');

        return null;
    }

    protected function exportColumns(): array
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
            'photo_path',
            'created_at',
            'updated_at',
        ];
    }
    
    protected function rules(): array
    {
        $currentYear = (int) now()->year;

        $kodeRule = Rule::unique('barangs', 'kode_barang_bmn');

        if ($this->showEditModal && $this->editingId) {
            $kodeRule = $kodeRule->ignore($this->editingId);
        }

        return [
            'namaBarang'      => ['required', 'string', 'min:2'],
            'merk'            => ['required', 'string', 'min:2'],
            'kodeBarangBmn'   => ['required', 'string', 'max:50', $kodeRule],
            'kategori'        => ['required', 'string', 'min:2'],
            'lokasi'          => ['required', 'string', 'min:2'],
            'kondisi'         => ['required', 'string', 'in:' . implode(',', $this->kondisiOptions)],
            'jumlah'          => ['required', 'integer', 'min:1'],
            'tahunPengadaan'  => ['required', 'integer', 'between:1900,' . $currentYear],
            'keterangan'      => ['nullable', 'string', 'max:500'],
            'photo'           => ['nullable', 'image'],
        ];
    }

    protected function messages(): array
    {
        return [
            'namaBarang.required'     => 'Nama barang wajib diisi.',
            'namaBarang.min'          => 'Nama barang minimal 2 karakter.',
            'merk.required'           => 'Merk wajib diisi.',
            'merk.min'                => 'Merk minimal 2 karakter.',
            'kodeBarangBmn.required'  => 'Kode barang BMN wajib diisi.',
            'kodeBarangBmn.unique'    => 'Kode barang BMN sudah terdaftar.',
            'kategori.required'       => 'Kategori wajib diisi.',
            'lokasi.required'         => 'Lokasi wajib diisi.',
            'kondisi.required'        => 'Kondisi wajib dipilih.',
            'kondisi.in'              => 'Pilih kondisi yang tersedia.',
            'jumlah.required'         => 'Jumlah wajib diisi.',
            'jumlah.integer'          => 'Jumlah harus berupa angka.',
            'jumlah.min'              => 'Jumlah minimal 1.',
            'tahunPengadaan.required' => 'Tahun pengadaan wajib diisi.',
            'tahunPengadaan.integer'  => 'Tahun pengadaan harus berupa angka.',
            'tahunPengadaan.between'  => 'Tahun pengadaan harus antara 1900 hingga tahun sekarang.',
            'keterangan.max'          => 'Keterangan maksimal 500 karakter.',
            'photo.image'             => 'Foto barang harus berupa file gambar.',
        ];
    }

    public function updated($property): void
    {
        $this->validateOnly($property);
    }

    public function updatedPhoto(): void
    {
        $this->validateOnly('photo');
        $this->photoPreviewUrl = $this->photo ? $this->photo->temporaryUrl() : null;
    }

    public function getCanSaveProperty(): bool
    {
        return trim($this->namaBarang) !== ''
            && trim($this->merk) !== ''
            && trim($this->kodeBarangBmn) !== ''
            && trim($this->kategori) !== ''
            && trim($this->lokasi) !== ''
            && trim($this->kondisi) !== ''
            && trim($this->jumlah) !== ''
            && trim($this->tahunPengadaan) !== ''
            && $this->getErrorBag()->isEmpty();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function cancelCreate(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function openEditModal(int $barangId): void
    {
        $barang = Barang::findOrFail($barangId);

        $this->editingId = $barang->id;
        $this->namaBarang = $barang->nama_barang;
        $this->merk = $barang->merk;
        $this->kodeBarangBmn = $barang->kode_barang_bmn;
        $this->kategori = $barang->kategori;
        $this->lokasi = $barang->lokasi;
        $this->kondisi = $barang->kondisi;
        $this->jumlah = (string) $barang->jumlah;
        $this->tahunPengadaan = (string) $barang->tahun_pengadaan;
        $this->keterangan = $barang->keterangan ?? '';
        $this->photoPreviewUrl = $barang->photo_url ?? $this->generateFallbackImage($barang->nama_barang, $barang->merk);

        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function cancelEdit(): void
    {
        $this->showEditModal = false;
        $this->editingId = null;
        $this->resetForm();
    }

    public function store(): void
    {
        $validated = $this->validate();

        $keterangan = $validated['keterangan'];
        $photoPath = $this->resolvePhotoPath(null, trim($validated['namaBarang']), trim($validated['merk']));

        $barang = Barang::create([
            'nama_barang'      => trim($validated['namaBarang']),
            'merk'             => trim($validated['merk']),
            'kode_barang_bmn'  => strtoupper(trim($validated['kodeBarangBmn'])),
            'kategori'         => trim($validated['kategori']),
            'lokasi'           => trim($validated['lokasi']),
            'kondisi'          => trim($validated['kondisi']),
            'jumlah'           => (int) $validated['jumlah'],
            'tahun_pengadaan'  => (int) $validated['tahunPengadaan'],
            'keterangan'       => $keterangan !== null && trim($keterangan) !== '' ? trim($keterangan) : null,
            'photo_path'       => $photoPath,
            'updated_by'       => Auth::id(),
        ]);

        $this->recordHistory($barang, 'created', $this->compileCreationChanges($barang));

        $this->cancelCreate();

        $this->dispatch('notify', body: 'Barang berhasil ditambahkan.');
        $this->dispatch('refresh-table');
        $this->dispatch('snapshot-refresh');
    }

    public function openDetailModal(int $barangId): void
    {
        $barang = Barang::with('updatedBy')->findOrFail($barangId);

        $this->detailBarang = [
            'nama_barang'     => $barang->nama_barang,
            'merk'            => $barang->merk,
            'kode_barang_bmn' => $barang->kode_barang_bmn,
            'kategori'        => $barang->kategori,
            'lokasi'          => $barang->lokasi,
            'kondisi'         => $barang->kondisi,
            'jumlah'          => (string) $barang->jumlah,
            'tahun_pengadaan' => (string) $barang->tahun_pengadaan,
            'keterangan'      => $barang->keterangan ?? '-',
            'created_at'      => optional($barang->created_at)->translatedFormat('d F Y H:i'),
            'updated_at'      => optional($barang->updated_at)->translatedFormat('d F Y H:i'),
            'updated_by'      => optional($barang->updatedBy)->name,
            'photo_url'       => $barang->photo_url ?? $this->generateFallbackImage($barang->nama_barang, $barang->merk),
        ];
        
        $this->historyRecords = $this->formatHistoryRecords(
            $barang->histories()->with('updatedBy')->latest()->get()
        );

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailBarang = [];
        $this->historyRecords = [];
    }

    public function update(): void
    {
        if (! $this->editingId) {
            return;
        }

        $validated = $this->validate();

        $barang = Barang::findOrFail($this->editingId);
        $keterangan = $validated['keterangan'];
        $photoPath = $this->resolvePhotoPath($barang, trim($validated['namaBarang']), trim($validated['merk']));

        $original = $barang->getOriginal();

        $barang->fill([
            'nama_barang'      => trim($validated['namaBarang']),
            'merk'             => trim($validated['merk']),
            'kode_barang_bmn'  => strtoupper(trim($validated['kodeBarangBmn'])),
            'kategori'         => trim($validated['kategori']),
            'lokasi'           => trim($validated['lokasi']),
            'kondisi'          => trim($validated['kondisi']),
            'jumlah'           => (int) $validated['jumlah'],
            'tahun_pengadaan'  => (int) $validated['tahunPengadaan'],
            'keterangan'       => $keterangan !== null && trim($keterangan) !== '' ? trim($keterangan) : null,
            'photo_path'       => $photoPath,
            'updated_by'       => Auth::id(),
        ]);
        
        $dirty = $barang->getDirty();

        $barang->save();

        $changes = $this->compileDirtyChanges($dirty, $original);

        if (! empty($changes)) {
            $this->recordHistory($barang, 'updated', $changes);
        }

        $this->cancelEdit();

        $this->dispatch('notify', body: 'Barang berhasil diperbarui.');
        $this->dispatch('refresh-table');
        $this->dispatch('snapshot-refresh');
    }

    public function confirmDelete(int $barangId): void
    {
        $this->dispatch('confirm-delete',
            id: $barangId,
            eventName: 'superadmin-barang-delete',
            payloadKey: 'barangId',
            title: 'Hapus Barang?',
            text: 'Data barang yang dihapus tidak dapat dikembalikan.',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
        );
    }

    #[On('superadmin-barang-delete')]
    public function delete(int $barangId): void
    {
        $barang = Barang::findOrFail($barangId);

        if ($this->editingId === $barangId) {
            $this->cancelEdit();
        }

        $barang->delete();

        $this->dispatch('notify', body: 'Barang berhasil dihapus.');
        $this->dispatch('refresh-table');
        $this->dispatch('snapshot-refresh');
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset([
            'namaBarang',
            'merk',
            'kodeBarangBmn',
            'kategori',
            'lokasi',
            'kondisi',
            'jumlah',
            'tahunPengadaan',
            'keterangan',
            'photo',
            'photoPreviewUrl',
        ]);

        $this->resetValidation();
    }
    
    protected function dispatchSnapshotRefresh(): void
    {
        $this->dispatch('barang-snapshot-refresh')->to('partials.snapshot-kondisi');
    }

    protected function trackedFields(): array
    {
        return array_keys($this->fieldLabels);
    }

    protected function compileCreationChanges(Barang $barang): array
    {
        $changes = [];

        foreach ($this->trackedFields() as $field) {
            $changes[$field] = [
                'old' => null,
                'new' => $barang->{$field},
            ];
        }

        return $changes;
    }

    protected function compileDirtyChanges(array $dirty, array $original): array
    {
        $changes = [];

        foreach ($dirty as $field => $newValue) {
            if (! in_array($field, $this->trackedFields(), true)) {
                continue;
            }

            $changes[$field] = [
                'old' => $original[$field] ?? null,
                'new' => $newValue,
            ];
        }

        return $changes;
    }

    protected function recordHistory(Barang $barang, string $action, array $changes): void
    {
        if (empty($changes)) {
            return;
        }

        $barang->histories()->create([
            'updated_by' => Auth::id(),
            'action'     => $action,
            'changes'    => $changes,
        ]);
    }

    protected function formatHistoryRecords($histories): array
    {
        return $histories->map(function ($history) {
            return [
                'id'        => $history->id,
                'action'    => $this->formatHistoryAction($history->action),
                'timestamp' => optional($history->created_at)->translatedFormat('d F Y H:i'),
                'user'      => optional($history->updatedBy)->name,
                'changes'   => $this->formatHistoryChanges($history->changes ?? []),
            ];
        })->toArray();
    }

    protected function formatHistoryAction(string $action): string
    {
        return match ($action) {
            'created' => 'Ditambahkan',
            'updated' => 'Diperbarui',
            default   => ucfirst($action),
        };
    }

    protected function formatHistoryChanges(array $changes): array
    {
        $formatted = [];

        foreach ($changes as $field => $value) {
            if (! array_key_exists($field, $this->fieldLabels)) {
                continue;
            }

            $formatted[] = [
                'label' => $this->fieldLabels[$field],
                'old'   => $this->formatHistoryValue($value['old'] ?? null),
                'new'   => $this->formatHistoryValue($value['new'] ?? null),
            ];
        }

        return $formatted;
    }

    protected function formatHistoryValue($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return (string) $value;
    }
    protected function resolvePhotoPath(?Barang $existing, string $namaBarang, string $merk): ?string
    {
        if ($this->photo instanceof TemporaryUploadedFile) {
            $storedPath = $this->storeCompressedPhoto($this->photo);

            if ($existing && $existing->photo_path && $this->isStoredLocally($existing->photo_path)) {
                Storage::disk('public')->delete($existing->photo_path);
            }

            return $storedPath;
        }

        if ($existing && $existing->photo_path) {
            return $existing->photo_path;
        }

        return $this->generateFallbackImage($namaBarang, $merk);
    }

    protected function storeCompressedPhoto(TemporaryUploadedFile $photo): string
    {
        $raw = @file_get_contents($photo->getRealPath());

        if ($raw === false) {
            return $photo->store('barang-photos', 'public');
        }

        $resource = @imagecreatefromstring($raw);

        if (! $resource instanceof \GdImage) {
            return $photo->store('barang-photos', 'public');
        }

        $resource = $this->forceTrueColor($resource);

        [$encoded, $finalResource] = $this->encodeWithinLimit($resource, self::MAX_COMPRESSED_BYTES);

        $filename = 'barang-photos/' . Str::uuid() . '.jpg';
        Storage::disk('public')->put($filename, $encoded);

        imagedestroy($finalResource);

        return $filename;
    }

    protected function forceTrueColor(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $trueColor = imagecreatetruecolor($width, $height);
        $background = imagecolorallocate($trueColor, 255, 255, 255);
        imagefill($trueColor, 0, 0, $background);
        imagecopyresampled($trueColor, $image, 0, 0, 0, 0, $width, $height, $width, $height);

        imagedestroy($image);

        return $trueColor;
    }

    protected function encodeWithinLimit(\GdImage $image, int $maxBytes): array
    {
        $quality = 85;
        $minimumQuality = 45;
        $data = $this->encodeJpeg($image, $quality);

        while (strlen($data) > $maxBytes) {
            if ($quality > $minimumQuality) {
                $quality = max($minimumQuality, $quality - 5);
            } else {
                $image = $this->resizeImage($image, 0.9);
                $quality = 80;
            }

            $data = $this->encodeJpeg($image, $quality);

            if ($quality <= $minimumQuality && imagesx($image) <= 320 && imagesy($image) <= 320) {
                break;
            }
        }

        return [$data, $image];
    }

    protected function encodeJpeg(\GdImage $image, int $quality): string
    {
        ob_start();
        imagejpeg($image, null, $quality);

        return (string) ob_get_clean();
    }

    protected function resizeImage(\GdImage $image, float $ratio): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $newWidth = max(1, (int) round($width * $ratio));
        $newHeight = max(1, (int) round($height * $ratio));

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        $background = imagecolorallocate($resized, 255, 255, 255);
        imagefill($resized, 0, 0, $background);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        imagedestroy($image);

        return $resized;
    }

    protected function generateFallbackImage(?string $namaBarang, ?string $merk): string
    {
        $parts = array_filter([
            $namaBarang ? trim($namaBarang) : null,
            $merk ? trim($merk) : null,
        ]);

        if (empty($parts)) {
            return 'https://source.unsplash.com/featured/?inventory';
        }

        $query = rawurlencode(implode(' ', $parts));

        return "https://source.unsplash.com/featured/?{$query}";
    }

    protected function isStoredLocally(string $path): bool
    {
        return ! Str::startsWith($path, ['http://', 'https://']);
    }
}