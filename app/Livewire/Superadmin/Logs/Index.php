<?php

namespace App\Livewire\Superadmin\Logs;

use App\Livewire\Concerns\FormatsBarangHistory;
use App\Models\BarangHistory;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    use FormatsBarangHistory;

    protected $paginateTheme = 'tailwind';

    public string $search = '';
    public string $filterAction = '';
    public string $perPage = '10';

    public bool $showDetailModal = false;
    public array $detailLog = [];

    protected $queryString = [
        'search'       => ['except' => ''],
        'filterAction' => ['except' => ''],
        'page'         => ['except' => 1],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterAction(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $perPage = max(1, (int) $this->perPage);

        $logs = $this->baseQuery()
            ->latest()
            ->paginate($perPage);

        $actions = [
            ''         => 'Semua Aksi',
            'created'  => 'Ditambahkan',
            'updated'  => 'Diperbarui',
            'deleted'  => 'Dihapus',
        ];

        return view('livewire.superadmin.logs.index', [
            'logs'    => $logs,
            'actions' => $actions,
        ]);
    }

    public function openDetailModal(int $logId): void
    {
        $history = BarangHistory::with(['barang', 'updatedBy'])->find($logId);

        if (! $history) {
            $this->dispatch('notify', body: 'Log tidak ditemukan atau telah dihapus.');
            $this->closeDetailModal();

            return;
        }

        $this->detailLog = [
            'id'        => $history->id,
            'timestamp' => optional($history->created_at)->translatedFormat('d F Y H:i'),
            'action'    => $this->formatHistoryAction($history->action),
            'barang'    => optional($history->barang)->nama_barang ?? '-',
            'barang_id' => $history->barang_id,
            'user'      => optional($history->updatedBy)->name ?? '-',
            'changes'   => $this->formatHistoryChanges($history->changes ?? []),
        ];

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->detailLog = [];
    }

    protected function baseQuery(): Builder
    {
        return BarangHistory::query()
            ->with(['barang', 'updatedBy'])
            ->when($this->filterAction !== '', function (Builder $query) {
                $query->where('action', $this->filterAction);
            })
            ->when($this->search !== '', function (Builder $query) {
                $query->where(function (Builder $inner) {
                    $searchTerm = '%' . str_replace('%', '\\%', trim($this->search)) . '%';

                    $inner->whereHas('barang', function (Builder $barangQuery) use ($searchTerm) {
                        $barangQuery->where('nama_barang', 'like', $searchTerm)
                            ->orWhere('kode_barang_bmn', 'like', $searchTerm);
                    })
                        ->orWhereHas('updatedBy', function (Builder $userQuery) use ($searchTerm) {
                            $userQuery->where('name', 'like', $searchTerm);
                        })
                        ->orWhere('action', 'like', $searchTerm);
                });
            });
    }
}
