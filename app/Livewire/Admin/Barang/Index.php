<?php

namespace App\Livewire\Admin\Barang;

use App\Models\Barang;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
class Index extends Component
{
    use WithPagination;

    protected $paginateTheme = 'tailwind';

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

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public ?int $editingId = null;

    public array $kondisiOptions = [
        'Baik',
        'Rusak Ringan',
        'Rusak Berat',
    ];

    public string $iconPath = 'M5 2a1 1 0 0 0-1 1v1H3a1 1 0 1 0 0 2h1v1H3a1 1 0 1 0 0 2h1v1H3a1 1 0 1 0 0 2h1v1a1 1 0 0 0 1 1h1v1a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-1h1a1 1 0 0 0 1-1v-1h1a1 1 0 1 0 0-2h-1v-1h1a1 1 0 1 0 0-2h-1V6h1a1 1 0 0 0 0-2h-1V3a1 1 0 0 0-1-1H5Zm2 3h6a1 1 0 0 1 1 1v6h-1v1H7v-1H6V6a1 1 0 0 1 1-1Zm1 2v2h2V7H8Zm0 3v2h2v-2H8Zm3-3v2h2V7h-2Zm0 3v2h2v-2h-2Z';
    
    public ?string $selectedCategory = null;
    
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

        return view('livewire.admin.barang.index', [
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
        ];
    }

    public function updated($property): void
    {
        $this->validateOnly($property);
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

        Barang::create([
            'nama_barang'      => trim($validated['namaBarang']),
            'merk'             => trim($validated['merk']),
            'kode_barang_bmn'  => strtoupper(trim($validated['kodeBarangBmn'])),
            'kategori'         => trim($validated['kategori']),
            'lokasi'           => trim($validated['lokasi']),
            'kondisi'          => trim($validated['kondisi']),
            'jumlah'           => (int) $validated['jumlah'],
            'tahun_pengadaan'  => (int) $validated['tahunPengadaan'],
            'keterangan'       => $keterangan !== null && trim($keterangan) !== '' ? trim($keterangan) : null,
        ]);

        $this->cancelCreate();

        $this->dispatch('notify', body: 'Barang berhasil ditambahkan.');
        $this->dispatch('refresh-table');
        $this->dispatch('snapshot-refresh');
    }

    public function update(): void
    {
        if (! $this->editingId) {
            return;
        }

        $validated = $this->validate();

        $barang = Barang::findOrFail($this->editingId);
        $keterangan = $validated['keterangan'];

        $barang->update([
            'nama_barang'      => trim($validated['namaBarang']),
            'merk'             => trim($validated['merk']),
            'kode_barang_bmn'  => strtoupper(trim($validated['kodeBarangBmn'])),
            'kategori'         => trim($validated['kategori']),
            'lokasi'           => trim($validated['lokasi']),
            'kondisi'          => trim($validated['kondisi']),
            'jumlah'           => (int) $validated['jumlah'],
            'tahun_pengadaan'  => (int) $validated['tahunPengadaan'],
            'keterangan'       => $keterangan !== null && trim($keterangan) !== '' ? trim($keterangan) : null,
        ]);

        $this->cancelEdit();

        $this->dispatch('notify', body: 'Barang berhasil diperbarui.');
        $this->dispatch('refresh-table');
        $this->dispatch('snapshot-refresh');
    }

    public function confirmDelete(int $barangId): void
    {
        $this->dispatch('confirm-delete',
            id: $barangId,
            eventName: 'admin-barang-delete',
            payloadKey: 'barangId',
            title: 'Hapus Barang?',
            text: 'Data barang yang dihapus tidak dapat dikembalikan.',
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
        );
    }

    #[On('admin-barang-delete')]
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
        ]);

        $this->resetValidation();
    }
    
    protected function dispatchSnapshotRefresh(): void
    {
        $this->dispatch('barang-snapshot-refresh')->to('partials.snapshot-kondisi');
    }
}