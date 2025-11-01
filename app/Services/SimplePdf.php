<?php

namespace App\Services;

class SimplePdf
{
    private float $width;
    private float $height;
    private float $marginLeft;
    private float $marginTop;
    private float $marginBottom;

    /**
     * @var array<int, array{content: string, images: array<string, true>}>
     */
    private array $pages = [];

    private int $currentPageIndex = -1;

    private float $currentY = 0.0;

    /**
     * @var array<string, array{data: string, width: int, height: int, colorSpace: string, filter: string, bitsPerComponent: int, objectNumber?: int}>
     */
    private array $imageRegistry = [];

    /**
     * @var array<string, string>
     */
    private array $imageLookup = [];

    private int $imageCounter = 0;

    public function __construct(float $width = 595.28, float $height = 841.89, float $margin = 40.0)
    {
        $this->width = $width;
        $this->height = $height;
        $this->marginLeft = $margin;
        $this->marginTop = $margin;
        $this->marginBottom = $margin;
    }

    public function addPage(): void
    {
        $this->pages[] = ['content' => '', 'images' => []];
        $this->currentPageIndex = count($this->pages) - 1;
        $this->currentY = $this->height - $this->marginTop;
    }

    public function addParagraph(string $text, float $fontSize = 11, float $leading = 14, ?int $wrap = 110, ?float $x = null): void
    {
        $lines = preg_split("/\r\n|\r|\n/", $text);

        foreach ($lines as $line) {
            $segments = $wrap !== null ? $this->wrapText($line, $wrap) : [$line];

            foreach ($segments as $segment) {
                $this->addLine($segment, $fontSize, $leading, $x);
            }
        }
    }

    public function addLine(string $text = '', float $fontSize = 11, float $leading = 14, ?float $x = null): void
    {
        $x ??= $this->marginLeft;
        $this->ensureSpace($leading);

        if ($text === '') {
            $text = ' ';
        }

        $escaped = $this->escapeText($text);
        $y = $this->currentY;

        $this->pages[$this->currentPageIndex]['content'] .= sprintf(
            "BT /F1 %.2f Tf %.2f %.2f Td (%s) Tj ET\n",
            $fontSize,
            $x,
            $y,
            $escaped,
        );

        $this->currentY -= $leading;
    }

    public function addSpacing(float $leading = 10): void
    {
        $this->ensureSpace($leading);
        $this->currentY -= $leading;
    }

    public function ensureBlockSpace(float $height): void
    {
        $this->ensureSpace($height);
    }

    public function getCursorY(): float
    {
        $this->ensurePageExists();

        return $this->currentY;
    }

    public function moveCursorTo(float $y): void
    {
        $this->ensurePageExists();

        if ($y < $this->marginBottom) {
            $this->addPage();

            return;
        }

        if ($y < $this->currentY) {
            $this->currentY = $y;
        }
    }

    public function drawImageFromPath(string $path, float $topY, float $x, float $maxWidth, float $maxHeight): ?float
    {
        $this->ensurePageExists();

        $info = @getimagesize($path);

        if (! $info || ! isset($info[0], $info[1], $info['mime'])) {
            return null;
        }

        [$imageWidth, $imageHeight] = $info;
        $mime = strtolower((string) $info['mime']);

        if ($mime !== 'image/jpeg' && $mime !== 'image/jpg') {
            return null;
        }

        $data = @file_get_contents($path);

        if ($data === false) {
            return null;
        }

        $imageName = $this->registerImage($data, (int) $imageWidth, (int) $imageHeight, '/DeviceRGB', '/DCTDecode', 8);

        $scale = min($maxWidth / max(1, $imageWidth), $maxHeight / max(1, $imageHeight), 1.0);
        $drawWidth = $imageWidth * $scale;
        $drawHeight = $imageHeight * $scale;
        $y = $topY - $drawHeight;

        $this->pages[$this->currentPageIndex]['content'] .= sprintf(
            "q %.2f 0 0 %.2f %.2f %.2f cm /%s Do Q\n",
            $drawWidth,
            $drawHeight,
            $x,
            $y,
            $imageName,
        );

        $this->pages[$this->currentPageIndex]['images'][$imageName] = true;

        return $drawHeight;
    }

    public function render(): string
    {
        $this->ensurePageExists();

        $objectNumber = 1;
        $offsets = [];

        $catalogNumber = $objectNumber++;
        $pagesNumber = $objectNumber++;
        $fontNumber = $objectNumber++;

        $contentNumbers = [];
        $pageNumbers = [];

        foreach ($this->imageRegistry as $name => &$image) {
            $image['objectNumber'] = $objectNumber++;
        }
        unset($image);

        foreach ($this->pages as $_) {
            $contentNumbers[] = $objectNumber++;
            $pageNumbers[] = $objectNumber++;
        }

        $pdf = "%PDF-1.4\n";

        $addObject = function (int $number, string $content) use (&$pdf, &$offsets): void {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number . " 0 obj\n" . $content . "\nendobj\n";
        };

        $addObject($fontNumber, "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>");

        foreach ($this->imageRegistry as $name => $image) {
            $data = $image['data'];

            if (! str_ends_with($data, "\n")) {
                $data .= "\n";
            }

            $length = strlen($data);
            $stream = sprintf(
                "<< /Type /XObject /Subtype /Image /Width %d /Height %d /ColorSpace %s /BitsPerComponent %d /Filter %s /Length %d >>\nstream\n",
                $image['width'],
                $image['height'],
                $image['colorSpace'],
                $image['bitsPerComponent'],
                $image['filter'],
                $length,
            );
            $stream .= $data;
            $stream .= "endstream\n";

            $addObject($image['objectNumber'], $stream);
        }

        foreach ($this->pages as $index => $page) {
            $content = $page['content'];

            if ($content === '') {
                $content = ' ';
            }

            if (! str_ends_with($content, "\n")) {
                $content .= "\n";
            }

            $length = strlen($content);
            $stream = sprintf("<< /Length %d >>\nstream\n%sendstream\n", $length, $content);
            $contentNumber = $contentNumbers[$index];
            $addObject($contentNumber, $stream);

            $resourceParts = [sprintf('/Font << /F1 %d 0 R >>', $fontNumber)];

            if ($page['images'] !== []) {
                $imageRefs = [];

                foreach (array_keys($page['images']) as $imageName) {
                    $objectNumber = $this->imageRegistry[$imageName]['objectNumber'] ?? null;

                    if ($objectNumber === null) {
                        continue;
                    }

                    $imageRefs[] = sprintf('/%s %d 0 R', $imageName, $objectNumber);
                }

                if ($imageRefs !== []) {
                    $resourceParts[] = sprintf('/XObject << %s >>', implode(' ', $imageRefs));
                }
            }

            $resources = implode(' ', $resourceParts);

            $pageObject = sprintf(
                '<< /Type /Page /Parent %d 0 R /MediaBox [0 0 %.2f %.2f] /Contents %d 0 R /Resources << %s >> >>',
                $pagesNumber,
                $this->width,
                $this->height,
                $contentNumber,
                $resources,
            );

            $addObject($pageNumbers[$index], $pageObject);
        }

        $kids = implode(' ', array_map(fn (int $number) => sprintf('%d 0 R', $number), $pageNumbers));

        $pagesObject = sprintf(
            '<< /Type /Pages /Count %d /Kids [%s] >>',
            count($pageNumbers),
            $kids,
        );
        $addObject($pagesNumber, $pagesObject);

        $catalogObject = sprintf('<< /Type /Catalog /Pages %d 0 R >>', $pagesNumber);
        $addObject($catalogNumber, $catalogObject);

        $xrefOffset = strlen($pdf);
        $totalObjects = $objectNumber;

        $pdf .= sprintf("xref\n0 %d\n", $totalObjects);
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i < $totalObjects; $i++) {
            $offset = $offsets[$i] ?? 0;
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= sprintf("trailer\n<< /Size %d /Root %d 0 R >>\nstartxref\n%d\n%%EOF", $totalObjects, $catalogNumber, $xrefOffset);

        return $pdf;
    }

    private function ensurePageExists(): void
    {
        if ($this->currentPageIndex === -1) {
            $this->addPage();
        }
    }

    private function ensureSpace(float $leading): void
    {
        $this->ensurePageExists();

        if (($this->currentY - $leading) < $this->marginBottom) {
            $this->addPage();
        }
    }

    private function escapeText(string $text): string
    {
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        $text = str_replace(["\r", "\n"], ' ', $text);

        return $text;
    }

    /**
     * @return array<int, string>
     */
    private function wrapText(string $text, int $width): array
    {
        if ($width <= 0) {
            return [$text];
        }

        if ($text === '') {
            return [''];
        }

        $words = preg_split('/\s+/u', $text) ?: [];
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

    private function registerImage(string $data, int $width, int $height, string $colorSpace, string $filter, int $bits): string
    {
        $hash = md5($data . $width . $height . $colorSpace . $filter . $bits);

        if (isset($this->imageLookup[$hash])) {
            return $this->imageLookup[$hash];
        }

        $name = 'Im' . (++$this->imageCounter);
        $this->imageLookup[$hash] = $name;
        $this->imageRegistry[$name] = [
            'data' => $data,
            'width' => $width,
            'height' => $height,
            'colorSpace' => $colorSpace,
            'filter' => $filter,
            'bitsPerComponent' => $bits,
        ];

        return $name;
    }
}