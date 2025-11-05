<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Collection;

trait FormatsBarangHistory
{
    protected function historyFieldLabels(): array
    {
        return [
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
    }

    protected function formatHistoryRecords($histories): array
    {
        return Collection::wrap($histories)
            ->map(function ($history) {
                return [
                    'id'        => $history->id,
                    'action'    => $this->formatHistoryAction($history->action),
                    'timestamp' => optional($history->created_at)->translatedFormat('d F Y H:i'),
                    'user'      => optional($history->updatedBy)->name,
                    'changes'   => $this->formatHistoryChanges($history->changes ?? []),
                ];
            })
            ->toArray();
    }

    protected function formatHistoryAction(string $action): string
    {
        return match ($action) {
            'created' => 'Ditambahkan',
            'updated' => 'Diperbarui',
            'deleted' => 'Dihapus',
            default   => ucfirst($action),
        };
    }

    protected function formatHistoryChanges($changes): array
    {
        $formatted = [];
        $labels = $this->historyFieldLabels();
        $changesArray = is_array($changes) ? $changes : [];

        foreach ($changesArray as $field => $value) {
            if (! array_key_exists($field, $labels)) {
                continue;
            }

            $formatted[] = [
                'label' => $labels[$field],
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

        if (is_array($value)) {
            return implode(', ', array_filter(array_map('strval', $value)));
        }

        return (string) $value;
    }
}