<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use function public_path;

class BarangPdfExporter
{
    private const PAGE_WIDTH = 595.0;
    private const PAGE_HEIGHT = 842.0;
    private const PAGE_MARGIN = 30.0;

    private const HEADER_TITLE_LINES = [
        'LAPORAN INVENTARIS',
        'PERALATAN TRANSMISI JOGLO',
    ];
    private const HEADER_FONT_SIZE = 16.0;
    private const HEADER_LEADING = 22.0;
    private const META_FONT_SIZE = 10.0;
    private const META_LEADING = 14.0;

    private const LOGO_RELATIVE_PATH = 'images/tvri-logo.jpg';
    private const LOGO_MAX_WIDTH = 80.0;
    private const LOGO_MAX_HEIGHT = 40.0;
    private const LOGO_BOTTOM_SPACING = 6.0;

    private const TABLE_HEADER_FONT_SIZE = 11.0;
    private const TABLE_HEADER_LINE_HEIGHT = 14.0;
    private const TABLE_HEADER_MIN_HEIGHT = 28.0;
    private const TABLE_BODY_FONT_SIZE = 9.0;
    private const TABLE_BODY_LINE_HEIGHT = 12.0;
    private const TABLE_ROW_MIN_HEIGHT = 24.0;

    private const TABLE_CELL_PADDING_X = 4.0;
    private const TABLE_CELL_PADDING_Y = 5.0;
    private const TABLE_LINE_WIDTH = 0.8;
    private const CHARACTER_WIDTH_RATIO = 0.5;
    private const EMPTY_ROW_COUNT = 15;

    /**
     * @var array<int, array<string, mixed>>
     */
    private const COLUMNS = [
        [
            'key' => 'no',
            'label' => 'No',
            'width' => 30.0,
            'align' => 'center',
            'header_align' => 'center',
        ],
        [
            'key' => 'nama_barang',
            'label' => 'Nama Barang',
            'width' => 120.0,
            'align' => 'left',
            'header_align' => 'center',
        ],
        [
            'key' => 'merk',
            'label' => 'Merk',
            'width' => 60.0,
            'align' => 'left',
            'header_align' => 'center',
        ],
        [
            'key' => 'kategori',
            'label' => 'Kategori',
            'width' => 60.0,
            'align' => 'left',
            'header_align' => 'center',
        ],
        [
            'key' => 'lokasi',
            'label' => 'Lokasi',
            'width' => 60.0,
            'align' => 'left',
            'header_align' => 'center',
        ],
        [
            'key' => 'kondisi',
            'label' => 'Kondisi',
            'width' => 50.0,
            'align' => 'center',
            'header_align' => 'center',
        ],
        [
            'key' => 'jumlah',
            'label' => 'Jumlah',
            'width' => 40.0,
            'align' => 'center',
            'header_align' => 'center',
        ],
        [
            'key' => 'tahun_pengadaan',
            'label' => 'Tahun',
            'width' => 40.0,
            'align' => 'center',
            'header_align' => 'center',
        ],
        [
            'key' => 'keterangan',
            'label' => 'Keterangan',
            'width' => 75.0,
            'align' => 'left',
            'header_align' => 'center',
        ],
    ];

    public function build(Collection $items): string
    {
        $pdf = new SimplePdf(width: self::PAGE_WIDTH, height: self::PAGE_HEIGHT, margin: self::PAGE_MARGIN);
        $printedAt = now();
        $lastUpdated = $this->resolveLastUpdated($items);

        $columns = $this->prepareColumns();
        $columnBoundaries = $this->computeColumnBoundaries($pdf, $columns);

        $rows = $this->mapItemsToRows($items);

        if ($rows === []) {
            $rows = array_fill(0, self::EMPTY_ROW_COUNT, $this->createEmptyRow());
        }

        $preparedRows = $this->prepareRowLines($rows, $columns);

        $pageNumber = 0;
        $currentY = $this->initializePage($pdf, $printedAt, $lastUpdated, ++$pageNumber, $columns, $columnBoundaries);

        foreach ($preparedRows as $row) {
            if ($currentY - $row['height'] < $pdf->getMarginBottom()) {
                $currentY = $this->initializePage($pdf, $printedAt, $lastUpdated, ++$pageNumber, $columns, $columnBoundaries);
            }

            $currentY = $this->renderTableRow($pdf, $columns, $columnBoundaries, $row, $currentY);
        }

        return $pdf->render();
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function mapItemsToRows(Collection $items): array
    {
        $rows = [];

        foreach ($items->values() as $index => $barang) {
            $rows[] = [
                'no' => (string) ($index + 1),
                'nama_barang' => $this->formatValue($barang->nama_barang ?? null),
                'merk' => $this->formatValue($barang->merk ?? null),
                'kategori' => $this->formatValue($barang->kategori ?? null),
                'lokasi' => $this->formatValue($barang->lokasi ?? null),
                'kondisi' => $this->formatValue($barang->kondisi ?? null),
                'jumlah' => $this->formatValue($barang->jumlah ?? null),
                'tahun_pengadaan' => $this->formatValue($barang->tahun_pengadaan ?? null),
                'keterangan' => $this->formatValue($barang->keterangan ?? null),
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, string>
     */
    private function createEmptyRow(): array
    {
        $row = [];

        foreach (self::COLUMNS as $column) {
            $row[$column['key']] = '';
        }

        return $row;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function prepareColumns(): array
    {
        $columns = [];

        foreach (self::COLUMNS as $column) {
            $column['bodyWrap'] = $this->calculateWrapLimit($column['width'], self::TABLE_BODY_FONT_SIZE);
            $column['headerWrap'] = $this->calculateWrapLimit($column['width'], self::TABLE_HEADER_FONT_SIZE);
            $column['headerLines'] = $this->wrapCellText($column['label'], $column['headerWrap']);
            $columns[] = $column;
        }

        return $columns;
    }

    /**
     * @param array<int, array<string, mixed>> $columns
     * @return array<int, float>
     */
    private function computeColumnBoundaries(SimplePdf $pdf, array $columns): array
    {
        $positions = [];
        $current = $pdf->getMarginLeft();
        $positions[] = $current;

        foreach ($columns as $column) {
            $current += (float) $column['width'];
            $positions[] = $current;
        }

        return $positions;
    }

    /**
     * @param array<int, array<string, string>> $rows
     * @param array<int, array<string, mixed>> $columns
     * @return array<int, array{lines: array<string, array<int, string>>, height: float}>
     */
    private function prepareRowLines(array $rows, array $columns): array
    {
        $prepared = [];

        foreach ($rows as $row) {
            $lines = [];
            $maxLines = 1;

            foreach ($columns as $column) {
                $key = (string) $column['key'];
                $value = $row[$key] ?? '';
                $wrapped = $this->wrapCellText($value, (int) $column['bodyWrap']);
                $lines[$key] = $wrapped;
                $maxLines = max($maxLines, count($wrapped));
            }

            $height = max(self::TABLE_ROW_MIN_HEIGHT, (2 * self::TABLE_CELL_PADDING_Y) + ($maxLines * self::TABLE_BODY_LINE_HEIGHT));

            $prepared[] = [
                'lines' => $lines,
                'height' => $height,
            ];
        }

        return $prepared;
    }

    /**
     * @param array<int, array<string, mixed>> $columns
     * @param array<int, float> $columnBoundaries
     */
    private function initializePage(SimplePdf $pdf, Carbon $printedAt, ?Carbon $lastUpdated, int $pageNumber, array $columns, array $columnBoundaries): float
    {
        $pdf->addPage();
        $this->renderHeader($pdf, $printedAt, $lastUpdated, $pageNumber);
        $pdf->addSpacing(12.0);

        $currentY = $pdf->getCursorY();

        return $this->renderTableHeader($pdf, $columns, $columnBoundaries, $currentY);
    }

    private function renderHeader(SimplePdf $pdf, Carbon $printedAt, ?Carbon $lastUpdated, int $pageNumber): void
    {
        $logoBottom = $this->renderLogo($pdf);

        foreach (self::HEADER_TITLE_LINES as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            $titleX = $this->resolveCenteredX($pdf, $trimmed, self::HEADER_FONT_SIZE);
            $pdf->addLine($trimmed, self::HEADER_FONT_SIZE, self::HEADER_LEADING, $titleX);
        }

        if ($logoBottom !== null) {
            $minimumY = $logoBottom - self::LOGO_BOTTOM_SPACING;

            if ($pdf->getCursorY() > $minimumY) {
                $pdf->moveCursorTo($minimumY);
            }
        }

        $margin = $pdf->getMarginLeft();
        $pdf->addLine('Tgl Update : ' . $this->formatTimestamp($lastUpdated), self::META_FONT_SIZE, self::META_LEADING, $margin);
        $pdf->addLine('Tgl Cetak  : ' . $this->formatTimestamp($printedAt), self::META_FONT_SIZE, self::META_LEADING, $margin);
        $pdf->addLine('Halaman    : ' . $pageNumber, self::META_FONT_SIZE, self::META_LEADING, $margin);
    }

    private function renderLogo(SimplePdf $pdf): ?float
    {
        $path = $this->resolveLogoPath();

        if ($path === null) {
            return null;
        }

        $topY = $pdf->getPageHeight() - $pdf->getMarginTop();
        $drawnHeight = $pdf->drawImageFromPath($path, $topY, $pdf->getMarginLeft(), self::LOGO_MAX_WIDTH, self::LOGO_MAX_HEIGHT);

        if ($drawnHeight === null) {
            return null;
        }

        return $topY - $drawnHeight;
    }

    private function resolveLogoPath(): ?string
    {
        if (! function_exists('public_path')) {
            return null;
        }

        $path = public_path(self::LOGO_RELATIVE_PATH);

        if (! is_string($path) || $path === '') {
            return null;
        }

        if (! is_file($path)) {
            return null;
        }

        return $path;
    }

    /**
     * @param array<int, array<string, mixed>> $columns
     * @param array<int, float> $columnBoundaries
     */
    private function renderTableHeader(SimplePdf $pdf, array $columns, array $columnBoundaries, float $currentY): float
    {
        $maxLines = 1;

        foreach ($columns as $column) {
            $maxLines = max($maxLines, count($column['headerLines']));
        }

        $rowHeight = max(self::TABLE_HEADER_MIN_HEIGHT, (2 * self::TABLE_CELL_PADDING_Y) + ($maxLines * self::TABLE_HEADER_LINE_HEIGHT));
        $top = $currentY;
        $bottom = $top - $rowHeight;

        $this->drawRowGrid($pdf, $columnBoundaries, $top, $bottom, true);

        foreach ($columns as $index => $column) {
            $lines = $column['headerLines'];
            $lineY = $top - self::TABLE_CELL_PADDING_Y - self::TABLE_HEADER_FONT_SIZE;

            foreach ($lines as $line) {
                $x = $this->resolveTextX($column, $columnBoundaries[$index], $line, self::TABLE_HEADER_FONT_SIZE, true);
                $pdf->addText($x, $lineY, $line, self::TABLE_HEADER_FONT_SIZE);
                $lineY -= self::TABLE_HEADER_LINE_HEIGHT;
            }
        }

        $pdf->moveCursorTo($bottom);

        return $bottom;
    }

    /**
     * @param array<int, array<string, mixed>> $columns
     * @param array<int, float> $columnBoundaries
     * @param array{lines: array<string, array<int, string>>, height: float} $row
     */
    private function renderTableRow(SimplePdf $pdf, array $columns, array $columnBoundaries, array $row, float $currentY): float
    {
        $rowHeight = (float) $row['height'];
        $top = $currentY;
        $bottom = $top - $rowHeight;

        $this->drawRowGrid($pdf, $columnBoundaries, $top, $bottom, false);

        foreach ($columns as $index => $column) {
            $key = (string) $column['key'];
            $lines = $row['lines'][$key] ?? [''];
            $lineY = $top - self::TABLE_CELL_PADDING_Y - self::TABLE_BODY_FONT_SIZE;

            foreach ($lines as $line) {
                $x = $this->resolveTextX($column, $columnBoundaries[$index], $line, self::TABLE_BODY_FONT_SIZE, false);
                $pdf->addText($x, $lineY, $line, self::TABLE_BODY_FONT_SIZE);
                $lineY -= self::TABLE_BODY_LINE_HEIGHT;
            }
        }

        $pdf->moveCursorTo($bottom);

        return $bottom;
    }

    private function drawRowGrid(SimplePdf $pdf, array $columnBoundaries, float $top, float $bottom, bool $includeTop): void
    {
        $left = $columnBoundaries[0];
        $right = $columnBoundaries[array_key_last($columnBoundaries)];

        if ($includeTop) {
            $pdf->drawLine($left, $top, $right, $top, self::TABLE_LINE_WIDTH);
        }

        $pdf->drawLine($left, $bottom, $right, $bottom, self::TABLE_LINE_WIDTH);

        foreach ($columnBoundaries as $x) {
            $pdf->drawLine($x, $bottom, $x, $top, self::TABLE_LINE_WIDTH);
        }
    }

    private function calculateWrapLimit(float $columnWidth, float $fontSize): int
    {
        $usableWidth = max(0.0, $columnWidth - (2 * self::TABLE_CELL_PADDING_X));

        if ($usableWidth <= 0.0) {
            return 1;
        }

        $characters = (int) floor($usableWidth / ($fontSize * self::CHARACTER_WIDTH_RATIO));

        return max(1, $characters);
    }

    /**
     * @return array<int, string>
     */
    private function wrapCellText(string $text, int $width): array
    {
        if ($width <= 0) {
            return [$text];
        }

        $normalized = trim((string) $text);

        if ($normalized === '') {
            return [''];
        }

        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? $normalized;
        $words = preg_split('/\s+/u', $normalized) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            if ($word === '') {
                continue;
            }

            $candidate = $current === '' ? $word : $current . ' ' . $word;

            if (mb_strlen($candidate) <= $width) {
                $current = $candidate;
                continue;
            }

            if ($current !== '') {
                $lines[] = $current;
                $current = '';
            }

            if (mb_strlen($word) <= $width) {
                $current = $word;
                continue;
            }

            foreach ($this->splitLongWord($word, $width) as $segment) {
                if ($current === '') {
                    $current = $segment;
                    continue;
                }

                $lines[] = $current;
                $current = $segment;
            }
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines === [] ? [''] : $lines;
    }

    /**
     * @return array<int, string>
     */
    private function splitLongWord(string $word, int $width): array
    {
        $segments = [];
        $length = mb_strlen($word);

        for ($offset = 0; $offset < $length; $offset += $width) {
            $segments[] = mb_substr($word, $offset, $width);
        }

        return $segments;
    }

    private function resolveCenteredX(SimplePdf $pdf, string $text, float $fontSize): float
    {
        $usableWidth = $pdf->getPageWidth() - (2 * $pdf->getMarginLeft());
        $estimatedWidth = min($usableWidth, $this->estimateTextWidth($text, $fontSize));

        return $pdf->getMarginLeft() + max(0.0, ($usableWidth - $estimatedWidth) / 2);
    }

    private function resolveTextX(array $column, float $columnLeft, string $text, float $fontSize, bool $isHeader): float
    {
        $contentWidth = max(0.0, ((float) $column['width']) - (2 * self::TABLE_CELL_PADDING_X));
        $estimatedWidth = min($contentWidth, $this->estimateTextWidth($text, $fontSize));
        $alignKey = $isHeader ? 'header_align' : 'align';
        $align = $column[$alignKey] ?? ($isHeader ? 'center' : 'left');

        return match ($align) {
            'center' => $columnLeft + self::TABLE_CELL_PADDING_X + max(0.0, ($contentWidth - $estimatedWidth) / 2),
            'right' => $columnLeft + self::TABLE_CELL_PADDING_X + max(0.0, $contentWidth - $estimatedWidth),
            default => $columnLeft + self::TABLE_CELL_PADDING_X,
        };
    }

    private function estimateTextWidth(string $text, float $fontSize): float
    {
        $length = mb_strlen($text);

        return max(0.0, $length * $fontSize * self::CHARACTER_WIDTH_RATIO);
    }

    private function formatValue(mixed $value): string
    {
        if ($value === null) {
            return '-';
        }

        if ($value instanceof \DateTimeInterface) {
            $value = Carbon::instance($value)->format('d/m/Y');
        }

        $string = trim((string) $value);

        return $string === '' ? '-' : $string;
    }

    private function resolveLastUpdated(Collection $items): ?Carbon
    {
        $latest = null;

        foreach ($items as $barang) {
            $candidate = $barang->updated_at ?? $barang->created_at ?? null;

            if ($candidate instanceof Carbon) {
                $timestamp = $candidate;
            } elseif ($candidate instanceof \DateTimeInterface) {
                $timestamp = Carbon::instance($candidate);
            } elseif (is_string($candidate) && $candidate !== '') {
                $timestamp = Carbon::parse($candidate);
            } else {
                continue;
            }

            if ($latest === null || $timestamp->greaterThan($latest)) {
                $latest = $timestamp;
            }
        }

        return $latest;
    }

    private function formatTimestamp(?Carbon $timestamp): string
    {
        return $timestamp?->format('d/m/Y h:i A') ?? '-';
    }
}