<?php

namespace Tests\Unit;

use App\Modules\Crm\Services\OrderOcrService;
use App\Modules\Crm\Services\PackagingEstimator;
use Tests\TestCase;

class PackagingEstimatorTest extends TestCase
{
    public function test_rsc_blank_and_board_weight(): void
    {
        $estimate = (new PackagingEstimator)->estimate([
            'product_type' => 'corrugated',
            'length_mm' => 400,
            'width_mm' => 300,
            'height_mm' => 200,
            'gsm' => 140,
            'flute' => 'C',
            'quantity' => 1000,
            'waste_percent' => 8,
            'ink_colors' => 0,
            'margin_percent' => 18,
            'tooling_cost' => 0,
        ]);

        $this->assertSame(1440.0, $estimate['blank_length_mm']);
        $this->assertSame(500.0, $estimate['blank_width_mm']);
        $this->assertEqualsWithDelta(158.941, $estimate['material_kg'], 0.05);
        $this->assertGreaterThan($estimate['material_cost'], $estimate['line_total']);
    }

    public function test_film_structure_gsm(): void
    {
        $gsm = (new PackagingEstimator)->filmGsm('12PET/12MET/80PE');

        $this->assertEqualsWithDelta(107.2, $gsm, 0.01);
    }

    public function test_plain_text_spec_is_parsed_without_a_model(): void
    {
        $parsed = (new OrderOcrService)->fromText('Need 5,000 pcs of 400 x 300 x 200 mm, B flute, 140 GSM, 3 colours');

        $this->assertSame(400.0, $parsed['length_mm']);
        $this->assertSame(300.0, $parsed['width_mm']);
        $this->assertSame(200.0, $parsed['height_mm']);
        $this->assertSame(5000.0, $parsed['quantity']);
        $this->assertSame('B', $parsed['flute']);
        $this->assertSame(140.0, $parsed['gsm']);
        $this->assertSame(3, $parsed['ink_colors']);
    }
}
