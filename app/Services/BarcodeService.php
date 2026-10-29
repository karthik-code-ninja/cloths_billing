<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class BarcodeService
{
    /**
     * In-memory cache of codes generated during current request to prevent collision before DB persist.
     *
     * @var array<string, bool>
     */
    protected static array $reservedCodes = [];

    /**
     * Generate a mathematically valid, guaranteed unique GS1 EAN-13 barcode.
     * Country prefix 890 = India (GS1 India).
     * Format: 890 (3 digits) + Manufacturer Code (4 digits) + Item Sequence (5 digits) + Check Digit (1 digit) = 13 digits.
     */
    public function generateUniqueEan13(?string $countryPrefix = null, ?string $mfgPrefix = null): string
    {
        $countryPrefix = $countryPrefix ?: (string) AppSetting::get('barcode_country_prefix', '890');
        $mfgPrefix = $mfgPrefix ?: (string) AppSetting::get('barcode_mfg_code', '2026');

        $prefix = substr($countryPrefix, 0, 3).substr(str_pad($mfgPrefix, 4, '0', STR_PAD_LEFT), 0, 4);

        // Find the maximum existing 12-digit number with this prefix
        $latest = ProductVariant::where('barcode', 'LIKE', "{$prefix}%")
            ->whereRaw('LENGTH(barcode) = 13')
            ->orderBy('barcode', 'desc')
            ->value('barcode');

        if ($latest && strlen($latest) === 13) {
            $currentSeq = (int) substr($latest, 7, 5);
            $nextSeq = $currentSeq + 1;
        } else {
            // Start sequence at 10001
            $nextSeq = 10001;
        }

        do {
            $seqStr = str_pad((string) $nextSeq, 5, '0', STR_PAD_LEFT);
            $twelveDigits = $prefix.$seqStr;
            $checkDigit = $this->calculateEan13CheckDigit($twelveDigits);
            $candidateBarcode = $twelveDigits.$checkDigit;

            $exists = isset(self::$reservedCodes[$candidateBarcode]) || ProductVariant::where('barcode', $candidateBarcode)->exists();
            if (! $exists) {
                self::$reservedCodes[$candidateBarcode] = true;

                return $candidateBarcode;
            }
            $nextSeq++;
        } while ($nextSeq < 99999);

        // Fallback with random tail if sequence overflows
        return $this->generateRandomUniqueEan13($countryPrefix);
    }

    /**
     * Generate a random unique EAN-13 barcode with valid check digit.
     */
    public function generateRandomUniqueEan13(string $countryPrefix = '890'): string
    {
        do {
            $twelve = substr($countryPrefix, 0, 3).str_pad((string) mt_rand(100000000, 999999999), 9, '0', STR_PAD_LEFT);
            $checkDigit = $this->calculateEan13CheckDigit($twelve);
            $barcode = $twelve.$checkDigit;
            $exists = ProductVariant::where('barcode', $barcode)->exists();
        } while ($exists);

        return $barcode;
    }

    /**
     * Calculate GS1 standard Modulo-10 Check Digit for 12 numeric digits.
     */
    public function calculateEan13CheckDigit(string $digits12): int
    {
        $digits = str_split(substr($digits12, 0, 12));
        $sum = 0;

        foreach ($digits as $index => $digit) {
            $val = (int) $digit;
            // Position 1-indexed: odd positions (0, 2, 4, 6, 8, 10) weight 1, even positions (1, 3, 5, 7, 9, 11) weight 3
            $weight = ($index % 2 === 0) ? 1 : 3;
            $sum += $val * $weight;
        }

        $remainder = $sum % 10;

        return ($remainder === 0) ? 0 : 10 - $remainder;
    }

    /**
     * Validate an EAN-13 barcode including length and check digit.
     */
    public function validateEan13(string $barcode): bool
    {
        $clean = trim($barcode);
        if (! ctype_digit($clean) || strlen($clean) !== 13) {
            return false;
        }

        $twelve = substr($clean, 0, 12);
        $expectedCheck = $this->calculateEan13CheckDigit($twelve);
        $actualCheck = (int) substr($clean, 12, 1);

        return $expectedCheck === $actualCheck;
    }

    /**
     * Generate a unique retail Code-128 barcode string.
     * Format: NDNA-YYMM-XXXXX (e.g. NDNA-2610-00142)
     */
    public function generateUniqueCode128(string $prefix = 'NDNA'): string
    {
        $datePart = now()->format('ym');
        $base = strtoupper($prefix).'-'.$datePart.'-';

        $latest = ProductVariant::where('barcode', 'LIKE', "{$base}%")
            ->orderBy('id', 'desc')
            ->value('barcode');

        if ($latest) {
            $parts = explode('-', $latest);
            $seq = (int) end($parts);
            $nextSeq = $seq + 1;
        } else {
            $nextSeq = 1;
        }

        do {
            $candidate = $base.str_pad((string) $nextSeq, 5, '0', STR_PAD_LEFT);
            $exists = isset(self::$reservedCodes[$candidate]) || ProductVariant::where('barcode', $candidate)->exists();
            if (! $exists) {
                self::$reservedCodes[$candidate] = true;

                return $candidate;
            }
            $nextSeq++;
        } while ($nextSeq < 999999);

        return $base.strtoupper(substr(md5(uniqid('', true)), 0, 6));
    }

    /**
     * Render a clean, standalone, high-contrast SVG vector barcode (Code-128 Subset B).
     * Works natively in all browsers, thermal label printers, and print media without any external libraries.
     */
    public function generateCode128Svg(
        string $text,
        int $barWidth = 2,
        int $height = 50,
        bool $showText = true,
        string $color = '#000000',
        string $preserveAspectRatio = 'none'
    ): string {
        $patterns = $this->getCode128Patterns();
        $code = trim($text);

        // Code 128 Subset B: Start B is 104
        $chars = [104]; // START_B
        $checksum = 104;

        $length = strlen($code);
        for ($i = 0; $i < $length; $i++) {
            $ascii = ord($code[$i]);
            // Subset B maps ASCII 32..127 to values 0..95
            $val = $ascii - 32;
            if ($val < 0 || $val > 95) {
                $val = 0; // Space fallback
            }
            $chars[] = $val;
            $checksum += $val * ($i + 1);
        }

        $checkValue = $checksum % 103;
        $chars[] = $checkValue;
        $chars[] = 106; // STOP (pattern 106)

        // Convert values to bar/space pattern string
        $binary = '';
        foreach ($chars as $val) {
            $binary .= $patterns[$val] ?? '11000100100';
        }
        // Stop character has a 2-module termination bar: '11'
        $binary .= '11';

        $totalModules = strlen($binary);
        $quietZone = 10; // Quiet zone on each side
        $svgWidth = ($totalModules + ($quietZone * 2)) * $barWidth;
        $svgHeight = $showText ? ($height + 18) : $height;

        $rects = '';
        $currentX = $quietZone * $barWidth;

        for ($i = 0; $i < $totalModules; $i++) {
            if ($binary[$i] === '1') {
                $rects .= sprintf(
                    '<rect x="%d" y="0" width="%d" height="%d" fill="%s" />',
                    $currentX,
                    $barWidth,
                    $height,
                    $color
                );
            }
            $currentX += $barWidth;
        }

        $textElement = '';
        if ($showText) {
            $escapedText = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
            $textY = $height + 14;
            $textElement = sprintf(
                '<text x="50%%" y="%d" text-anchor="middle" font-family="monospace, monospace" font-size="11" font-weight="600" fill="%s">%s</text>',
                $textY,
                $color,
                $escapedText
            );
        }

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" width="100%%" height="%d" preserveAspectRatio="%s" style="display:block;width:100%%;max-width:%dpx;">%s%s</svg>',
            $svgWidth,
            $svgHeight,
            $svgHeight,
            $preserveAspectRatio,
            $svgWidth,
            $rects,
            $textElement
        );
    }

    /**
     * Code 128 character encodings (107 patterns of 11 modules each; stop is 13 modules).
     * 1 = bar, 0 = space.
     */
    protected function getCode128Patterns(): array
    {
        return [
            0 => '11011001100', 1 => '11001101100', 2 => '11001100110', 3 => '10010011000',
            4 => '10010001100', 5 => '10001001100', 6 => '10011001000', 7 => '10011000100',
            8 => '10001100100', 9 => '11001001000', 10 => '11001000100', 11 => '11000100100',
            12 => '10110011100', 13 => '10011011100', 14 => '10011001110', 15 => '10111001100',
            16 => '10011101100', 17 => '10011100110', 18 => '11001110010', 19 => '11001011100',
            20 => '11001001110', 21 => '11011100100', 22 => '11001110100', 23 => '11101101110',
            24 => '11101001100', 25 => '11100101100', 26 => '11100100110', 27 => '11101100100',
            28 => '11100110100', 29 => '11100110010', 30 => '11011011000', 31 => '11011000110',
            32 => '11000110110', 33 => '10100011000', 34 => '10001011000', 35 => '10001000110',
            36 => '10110001000', 37 => '10001101000', 38 => '10001100010', 39 => '11010001000',
            40 => '11000101000', 41 => '11000100010', 42 => '10110111000', 43 => '10110001110',
            44 => '10001101110', 45 => '10111011000', 46 => '10111000110', 47 => '10001110110',
            48 => '11101110110', 49 => '11010001110', 50 => '11000101110', 51 => '11011101000',
            52 => '11011100010', 53 => '11011101110', 54 => '11101011000', 55 => '11101000110',
            56 => '11100010110', 57 => '11101101000', 58 => '11101100010', 59 => '11100011010',
            60 => '11101111010', 61 => '11001000010', 62 => '11110001010', 63 => '10100110000',
            64 => '10100001100', 65 => '10010110000', 66 => '10010000110', 67 => '10000101100',
            68 => '10000100110', 69 => '10110010000', 70 => '10110000100', 71 => '10011010000',
            72 => '10011000010', 73 => '10000110100', 74 => '10000110010', 75 => '11000010010',
            76 => '11001010000', 77 => '11110111010', 78 => '11000010100', 79 => '10001111010',
            80 => '10100111100', 81 => '10010111100', 82 => '10010011110', 83 => '10111100100',
            84 => '10011110100', 85 => '10011110010', 86 => '11110100100', 87 => '11110010100',
            88 => '11110010010', 89 => '11011011110', 90 => '11011110110', 91 => '11110110110',
            92 => '10101111000', 93 => '10100011110', 94 => '10001011110', 95 => '10111101000',
            96 => '10111100010', 97 => '11110101000', 98 => '11110100010', 99 => '10111011110',
            100 => '10111101110', 101 => '11101011110', 102 => '11110101110', 103 => '11010000100',
            104 => '11010010000', // START_A
            105 => '11010011100', // START_B
            106 => '11000111010', // STOP
        ];
    }
}
