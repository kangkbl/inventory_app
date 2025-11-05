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
     * @var array<int, array{content: string, images: array<string, true>, fonts: array<string, true>}>
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

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $fonts = [];

    private string $currentFontKey;

    private ?string $boldFontKey = null;

    public function __construct(float $width = 595.28, float $height = 841.89, float $margin = 40.0)
    {
        $this->width = $width;
        $this->height = $height;
        $this->marginLeft = $margin;
        $this->marginTop = $margin;
        $this->marginBottom = $margin;
        $this->registerType1Font('F1', 'Helvetica');
        $this->registerType1Font('F2', 'Helvetica-Bold');
        $this->currentFontKey = 'F1';
        $this->boldFontKey = 'F2';
    }

    public function addPage(): void
    {
        $this->pages[] = ['content' => '', 'images' => [], 'fonts' => []];
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

    public function addLine(string $text = '', float $fontSize = 11, float $leading = 14, ?float $x = null, ?string $fontKey = null): void
    {
        $x ??= $this->marginLeft;
        $this->ensureSpace($leading);

        if ($text === '') {
            $text = ' ';
        }

        $escaped = $this->escapeText($text);
        $y = $this->currentY;
        $fontKey = $this->resolveFontKey($fontKey);

        $this->pages[$this->currentPageIndex]['content'] .= sprintf(
            "BT /%s %.2f Tf %.2f %.2f Td (%s) Tj ET\n",
            $fontKey,
            $fontSize,
            $x,
            $y,
            $escaped,
        );

        $this->pages[$this->currentPageIndex]['fonts'][$fontKey] = true;

        $this->currentY -= $leading;
    }

    public function addText(float $x, float $y, string $text, float $fontSize = 11, ?string $fontKey = null): void
    {
        $this->ensurePageExists();

        if ($text === '') {
            $text = ' ';
        }

        $escaped = $this->escapeText($text);
        $fontKey = $this->resolveFontKey($fontKey);

        $this->pages[$this->currentPageIndex]['content'] .= sprintf(
            "BT /%s %.2f Tf %.2f %.2f Td (%s) Tj ET\n",
            $fontKey,
            $fontSize,
            $x,
            $y,
            $escaped,
        );

        $this->pages[$this->currentPageIndex]['fonts'][$fontKey] = true;
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

    public function drawLine(float $x1, float $y1, float $x2, float $y2, float $width = 1.0): void
    {
        $this->ensurePageExists();

        $this->pages[$this->currentPageIndex]['content'] .= sprintf(
            "q %.2f w %.2f %.2f m %.2f %.2f l S Q\n",
            $width,
            $x1,
            $y1,
            $x2,
            $y2,
        );
    }

    public function getPageWidth(): float
    {
        return $this->width;
    }

    public function getPageHeight(): float
    {
        return $this->height;
    }

    public function getMarginLeft(): float
    {
        return $this->marginLeft;
    }

    public function getMarginTop(): float
    {
        return $this->marginTop;
    }

    public function getMarginBottom(): float
    {
        return $this->marginBottom;
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

        $data = null;

        if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
            $data = @file_get_contents($path);
        } elseif ($mime === 'image/png') {
            if (! function_exists('imagecreatefrompng') || ! function_exists('imagecreatetruecolor') || ! function_exists('imagejpeg')) {
                return null;
            }

            $resource = @imagecreatefrompng($path);

            if ($resource === false) {
                return null;
            }

            $imageWidth = imagesx($resource);
            $imageHeight = imagesy($resource);
            $background = @imagecreatetruecolor($imageWidth, $imageHeight);

            if ($background === false) {
                imagedestroy($resource);

                return null;
            }

            if (function_exists('imagealphablending')) {
                imagealphablending($background, true);
            }

            $white = imagecolorallocate($background, 255, 255, 255);
            imagefilledrectangle($background, 0, 0, $imageWidth, $imageHeight, $white);
            imagecopy($background, $resource, 0, 0, 0, 0, $imageWidth, $imageHeight);
            imagedestroy($resource);

            $stream = fopen('php://temp', 'w+b');

            if ($stream === false) {
                imagedestroy($background);

                return null;
            }

            if (! imagejpeg($background, $stream, 100)) {
                imagedestroy($background);
                fclose($stream);

                return null;
            }

            imagedestroy($background);
            rewind($stream);
            $data = stream_get_contents($stream);
            fclose($stream);
        }

        if ($data === false || $data === null) {
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
        
        $fontObjectNumbers = [];

        foreach ($this->fonts as $fontKey => &$font) {
            $font['objectNumber'] = $objectNumber++;

            if (($font['kind'] ?? null) === 'truetype') {
                $font['descriptorObjectNumber'] = $objectNumber++;
                $font['fileObjectNumber'] = $objectNumber++;
            }

            $fontObjectNumbers[$fontKey] = $font['objectNumber'];
        }
        unset($font);

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

        foreach ($this->fonts as $fontKey => $font) {
            if (($font['kind'] ?? null) === 'truetype') {
                $prepared = $font['prepared'] ?? $this->prepareTrueTypeFont($font);
                $this->fonts[$fontKey]['prepared'] = $prepared;

                $descriptor = sprintf(
                    "<< /Type /FontDescriptor /FontName /%s /Ascent %d /Descent %d /CapHeight %d /Flags %d /ItalicAngle %.2f /StemV %d /FontBBox [%d %d %d %d] /FontFile2 %d 0 R >>",
                    $prepared['fontName'],
                    $prepared['ascent'],
                    $prepared['descent'],
                    $prepared['capHeight'],
                    $prepared['flags'],
                    $prepared['italicAngle'],
                    $prepared['stemV'],
                    $prepared['bbox'][0],
                    $prepared['bbox'][1],
                    $prepared['bbox'][2],
                    $prepared['bbox'][3],
                    $font['fileObjectNumber'],
                );

                $addObject($font['descriptorObjectNumber'], $descriptor);

                $widths = implode(' ', $prepared['widths']);
                $fontObject = sprintf(
                    "<< /Type /Font /Subtype /TrueType /BaseFont /%s /Encoding /WinAnsiEncoding /FirstChar %d /LastChar %d /Widths [%s] /FontDescriptor %d 0 R >>",
                    $prepared['fontName'],
                    $prepared['firstChar'],
                    $prepared['lastChar'],
                    $widths,
                    $font['descriptorObjectNumber'],
                );

                $addObject($font['objectNumber'], $fontObject);

                $fontData = $prepared['data'];

                if (! str_ends_with($fontData, "\n")) {
                    $fontData .= "\n";
                }

                $stream = sprintf(
                    "<< /Length %d >>\nstream\n%s\nendstream\n",
                    strlen($fontData),
                    $fontData,
                );

                $addObject($font['fileObjectNumber'], $stream);

                continue;
            }

            $baseFont = $font['baseFont'] ?? 'Helvetica';
            $addObject($font['objectNumber'], sprintf('<< /Type /Font /Subtype /Type1 /BaseFont /%s >>', $baseFont));
        }

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

            $fontRefs = [];

            foreach ($fontObjectNumbers as $fontKey => $objectNumber) {
                $fontRefs[] = sprintf('/%s %d 0 R', $fontKey, $objectNumber);
            }

            $resourceParts = [];

            if ($fontRefs !== []) {
                $resourceParts[] = sprintf('/Font << %s >>', implode(' ', $fontRefs));
            }

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

    private function resolveFontKey(?string $fontKey): string
    {
        if ($fontKey !== null && isset($this->fonts[$fontKey])) {
            return $fontKey;
        }

        if (isset($this->fonts[$this->currentFontKey])) {
            return $this->currentFontKey;
        }

        return array_key_first($this->fonts) ?? 'F1';
    }

    public function setCurrentFont(string $fontKey): void
    {
        if (isset($this->fonts[$fontKey])) {
            $this->currentFontKey = $fontKey;
        }
    }

    public function getCurrentFontKey(): string
    {
        return $this->currentFontKey;
    }

    public function setBoldFontKey(?string $fontKey): void
    {
        if ($fontKey !== null && ! isset($this->fonts[$fontKey])) {
            return;
        }

        $this->boldFontKey = $fontKey;
    }

    public function getBoldFontKey(): ?string
    {
        return $this->boldFontKey;
    }

    public function withFont(string $fontKey, callable $callback): void
    {
        if (! isset($this->fonts[$fontKey])) {
            $callback($this);

            return;
        }

        $previous = $this->currentFontKey;
        $this->currentFontKey = $fontKey;

        try {
            $callback($this);
        } finally {
            $this->currentFontKey = $previous;
        }
    }

    public function registerType1Font(string $fontKey, string $baseFont): void
    {
        $this->fonts[$fontKey] = [
            'kind' => 'type1',
            'baseFont' => $baseFont,
        ];
    }

    public function registerTrueTypeFont(string $fontKey, string $path, string $fontName, bool $isBold = false): void
    {
        if (! is_file($path)) {
            return;
        }

        $data = @file_get_contents($path);

        if ($data === false) {
            return;
        }

        $this->fonts[$fontKey] = [
            'kind' => 'truetype',
            'baseFont' => $fontName,
            'fontPath' => $path,
            'fontData' => $data,
            'isBold' => $isBold,
        ];
    }

    /**
     * @param array{fontPath: string, fontData: string, baseFont: string, isBold?: bool} $font
     * @return array{fontName: string, firstChar: int, lastChar: int, widths: array<int, int>, ascent: int, descent: int, capHeight: int, flags: int, italicAngle: float, stemV: int, bbox: array<int, int>, data: string}
     */
    private function prepareTrueTypeFont(array $font): array
    {
        $fontName = str_replace(' ', '', $font['baseFont']);
        $firstChar = 32;
        $lastChar = 126;
        $size = 1000;
        $path = $font['fontPath'];

        if (! function_exists('imagettfbbox')) {
            $widthCount = ($lastChar - $firstChar) + 1;
            $widths = array_fill(0, $widthCount, 600);
            $flags = 32;

            if (! empty($font['isBold'])) {
                $flags |= 262144;
            }

            return [
                'fontName' => $fontName,
                'firstChar' => $firstChar,
                'lastChar' => $lastChar,
                'widths' => $widths,
                'ascent' => 800,
                'descent' => -200,
                'capHeight' => 700,
                'flags' => $flags,
                'italicAngle' => 0.0,
                'stemV' => ! empty($font['isBold']) ? 120 : 80,
                'bbox' => [0, -200, 1000, 800],
                'data' => $font['fontData'],
            ];
        }

        $widths = [];
        $minX = 0;
        $maxX = 0;
        $maxAscent = 0;
        $maxDescent = 0;

        for ($code = $firstChar; $code <= $lastChar; $code++) {
            $char = chr($code);
            $box = @imagettfbbox($size, 0, $path, $char);

            if ($box === false) {
                $widths[] = 600;

                continue;
            }

            $minX = min($minX, $box[0], $box[2], $box[4], $box[6]);
            $maxX = max($maxX, $box[0], $box[2], $box[4], $box[6]);
            $maxAscent = max($maxAscent, -min($box[1], $box[3], $box[5], $box[7]));
            $maxDescent = max($maxDescent, max($box[1], $box[3], $box[5], $box[7]));

            $width = (int) round(max($box[2], $box[4]) - min($box[0], $box[6]));
            $widths[] = max(0, $width);
        }

        $capBox = @imagettfbbox($size, 0, $path, 'H');
        $capHeight = $capBox !== false
            ? (int) round(abs(min($capBox[1], $capBox[3], $capBox[5], $capBox[7])))
            : (int) round($maxAscent);

        $bbox = [
            (int) floor($minX),
            (int) floor(-$maxDescent),
            (int) ceil($maxX),
            (int) ceil($maxAscent),
        ];

        $flags = 32;

        if (! empty($font['isBold'])) {
            $flags |= 262144;
        }

        $italicAngle = 0.0;
        $stemV = ! empty($font['isBold']) ? 120 : 80;

        return [
            'fontName' => $fontName,
            'firstChar' => $firstChar,
            'lastChar' => $lastChar,
            'widths' => $widths,
            'ascent' => (int) round($maxAscent),
            'descent' => (int) round(-$maxDescent),
            'capHeight' => (int) round($capHeight),
            'flags' => $flags,
            'italicAngle' => $italicAngle,
            'stemV' => $stemV,
            'bbox' => $bbox,
            'data' => $font['fontData'],
        ];
    }
}