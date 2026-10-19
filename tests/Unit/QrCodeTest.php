<?php

namespace Tests\Unit;

use Tests\TestCase;

require_once dirname(__DIR__, 2) . '/app/Lib/phpqrcode/phpqrcode.php';

class QrCodeTest extends TestCase
{
    public function test_png_generation_returns_a_png_image(): void
    {
        ob_start();
        \QRcode::png('QR library test', false, QR_ECLEVEL_L, 3, 2);
        $png = ob_get_clean();

        $this->assertSame("\x89PNG\r\n\x1a\n", substr($png, 0, 8));
    }

    public function test_eight_bit_and_kanji_modes_encode(): void
    {
        $encoder = new \QRcode();
        $eightBit = $encoder->encodeString8bit('native QR test', 0, QR_ECLEVEL_L);
        $kanjiData = hex2bin('93FA967B8CEA');
        $kanji = $encoder->encodeString($kanjiData, 0, QR_ECLEVEL_L, QR_MODE_KANJI, true);

        $this->assertTrue($eightBit instanceof \QRcode);
        $this->assertTrue($kanji instanceof \QRcode);
    }

    public function test_structured_append_header_and_cache_clear_work(): void
    {
        $input = new \QRinput();

        $this->assertSame(0, $input->insertStructuredAppendHeader(2, 1, 7));
        $this->assertSame(1, count($input->items));
        $this->assertSame(QR_MODE_STRUCTURE, $input->items[0]->mode);
        \QRspec::$frames = [1 => ['cached frame']];
        \QRtools::clearCache();
        $this->assertSame([], \QRspec::$frames);
    }
}
