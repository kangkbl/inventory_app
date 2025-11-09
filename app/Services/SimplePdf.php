<?php

namespace App\Services;

use function iconv;

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

    private string $fontResourceName = 'F1';

    private string $fontBaseName = 'Helvetica';

    private string $fontEncoding = 'WinAnsiEncoding';

    /**
     * @var array{
     *     name: string,
     *     firstChar: int,
     *     lastChar: int,
     *     widths: array<int, int>,
     *     missingWidth: int,
     *     descriptor: array{
     *         flags: int,
     *         ascent: int,
     *         descent: int,
     *         capHeight: int,
     *         italicAngle: float,
     *         stemV: int,
     *         fontBBox: array<int, int>,
     *         avgWidth: int,
     *         maxWidth: int,
     *         missingWidth: int
     *     },
     *     data: string
     * }|null
     */
    private ?array $trueTypeFont = null;

    /**
     * @var array<string, array{data: string, width: int, height: int, colorSpace: string, filter: string, bitsPerComponent: int, objectNumber?: int}>
     */
    private array $imageRegistry = [];

    /**
     * @var array<string, string>
     */
    private array $imageLookup = [];

    private int $imageCounter = 0;

    public function __construct(float $width = 595.28, float $height = 841.89, float $margin = 40.0, ?float $marginTop = null, ?float $marginBottom = null)
    {
        $this->width = $width;
        $this->height = $height;
        $this->marginLeft = $margin;
        $this->marginTop = $marginTop ?? $margin;
        $this->marginBottom = $marginBottom ?? $margin;
    }

    public function useTrueTypeFont(string $path, ?string $postScriptName = null): void
    {
        if ($path === '' || ! is_file($path)) {
            return;
        }

        $data = @file_get_contents($path);

        if ($data === false) {
            return;
        }

        $font = $this->parseTrueTypeFont($data, $postScriptName);

        if ($font === null) {
            return;
        }

        $this->trueTypeFont = $font;
        $this->fontBaseName = $font['name'];
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

        $encoded = $this->encodeText($text);

        if ($encoded === '') {
            $encoded = ' ';
        }

        $escaped = $this->escapeText($encoded);
        $y = $this->currentY;

        $this->pages[$this->currentPageIndex]['content'] .= sprintf(
            "BT /%s %.2f Tf %.2f %.2f Td (%s) Tj ET\n",
            $this->fontResourceName,
            $fontSize,
            $x,
            $y,
            $escaped,
        );

        $this->currentY -= $leading;
    }

    public function addText(float $x, float $y, string $text, float $fontSize = 11): void
    {
        $this->ensurePageExists();

        $encoded = $this->encodeText($text);

        if ($encoded === '') {
            $encoded = ' ';
        }

        $escaped = $this->escapeText($encoded);

        $this->pages[$this->currentPageIndex]['content'] .= sprintf(
            "BT /%s %.2f Tf %.2f %.2f Td (%s) Tj ET\n",
            $this->fontResourceName,
            $fontSize,
            $x,
            $y,
            $escaped,
        );
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

    public function drawSvgFromPath(string $path, float $topY, float $x, float $maxWidth, float $maxHeight): ?float
    {
        $this->ensurePageExists();

        $contents = @file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        $minX = 0.0;
        $minY = 0.0;
        $viewBoxWidth = null;
        $viewBoxHeight = null;

        if (preg_match('/viewBox="([^"]+)"/i', $contents, $viewBoxMatch)) {
            $viewBoxParts = preg_split('/[\s,]+/', trim($viewBoxMatch[1]));

            if ($viewBoxParts === false || count($viewBoxParts) !== 4) {
                return null;
            }

            [$minX, $minY, $viewBoxWidth, $viewBoxHeight] = array_map('floatval', $viewBoxParts);
        } else {
            $viewBoxWidth = $this->parseSvgLengthAttribute($contents, 'width');
            $viewBoxHeight = $this->parseSvgLengthAttribute($contents, 'height');

            if ($viewBoxWidth === null || $viewBoxHeight === null) {
                return null;
            }

            $minXAttr = $this->parseSvgLengthAttribute($contents, 'x');
            $minYAttr = $this->parseSvgLengthAttribute($contents, 'y');

            if ($minXAttr !== null) {
                $minX = $minXAttr;
            }

            if ($minYAttr !== null) {
                $minY = $minYAttr;
            }
        }

        if ($viewBoxWidth === null || $viewBoxHeight === null || $viewBoxWidth <= 0 || $viewBoxHeight <= 0) {
            return null;
        }

        if (! preg_match('/<path[^>]*d="([^"]+)"[^>]*>/i', $contents, $pathMatch)) {
            return null;
        }

        $pathData = $pathMatch[1];

        $fillColor = '#000000';

        if (preg_match('/fill="([^"]+)"/i', $pathMatch[0], $fillMatch)) {
            $fillColor = (string) $fillMatch[1];
        } elseif (preg_match('/style="([^"]+)"/i', $pathMatch[0], $styleMatch)) {
            $styleDeclarations = explode(';', $styleMatch[1]);

            foreach ($styleDeclarations as $declaration) {
                [$property, $value] = array_pad(array_map('trim', explode(':', $declaration, 2)), 2, null);

                if ($property === 'fill' && $value !== null && $value !== '') {
                    $fillColor = $value;

                    break;
                }
            }
        }

        $pdfPath = $this->convertSvgPathToPdfPath($pathData);

        if ($pdfPath === '') {
            return null;
        }

        $scale = min($maxWidth / $viewBoxWidth, $maxHeight / $viewBoxHeight, 1.0);

        $drawHeight = $viewBoxHeight * $scale;

        [$r, $g, $b] = $this->parseSvgColor($fillColor);

        $translateX = $x - ($minX * $scale);
        $translateY = $topY + ($minY * $scale);

        $this->pages[$this->currentPageIndex]['content'] .= sprintf(
            "q %.4f 0 0 %.4f %.4f %.4f cm %.4f %.4f %.4f rg %sf Q\n",
            $scale,
            -$scale,
            $translateX,
            $translateY,
            $r,
            $g,
            $b,
            $pdfPath,
        );

        return $drawHeight;
    }

    private function parseSvgColor(string $color): array
    {
        $color = trim($color);

        if ($color === '' || strtolower($color) === 'none') {
            return [0.0, 0.0, 0.0];
        }

        if ($color[0] === '#') {
            $hex = substr($color, 1);

            if (strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }

            if (strlen($hex) === 6 && ctype_xdigit($hex)) {
                $r = hexdec(substr($hex, 0, 2));
                $g = hexdec(substr($hex, 2, 2));
                $b = hexdec(substr($hex, 4, 2));

                return [$r / 255, $g / 255, $b / 255];
            }
        }

        return [0.0, 0.0, 0.0];
    }

    private function parseSvgLengthAttribute(string $svg, string $attribute): ?float
    {
        if (! preg_match('/\b' . preg_quote($attribute, '/') . '="([^"]+)"/i', $svg, $match)) {
            return null;
        }

        return $this->parseSvgLength($match[1]);
    }

    private function parseSvgLength(string $value): ?float
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (! preg_match('/^([-+]?\d*\.?\d+)([a-z%]*)$/i', $value, $parts)) {
            if (preg_match('/([-+]?\d*\.?\d+)/', $value, $fallback)) {
                return (float) $fallback[1];
            }

            return null;
        }

        $number = (float) $parts[1];
        $unit = strtolower($parts[2] ?? '');

        return match ($unit) {
            '', 'px' => $number,
            'pt' => $number * (96.0 / 72.0),
            'in' => $number * 96.0,
            'cm' => $number * (96.0 / 2.54),
            'mm' => $number * (96.0 / 25.4),
            default => null,
        };
    }

    private function convertSvgPathToPdfPath(string $pathData): string
    {
        if ($pathData === '') {
            return '';
        }

        if (! preg_match_all('/([MmLlHhVvCcZz])|([-+]?\d*\.?\d+(?:[eE][-+]?\d+)?)/', $pathData, $matches)) {
            return '';
        }

        $tokens = [];

        foreach ($matches[0] as $index => $token) {
            if ($matches[1][$index] !== '') {
                $tokens[] = ['type' => 'command', 'value' => $matches[1][$index]];
            } elseif ($matches[2][$index] !== '') {
                $tokens[] = ['type' => 'number', 'value' => (float) $matches[2][$index]];
            }
        }

        $result = [];
        $currentX = 0.0;
        $currentY = 0.0;
        $subPathStartX = 0.0;
        $subPathStartY = 0.0;

        $count = count($tokens);
        $i = 0;

        while ($i < $count) {
            $token = $tokens[$i];

            if ($token['type'] !== 'command') {
                $i++;

                continue;
            }

            $command = $token['value'];
            $absoluteCommand = strtoupper($command);
            $isRelative = $command !== $absoluteCommand;
            $i++;

            $numbers = [];

            while ($i < $count && $tokens[$i]['type'] === 'number') {
                $numbers[] = $tokens[$i]['value'];
                $i++;
            }

            switch ($absoluteCommand) {
                case 'M':
                    $pairs = count($numbers) / 2;

                    for ($j = 0; $j < $pairs; $j++) {
                        $x = $numbers[$j * 2] ?? null;
                        $y = $numbers[($j * 2) + 1] ?? null;

                        if ($x === null || $y === null) {
                            return '';
                        }

                        if ($isRelative) {
                            $x += $currentX;
                            $y += $currentY;
                        }

                        if ($j === 0) {
                            $result[] = sprintf('%.4f %.4f m ', $x, $y);
                            $subPathStartX = $x;
                            $subPathStartY = $y;
                        } else {
                            $result[] = sprintf('%.4f %.4f l ', $x, $y);
                        }

                        $currentX = $x;
                        $currentY = $y;
                    }

                    break;

                case 'L':
                    $pairs = count($numbers) / 2;

                    for ($j = 0; $j < $pairs; $j++) {
                        $x = $numbers[$j * 2] ?? null;
                        $y = $numbers[($j * 2) + 1] ?? null;

                        if ($x === null || $y === null) {
                            return '';
                        }

                        if ($isRelative) {
                            $x += $currentX;
                            $y += $currentY;
                        }

                        $result[] = sprintf('%.4f %.4f l ', $x, $y);
                        $currentX = $x;
                        $currentY = $y;
                    }

                    break;

                case 'H':
                    foreach ($numbers as $x) {
                        if ($isRelative) {
                            $currentX += $x;
                        } else {
                            $currentX = $x;
                        }

                        $result[] = sprintf('%.4f %.4f l ', $currentX, $currentY);
                    }

                    break;

                case 'V':
                    foreach ($numbers as $y) {
                        if ($isRelative) {
                            $currentY += $y;
                        } else {
                            $currentY = $y;
                        }

                        $result[] = sprintf('%.4f %.4f l ', $currentX, $currentY);
                    }

                    break;

                case 'C':
                    $segments = count($numbers) / 6;

                    for ($j = 0; $j < $segments; $j++) {
                        $x1 = $numbers[($j * 6)] ?? null;
                        $y1 = $numbers[($j * 6) + 1] ?? null;
                        $x2 = $numbers[($j * 6) + 2] ?? null;
                        $y2 = $numbers[($j * 6) + 3] ?? null;
                        $x = $numbers[($j * 6) + 4] ?? null;
                        $y = $numbers[($j * 6) + 5] ?? null;

                        if ($x1 === null || $y1 === null || $x2 === null || $y2 === null || $x === null || $y === null) {
                            return '';
                        }

                        if ($isRelative) {
                            $x1 += $currentX;
                            $y1 += $currentY;
                            $x2 += $currentX;
                            $y2 += $currentY;
                            $x += $currentX;
                            $y += $currentY;
                        }

                        $result[] = sprintf('%.4f %.4f %.4f %.4f %.4f %.4f c ', $x1, $y1, $x2, $y2, $x, $y);
                        $currentX = $x;
                        $currentY = $y;
                    }

                    break;

                case 'Z':
                    $result[] = 'h ';
                    $currentX = $subPathStartX;
                    $currentY = $subPathStartY;

                    break;

                default:
                    return '';
            }
        }

        return implode('', $result);
    }

    /**
     * @return array{
     *     name: string,
     *     firstChar: int,
     *     lastChar: int,
     *     widths: array<int, int>,
     *     missingWidth: int,
     *     descriptor: array{
     *         flags: int,
     *         ascent: int,
     *         descent: int,
     *         capHeight: int,
     *         italicAngle: float,
     *         stemV: int,
     *         fontBBox: array<int, int>,
     *         avgWidth: int,
     *         maxWidth: int,
     *         missingWidth: int
     *     },
     *     data: string
     * }|null
     */
    private function parseTrueTypeFont(string $data, ?string $postScriptName): ?array
    {
        $length = strlen($data);

        if ($length < 12) {
            return null;
        }

        $numTables = $this->readUInt16($data, 4);

        if ($numTables === null || $numTables <= 0) {
            return null;
        }

        $tables = [];

        for ($i = 0; $i < $numTables; $i++) {
            $entryOffset = 12 + ($i * 16);

            if ($entryOffset + 16 > $length) {
                return null;
            }

            $tag = substr($data, $entryOffset, 4);
            $tableOffset = $this->readUInt32($data, $entryOffset + 8);
            $tableLength = $this->readUInt32($data, $entryOffset + 12);

            if ($tag === false || $tableOffset === null || $tableLength === null) {
                return null;
            }

            if ($tableOffset + $tableLength > $length) {
                return null;
            }

            $tables[$tag] = substr($data, $tableOffset, $tableLength);
        }

        $head = $tables['head'] ?? null;
        $hhea = $tables['hhea'] ?? null;
        $maxp = $tables['maxp'] ?? null;
        $hmtx = $tables['hmtx'] ?? null;
        $cmap = $tables['cmap'] ?? null;

        if ($head === null || $hhea === null || $maxp === null || $hmtx === null || $cmap === null) {
            return null;
        }

        $unitsPerEm = $this->readUInt16($head, 18);

        if ($unitsPerEm === null || $unitsPerEm <= 0) {
            return null;
        }

        $xMin = $this->readInt16($head, 36) ?? 0;
        $yMin = $this->readInt16($head, 38) ?? 0;
        $xMax = $this->readInt16($head, 40) ?? 0;
        $yMax = $this->readInt16($head, 42) ?? 0;

        $ascentUnits = $this->readInt16($hhea, 4) ?? 0;
        $descentUnits = $this->readInt16($hhea, 6) ?? 0;
        $numberOfHMetrics = $this->readUInt16($hhea, 34);

        if ($numberOfHMetrics === null || $numberOfHMetrics <= 0) {
            return null;
        }

        $numGlyphs = $this->readUInt16($maxp, 4);

        if ($numGlyphs === null || $numGlyphs <= 0) {
            return null;
        }

        $advanceWidths = [];

        for ($i = 0; $i < $numberOfHMetrics; $i++) {
            $advance = $this->readUInt16($hmtx, $i * 4);

            if ($advance === null) {
                return null;
            }

            $advanceWidths[$i] = $advance;
        }

        $lastAdvance = $advanceWidths[$numberOfHMetrics - 1] ?? 0;

        for ($i = $numberOfHMetrics; $i < $numGlyphs; $i++) {
            $advanceWidths[$i] = $lastAdvance;
        }

        $glyphMap = $this->parseCmapTable($cmap, 32, 126);

        if ($glyphMap === null) {
            return null;
        }

        $firstChar = 32;
        $lastChar = 126;

        $spaceGlyph = $glyphMap[32] ?? null;
        $missingAdvance = $spaceGlyph !== null && isset($advanceWidths[$spaceGlyph])
            ? $advanceWidths[$spaceGlyph]
            : ($advanceWidths[0] ?? $unitsPerEm);
        $missingWidth = (int) round(($missingAdvance / $unitsPerEm) * 1000);

        $widths = [];
        $maxWidth = 0;
        $totalWidth = 0;

        for ($char = $firstChar; $char <= $lastChar; $char++) {
            $glyphIndex = $glyphMap[$char] ?? null;
            $advance = $missingAdvance;

            if ($glyphIndex !== null && isset($advanceWidths[$glyphIndex])) {
                $advance = $advanceWidths[$glyphIndex];
            }

            $width = (int) round(($advance / $unitsPerEm) * 1000);
            $width = max(0, $width);
            $widths[] = $width;
            $maxWidth = max($maxWidth, $width);
            $totalWidth += $width;
        }

        $avgWidthUnits = null;
        $weightClass = null;
        $capHeightUnits = null;

        if (isset($tables['OS/2'])) {
            $os2 = $tables['OS/2'];
            $avgWidthUnits = $this->readInt16($os2, 2);
            $weightClass = $this->readUInt16($os2, 4);
            $version = $this->readUInt16($os2, 0) ?? 0;

            if ($version >= 2) {
                $capHeightUnits = $this->readInt16($os2, 88);
            }
        }

        if ($capHeightUnits === null || $capHeightUnits <= 0) {
            $capHeightUnits = $ascentUnits;
        }

        $characterCount = ($lastChar - $firstChar) + 1;
        $avgWidth = $avgWidthUnits !== null
            ? (int) round(($avgWidthUnits / $unitsPerEm) * 1000)
            : (int) round($totalWidth / max(1, $characterCount));

        $italicAngle = 0.0;

        if (isset($tables['post'])) {
            $post = $tables['post'];
            $angle = $this->readFixed($post, 4);

            if ($angle !== null) {
                $italicAngle = $angle;
            }
        }

        $flags = 32;

        if (abs($italicAngle) > 0.01) {
            $flags |= 64;
        }

        $stemV = (int) round(($weightClass ?? 400) / 5);
        $stemV = max(50, min(1000, $stemV));

        $fontBBox = [
            (int) floor(($xMin / $unitsPerEm) * 1000),
            (int) floor(($yMin / $unitsPerEm) * 1000),
            (int) ceil(($xMax / $unitsPerEm) * 1000),
            (int) ceil(($yMax / $unitsPerEm) * 1000),
        ];

        $ascent = (int) round(($ascentUnits / $unitsPerEm) * 1000);
        $descent = (int) round(($descentUnits / $unitsPerEm) * 1000);
        $capHeight = (int) round(($capHeightUnits / $unitsPerEm) * 1000);

        $postScript = null;

        if (isset($tables['name'])) {
            $postScript = $this->extractPostScriptName($tables['name']);
        }

        if ($postScriptName !== null && $postScriptName !== '') {
            $postScript = $postScriptName;
        }

        $fontName = $this->sanitizeFontName($postScript ?? 'CustomFont');

        return [
            'name' => $fontName,
            'firstChar' => $firstChar,
            'lastChar' => $lastChar,
            'widths' => $widths,
            'missingWidth' => $missingWidth,
            'descriptor' => [
                'flags' => $flags,
                'ascent' => $ascent,
                'descent' => $descent,
                'capHeight' => $capHeight,
                'italicAngle' => $italicAngle,
                'stemV' => $stemV,
                'fontBBox' => $fontBBox,
                'avgWidth' => $avgWidth,
                'maxWidth' => $maxWidth,
                'missingWidth' => $missingWidth,
            ],
            'data' => $data,
        ];
    }

    /**
     * @return array<int, int>|null
     */
    private function parseCmapTable(string $cmap, int $minChar, int $maxChar): ?array
    {
        $numTables = $this->readUInt16($cmap, 2);

        if ($numTables === null) {
            return null;
        }

        $length = strlen($cmap);
        $candidates = [];

        for ($i = 0; $i < $numTables; $i++) {
            $recordOffset = 4 + ($i * 8);

            if ($recordOffset + 8 > $length) {
                return null;
            }

            $platformId = $this->readUInt16($cmap, $recordOffset) ?? 0;
            $encodingId = $this->readUInt16($cmap, $recordOffset + 2) ?? 0;
            $subtableOffset = $this->readUInt32($cmap, $recordOffset + 4);

            if ($subtableOffset === null || $subtableOffset >= $length) {
                continue;
            }

            $format = $this->readUInt16($cmap, $subtableOffset);

            if ($format === null) {
                continue;
            }

            $subtableLength = $format === 12
                ? $this->readUInt32($cmap, $subtableOffset + 4)
                : $this->readUInt16($cmap, $subtableOffset + 2);

            if ($subtableLength === null || $subtableOffset + $subtableLength > $length) {
                continue;
            }

            $priority = $this->determineCmapPriority($platformId, $encodingId, (int) $format);

            if ($priority === null) {
                continue;
            }

            $candidates[] = [
                'priority' => $priority,
                'format' => (int) $format,
                'data' => substr($cmap, $subtableOffset, $subtableLength),
            ];
        }

        usort($candidates, static fn (array $a, array $b): int => $a['priority'] <=> $b['priority']);

        foreach ($candidates as $candidate) {
            if ($candidate['format'] === 4) {
                $map = $this->parseCmapFormat4($candidate['data'], $minChar, $maxChar);
            } elseif ($candidate['format'] === 12) {
                $map = $this->parseCmapFormat12($candidate['data'], $minChar, $maxChar);
            } else {
                $map = null;
            }

            if ($map !== null) {
                return $map;
            }
        }

        return null;
    }

    private function determineCmapPriority(int $platformId, int $encodingId, int $format): ?int
    {
        if ($platformId === 3 && ($encodingId === 1 || $encodingId === 10)) {
            return $format === 12 ? 1 : 0;
        }

        if ($platformId === 0) {
            return $format === 12 ? 3 : 2;
        }

        if ($platformId === 1 && $encodingId === 0) {
            return 4;
        }

        return null;
    }

    /**
     * @return array<int, int>|null
     */
    private function parseCmapFormat4(string $subtable, int $minChar, int $maxChar): ?array
    {
        $length = strlen($subtable);

        if ($length < 16) {
            return null;
        }

        $segCountX2 = $this->readUInt16($subtable, 6);

        if ($segCountX2 === null || $segCountX2 === 0) {
            return null;
        }

        $segCount = (int) ($segCountX2 / 2);
        $endCountOffset = 14;
        $startCountOffset = $endCountOffset + (2 * $segCount) + 2;
        $idDeltaOffset = $startCountOffset + (2 * $segCount);
        $idRangeOffsetOffset = $idDeltaOffset + (2 * $segCount);
        $glyphArrayOffset = $idRangeOffsetOffset + (2 * $segCount);

        if ($glyphArrayOffset > $length) {
            return null;
        }

        $map = [];

        for ($segment = 0; $segment < $segCount; $segment++) {
            $endCode = $this->readUInt16($subtable, $endCountOffset + (2 * $segment));
            $startCode = $this->readUInt16($subtable, $startCountOffset + (2 * $segment));
            $idDelta = $this->readInt16($subtable, $idDeltaOffset + (2 * $segment));
            $idRangeOffset = $this->readUInt16($subtable, $idRangeOffsetOffset + (2 * $segment));

            if ($endCode === null || $startCode === null || $idDelta === null || $idRangeOffset === null) {
                return null;
            }

            $start = max($minChar, $startCode);
            $end = min($maxChar, $endCode);

            if ($start > $end) {
                continue;
            }

            for ($code = $start; $code <= $end; $code++) {
                if ($idRangeOffset === 0) {
                    $glyphIndex = ($code + $idDelta) & 0xFFFF;
                } else {
                    $rangeOffsetAddress = $idRangeOffsetOffset + (2 * $segment);
                    $glyphIndexOffset = $rangeOffsetAddress + $idRangeOffset + (2 * ($code - $startCode));

                    if ($glyphIndexOffset >= $length) {
                        continue;
                    }

                    $glyphIndex = $this->readUInt16($subtable, $glyphIndexOffset);

                    if ($glyphIndex === null || $glyphIndex === 0) {
                        continue;
                    }

                    $glyphIndex = ($glyphIndex + $idDelta) & 0xFFFF;
                }

                if ($glyphIndex === 0) {
                    continue;
                }

                $map[$code] = $glyphIndex;
            }
        }

        return $map;
    }

    /**
     * @return array<int, int>|null
     */
    private function parseCmapFormat12(string $subtable, int $minChar, int $maxChar): ?array
    {
        if (strlen($subtable) < 16) {
            return null;
        }

        $groupCount = $this->readUInt32($subtable, 12);

        if ($groupCount === null) {
            return null;
        }

        $map = [];

        for ($i = 0; $i < $groupCount; $i++) {
            $offset = 16 + ($i * 12);

            if ($offset + 12 > strlen($subtable)) {
                return null;
            }

            $startChar = $this->readUInt32($subtable, $offset);
            $endChar = $this->readUInt32($subtable, $offset + 4);
            $startGlyph = $this->readUInt32($subtable, $offset + 8);

            if ($startChar === null || $endChar === null || $startGlyph === null) {
                return null;
            }

            $start = max($minChar, (int) $startChar);
            $end = min($maxChar, (int) $endChar);

            if ($start > $end) {
                continue;
            }

            for ($code = $start; $code <= $end; $code++) {
                $map[$code] = (int) ($startGlyph + ($code - $startChar));
            }
        }

        return $map;
    }

    private function extractPostScriptName(string $nameTable): ?string
    {
        $count = $this->readUInt16($nameTable, 2);
        $stringOffset = $this->readUInt16($nameTable, 4);

        if ($count === null || $stringOffset === null) {
            return null;
        }

        $length = strlen($nameTable);
        $best = null;

        for ($i = 0; $i < $count; $i++) {
            $recordOffset = 6 + ($i * 12);

            if ($recordOffset + 12 > $length) {
                return null;
            }

            $platformId = $this->readUInt16($nameTable, $recordOffset) ?? 0;
            $encodingId = $this->readUInt16($nameTable, $recordOffset + 2) ?? 0;
            $languageId = $this->readUInt16($nameTable, $recordOffset + 4) ?? 0;
            $nameId = $this->readUInt16($nameTable, $recordOffset + 6) ?? 0;
            $valueLength = $this->readUInt16($nameTable, $recordOffset + 8);
            $valueOffset = $this->readUInt16($nameTable, $recordOffset + 10);

            if ($nameId !== 6 || $valueLength === null || $valueOffset === null) {
                continue;
            }

            $start = $stringOffset + $valueOffset;

            if ($start + $valueLength > $length) {
                continue;
            }

            $raw = substr($nameTable, $start, $valueLength);
            $decoded = null;

            if ($platformId === 3) {
                $decoded = iconv('UTF-16BE', 'UTF-8//IGNORE', $raw);

                if ($decoded === false) {
                    $decoded = null;
                }
            } elseif ($platformId === 1 && $encodingId === 0) {
                $decoded = iconv('MACINTOSH', 'UTF-8//IGNORE', $raw);

                if ($decoded === false) {
                    $decoded = null;
                }
            }

            if ($decoded === null || $decoded === '') {
                continue;
            }

            $priority = ($platformId === 3 && $encodingId === 1 && $languageId === 0x0409) ? 0 : ($platformId === 3 ? 1 : 2);

            if ($best === null || $priority < $best['priority']) {
                $best = [
                    'priority' => $priority,
                    'value' => $decoded,
                ];
            }
        }

        return $best['value'] ?? null;
    }

    private function sanitizeFontName(string $name): string
    {
        $name = str_replace(' ', '-', $name);
        $name = preg_replace('/[^A-Za-z0-9\-]+/', '', $name) ?? '';

        if ($name === '') {
            return 'CustomFont';
        }

        if (! preg_match('/^[A-Za-z]/', $name)) {
            $name = 'F' . $name;
        }

        return $name;
    }

    private function readUInt16(string $data, int $offset): ?int
    {
        if ($offset < 0 || $offset + 2 > strlen($data)) {
            return null;
        }

        $value = unpack('n', substr($data, $offset, 2));

        if (! is_array($value)) {
            return null;
        }

        return (int) $value[1];
    }

    private function readInt16(string $data, int $offset): ?int
    {
        $value = $this->readUInt16($data, $offset);

        if ($value === null) {
            return null;
        }

        if ($value >= 0x8000) {
            $value -= 0x10000;
        }

        return $value;
    }

    private function readUInt32(string $data, int $offset): ?int
    {
        if ($offset < 0 || $offset + 4 > strlen($data)) {
            return null;
        }

        $value = unpack('N', substr($data, $offset, 4));

        if (! is_array($value)) {
            return null;
        }

        return (int) $value[1];
    }

    private function readInt32(string $data, int $offset): ?int
    {
        $value = $this->readUInt32($data, $offset);

        if ($value === null) {
            return null;
        }

        if ($value >= 0x80000000) {
            $value -= 0x100000000;
        }

        return $value;
    }

    private function readFixed(string $data, int $offset): ?float
    {
        $value = $this->readInt32($data, $offset);

        if ($value === null) {
            return null;
        }

        return $value / 65536;
    }

    public function render(): string
    {
        $this->ensurePageExists();

        $objectNumber = 1;
        $offsets = [];

        $catalogNumber = $objectNumber++;
        $pagesNumber = $objectNumber++;

        $fontDescriptorNumber = null;
        $fontFileNumber = null;

        if ($this->trueTypeFont !== null) {
            $fontDescriptorNumber = $objectNumber++;
            $fontFileNumber = $objectNumber++;
        }

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

        if ($this->trueTypeFont !== null && $fontDescriptorNumber !== null && $fontFileNumber !== null) {
            $fontData = $this->trueTypeFont['data'];
            $fontLength = strlen($fontData);
            $fontStream = sprintf("<< /Length %d >>\nstream\n", $fontLength);
            $fontStream .= $fontData;

            if (! str_ends_with($fontData, "\n")) {
                $fontStream .= "\n";
            }

            $fontStream .= "endstream\n";
            $addObject($fontFileNumber, $fontStream);

            $descriptor = $this->trueTypeFont['descriptor'];
            $bbox = implode(' ', array_map(static fn (int $value): string => sprintf('%.0f', $value), $descriptor['fontBBox']));
            $descriptorObject = sprintf(
                '<< /Type /FontDescriptor /FontName /%s /Flags %d /Ascent %.0f /Descent %.0f /CapHeight %.0f /ItalicAngle %.2f /StemV %.0f /AvgWidth %.0f /MaxWidth %.0f /MissingWidth %.0f /FontBBox [%s] /FontFile2 %d 0 R >>',
                $this->trueTypeFont['name'],
                $descriptor['flags'],
                $descriptor['ascent'],
                $descriptor['descent'],
                $descriptor['capHeight'],
                $descriptor['italicAngle'],
                $descriptor['stemV'],
                $descriptor['avgWidth'],
                $descriptor['maxWidth'],
                $descriptor['missingWidth'],
                $bbox,
                $fontFileNumber,
            );
            $addObject($fontDescriptorNumber, $descriptorObject);

            $widths = implode(' ', array_map(static fn (int $width): string => (string) $width, $this->trueTypeFont['widths']));
            $fontObject = sprintf(
                '<< /Type /Font /Subtype /TrueType /BaseFont /%s /FirstChar %d /LastChar %d /Widths [%s] /Encoding /%s /MissingWidth %d /FontDescriptor %d 0 R >>',
                $this->trueTypeFont['name'],
                $this->trueTypeFont['firstChar'],
                $this->trueTypeFont['lastChar'],
                $widths,
                $this->fontEncoding,
                $this->trueTypeFont['missingWidth'],
                $fontDescriptorNumber,
            );
            $addObject($fontNumber, $fontObject);
        } else {
            $addObject($fontNumber, sprintf('<< /Type /Font /Subtype /Type1 /BaseFont /%s >>', $this->fontBaseName));
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

            $resourceParts = [sprintf('/Font << /%s %d 0 R >>', $this->fontResourceName, $fontNumber)];

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

    private function encodeText(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);

        if ($encoded === false) {
            return $text;
        }

        return $encoded;
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
