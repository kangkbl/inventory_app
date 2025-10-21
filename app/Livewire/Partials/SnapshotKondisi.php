<?php

namespace App\Livewire\Partials;

use App\Models\Barang;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\On;
use Livewire\Component;

class SnapshotKondisi extends Component
{
    public int $totalItems = 0;
    public int $baik = 0;
    public int $rusak = 0;
    public int $perbaikan = 0;
    public int $lainnya = 0;

    protected string $field = 'kondisi';

    public function mount(): void
    {
        $this->refreshCounts();
    }

    #[On('barang-snapshot-refresh')]
    public function refreshCounts(): void
    {
        if (! class_exists(Barang::class) || ! Schema::hasTable('barangs')) {
            $this->resetCounts();

            return;
        }

        $this->field = Schema::hasColumn('barangs', 'kondisi') ? 'kondisi' : 'status_barang';

        $this->totalItems = Barang::count();
        $this->baik = $this->countByValue('baik');
        $this->rusak = $this->countByValue('rusak');
        $this->perbaikan = $this->countByValue('perbaikan');
        $this->lainnya = max($this->totalItems - ($this->baik + $this->rusak + $this->perbaikan), 0);
    }

    public function getPercent(int $value): int
    {
        if ($this->totalItems === 0) {
            return 0;
        }

        return (int) round(($value / $this->totalItems) * 100);
    }

    public function render()
    {
        return view('livewire.partials.snapshot-kondisi');
    }

    protected function countByValue(string $value): int
    {
        if ($this->totalItems === 0) {
            return 0;
        }

        return Barang::whereRaw('LOWER(' . $this->field . ') = ?', [$value])->count();
    }

    protected function resetCounts(): void
    {
        $this->totalItems = 0;
        $this->baik = 0;
        $this->rusak = 0;
        $this->perbaikan = 0;
        $this->lainnya = 0;
    }
}