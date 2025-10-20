<?php

namespace App\Livewire\Superadmin\Kategori;


use App\Models\Kategori;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $paginateTheme = 'tailwind';

    public $paginate = '10';
    public $search = '';

    public string $namaKategori = '';
    public string $deskripsi = '';

    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public ?int $editingId = null;

    public string $iconPath = "M15 1.943v12.114a1 1 0 0 1-1.581.814L8 11V5l5.419-3.871A1 1 0 0 1 15 1.943ZM7 4H2a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2v5a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2V4ZM4 17v-5h1v5H4ZM16 5.183v5.634a2.984 2.984 0 0 0 0-5.634Z";

    public function render()
    {
        $categories = Kategori::query()
            ->when($this->search !== '', function ($query) {
                $query->where('nama', 'like', '%' . $this->search . '%');
            })
            ->orderBy('nama')
            ->paginate($this->paginate);

        return view('livewire.superadmin.kategori.index', [
            'title'       => 'Category Management',
            'addCategory' => 'Tambah Kategori',
            'iconPath'    => $this->iconPath,
            'kategoris'   => $categories,
        ]);
    }

    protected function rules(): array
    {
        $nameRule = Rule::unique('kategoris', 'nama');

        if ($this->showEditModal && $this->editingId) {
            $nameRule = $nameRule->ignore($this->editingId);
        }

        return [
            'namaKategori' => ['required', 'string', 'min:2', $nameRule],
            'deskripsi'    => ['nullable', 'string', 'max:500'],
        ];
    }

    protected function messages(): array
    {
        return [
            'namaKategori.required' => 'Nama kategori wajib diisi.',
            'namaKategori.min'      => 'Nama kategori minimal 2 karakter.',
            'namaKategori.unique'   => 'Nama kategori sudah terdaftar.',
            'deskripsi.max'         => 'Deskripsi maksimal 500 karakter.',
        ];
    }

    public function updated($property): void
    {
        $this->validateOnly($property);
    }

    public function getCanSaveProperty(): bool
    {
        return trim($this->namaKategori) !== ''
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

    public function openEditModal(int $kategoriId): void
    {
        $kategori = Kategori::findOrFail($kategoriId);

        $this->editingId = $kategori->id;
        $this->namaKategori = $kategori->nama;
        $this->deskripsi = $kategori->deskripsi ?? '';

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

        Kategori::create([
            'nama'       => trim($validated['namaKategori']),
            'deskripsi'  => $this->prepareDescription($validated['deskripsi'] ?? null),
        ]);

        $this->cancelCreate();

        $this->dispatch('notify', body: 'Kategori berhasil ditambahkan.');
        $this->dispatch('refresh-table');
    }

    public function update(): void
    {
        if (! $this->editingId) {
            return;
        }

        $validated = $this->validate();

        $kategori = Kategori::findOrFail($this->editingId);

        $kategori->update([
            'nama'      => trim($validated['namaKategori']),
            'deskripsi' => $this->prepareDescription($validated['deskripsi'] ?? null),
        ]);

        $this->cancelEdit();

        $this->dispatch('notify', body: 'Kategori berhasil diperbarui.');
        $this->dispatch('refresh-table');
    }

    public function delete(int $kategoriId): void
    {
        $kategori = Kategori::findOrFail($kategoriId);

        if ($this->editingId === $kategoriId) {
            $this->cancelEdit();
        }

        $kategori->delete();

        $this->dispatch('notify', body: 'Kategori berhasil dihapus.');
        $this->dispatch('refresh-table');
        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset([
            'namaKategori',
            'deskripsi',
        ]);

        $this->resetValidation();
    }

    private function prepareDescription(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
