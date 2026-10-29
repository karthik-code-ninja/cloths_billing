<?php

namespace App\Services;

class EscPosService
{
    // ESC/POS Commands
    public const ESC = "\x1B";

    public const GS = "\x1D";

    public const FS = "\x1C";

    // Text Alignment
    public const ALIGN_LEFT = "\x1B\x61\x00";

    public const ALIGN_CENTER = "\x1B\x61\x01";

    public const ALIGN_RIGHT = "\x1B\x61\x02";

    // Font styles
    public const TEXT_NORMAL = "\x1B\x21\x00";

    public const TEXT_BOLD_ON = "\x1B\x45\x01";

    public const TEXT_BOLD_OFF = "\x1B\x45\x00";

    public const TEXT_DOUBLE_HEIGHT = "\x1B\x21\x10";

    public const TEXT_DOUBLE_WIDTH = "\x1B\x21\x20";

    public const TEXT_DOUBLE_SIZE = "\x1B\x21\x30";

    public const TEXT_UNDERLINE_ON = "\x1B\x2D\x01";

    public const TEXT_UNDERLINE_OFF = "\x1B\x2D\x00";

    // Paper Cut
    public const CUT_FULL = "\x1D\x56\x00";

    public const CUT_PARTIAL = "\x1D\x56\x01";

    public const CUT_FEED_AND_CUT = "\x1D\x56\x42\x03"; // Feed 3 lines and cut

    // Cash Drawer Kick
    public const CASH_DRAWER_PIN2 = "\x1B\x70\x00\x19\xFA"; // pulse 25ms on, 250ms off

    public const CASH_DRAWER_PIN5 = "\x1B\x70\x01\x19\xFA";

    // Initialize
    public const INIT = "\x1B\x40";

    /**
     * Format a line with left text and right text padded to exact columns (e.g. 32 or 42).
     */
    public function formatTwoColumns(string $left, string $right, int $columns = 42): string
    {
        $leftLen = mb_strlen($left);
        $rightLen = mb_strlen($right);

        if ($leftLen + $rightLen >= $columns) {
            $maxLeft = max(1, $columns - $rightLen - 1);
            $left = mb_substr($left, 0, $maxLeft);
            $leftLen = mb_strlen($left);
        }

        $spaces = max(1, $columns - $leftLen - $rightLen);

        return $left.str_repeat(' ', $spaces).$right."\n";
    }

    /**
     * Generate horizontal divider line.
     */
    public function divider(string $char = '-', int $columns = 42): string
    {
        return str_repeat($char, $columns)."\n";
    }

    /**
     * Generate ESC/POS barcode (CODE128).
     */
    public function barcode(string $data, int $height = 50): string
    {
        $h = chr(min(255, max(1, $height)));
        $len = chr(strlen($data) + 2);

        // GS h <height>, GS w 2, GS H 2 (text below), GS k 73 (CODE128)
        return self::GS."h{$h}".self::GS."w\x02".self::GS."H\x02".self::GS."k\x49{$len}{B{$data}";
    }

    /**
     * Generate ESC/POS QR Code (Model 2).
     */
    public function qrCode(string $data, int $moduleSize = 4): string
    {
        $size = chr(min(16, max(1, $moduleSize)));
        $len = strlen($data) + 3;
        $pL = chr($len % 256);
        $pH = chr((int) floor($len / 256));

        $buffer = '';
        // 1. Set model
        $buffer .= self::GS.'(k'."\x04\x00\x31\x41\x32\x00";
        // 2. Set module size
        $buffer .= self::GS.'(k'."\x03\x00\x31\x43{$size}";
        // 3. Set error correction level (Level M: 49)
        $buffer .= self::GS.'(k'."\x03\x00\x31\x45\x31";
        // 4. Store data in symbol storage
        $buffer .= self::GS.'(k'."{$pL}{$pH}\x31\x50\x30{$data}";
        // 5. Print the symbol
        $buffer .= self::GS.'(k'."\x03\x00\x31\x51\x30";

        return $buffer;
    }
}
