<?php

namespace App\Modules\Crm\Services;

/**
 * Planning estimate for a converting plant. These are the same blank and
 * yield formulas an estimator uses on a worksheet before a dieline exists.
 *
 * Corrugated RSC (FEFCO 0201):
 *   blank length = 2 × (L + W) + glue joint
 *   blank width  = H + W          (outer flaps are half the width)
 *   kg = m² × GSM × flute take-up × (1 + waste) / 1000
 *
 * Flexible: web width and repeat from pouch size, film GSM from micron × density.
 * Labels: ups across the web, repeat down the web.
 * Mono cartons: straight-tuck blank, not a certified dieline.
 */
class PackagingEstimator
{
    /** Flute take-up on combined board, including a typical medium. */
    public const FLUTE_TAKEUP = [
        'A' => 1.53,
        'B' => 1.36,
        'C' => 1.46,
        'E' => 1.27,
        'F' => 1.22,
        'BC' => 2.82,
        'EB' => 2.63,
    ];

    /** g/cm³ used to turn a film micron into GSM. */
    public const FILM_DENSITY = [
        'PET' => 1.40,
        'MET' => 1.40,
        'METPET' => 1.40,
        'PE' => 0.92,
        'CPP' => 0.91,
        'BOPP' => 0.91,
        'NY' => 1.14,
        'AL' => 2.70,
        'PAPER' => 1.00,
    ];

    /** @param  array<string, mixed>  $input */
    public function estimate(array $input): array
    {
        $type = $input['product_type'] ?? 'corrugated';

        $physical = match ($type) {
            'flexible' => $this->flexible($input),
            'label' => $this->label($input),
            'carton' => $this->carton($input),
            default => $this->corrugated($input),
        };

        return $this->cost($input, $physical);
    }

    /** @param  array<string, mixed>  $input */
    protected function corrugated(array $input): array
    {
        $l = (float) $input['length_mm'];
        $w = (float) $input['width_mm'];
        $h = (float) $input['height_mm'];
        $joint = (float) ($input['joint_mm'] ?? 40);
        $blankL = (2 * ($l + $w)) + $joint;
        $blankW = $h + $w;
        $area = ($blankL / 1000) * ($blankW / 1000);
        $flute = strtoupper((string) ($input['flute'] ?? 'C'));
        $takeup = self::FLUTE_TAKEUP[$flute] ?? 1.46;
        $gsm = (float) ($input['gsm'] ?? 140);

        return [
            'style' => 'RSC FEFCO 0201',
            'blank_length_mm' => round($blankL, 1),
            'blank_width_mm' => round($blankW, 1),
            'area_m2' => $area,
            'gsm' => $gsm,
            'takeup' => $takeup,
            'kg_per_unit_net' => $area * $gsm * $takeup / 1000,
            'price_per_kg' => (float) ($input['price_per_kg'] ?? config('packflow.rates.board_price_per_kg')),
            'speed_per_minute' => (float) ($input['speed_per_minute'] ?? 90),
            'setup_minutes' => (float) ($input['setup_minutes'] ?? 25),
            'machine_rate' => (float) ($input['machine_rate'] ?? config('packflow.rates.default_machine_per_minute')),
            'crew' => (int) ($input['crew'] ?? 3),
        ];
    }

    /** @param  array<string, mixed>  $input */
    protected function flexible(array $input): array
    {
        $w = (float) $input['width_mm'];
        $h = (float) $input['height_mm'];
        $gusset = (float) ($input['gusset_mm'] ?? 0);
        $seal = (float) ($input['seal_mm'] ?? 12);
        $web = ($w * 2) + ($gusset * 2) + ($seal * 2);
        $repeat = $h + ($seal * 2);
        $area = ($web / 1000) * ($repeat / 1000);
        $gsm = $this->filmGsm((string) ($input['film_structure'] ?? '12PET/12MET/80PE'));

        return [
            'style' => $gusset > 0 ? 'Stand-up pouch' : 'Pillow pouch',
            'blank_length_mm' => round($web, 1),
            'blank_width_mm' => round($repeat, 1),
            'area_m2' => $area,
            'gsm' => $gsm,
            'takeup' => 1,
            'kg_per_unit_net' => $area * $gsm / 1000,
            'price_per_kg' => (float) ($input['price_per_kg'] ?? config('packflow.rates.film_price_per_kg')),
            'speed_per_minute' => (float) ($input['speed_per_minute'] ?? 80),
            'setup_minutes' => (float) ($input['setup_minutes'] ?? 90),
            'machine_rate' => (float) ($input['machine_rate'] ?? 85),
            'crew' => (int) ($input['crew'] ?? 4),
            'film_gsm' => $gsm,
        ];
    }

    /** @param  array<string, mixed>  $input */
    protected function label(array $input): array
    {
        $w = (float) $input['width_mm'];
        $h = (float) $input['height_mm'];
        $gap = (float) ($input['gap_mm'] ?? 3);
        $web = (float) ($input['web_mm'] ?? max($w + 10, 100));
        $across = max(1, (int) floor(($web + $gap) / ($w + $gap)));
        $repeat = $h + $gap;
        $area = (($w + $gap) / 1000) * ($repeat / 1000);

        return [
            'style' => 'Self-adhesive label',
            'blank_length_mm' => round($web, 1),
            'blank_width_mm' => round($repeat, 1),
            'ups' => $across,
            'area_m2' => $area,
            'gsm' => 0,
            'takeup' => 1,
            'kg_per_unit_net' => 0,
            'sqm_price' => (float) ($input['price_per_sqm'] ?? config('packflow.rates.label_stock_per_sqm')),
            'price_per_kg' => 0,
            'speed_per_minute' => (float) ($input['speed_per_minute'] ?? 60),
            'setup_minutes' => (float) ($input['setup_minutes'] ?? 40),
            'machine_rate' => (float) ($input['machine_rate'] ?? 55),
            'crew' => (int) ($input['crew'] ?? 2),
        ];
    }

    /** @param  array<string, mixed>  $input */
    protected function carton(array $input): array
    {
        $l = (float) $input['length_mm'];
        $w = (float) $input['width_mm'];
        $h = (float) $input['height_mm'];
        $blankL = (2 * ($l + $w)) + 20;
        $blankW = $h + $w;
        $area = ($blankL / 1000) * ($blankW / 1000);
        $gsm = (float) ($input['gsm'] ?? 300);

        return [
            'style' => 'Straight-tuck carton (planning blank)',
            'blank_length_mm' => round($blankL, 1),
            'blank_width_mm' => round($blankW, 1),
            'area_m2' => $area,
            'gsm' => $gsm,
            'takeup' => 1,
            'kg_per_unit_net' => $area * $gsm / 1000,
            'price_per_kg' => (float) ($input['price_per_kg'] ?? config('packflow.rates.carton_board_per_kg')),
            'speed_per_minute' => (float) ($input['speed_per_minute'] ?? 120),
            'setup_minutes' => (float) ($input['setup_minutes'] ?? 35),
            'machine_rate' => (float) ($input['machine_rate'] ?? 50),
            'crew' => (int) ($input['crew'] ?? 3),
        ];
    }

    /**
     * "12PET/12MET/80PE" → GSM. Micron × density = GSM.
     */
    public function filmGsm(string $structure): float
    {
        preg_match_all('/(\d+(?:\.\d+)?)\s*([A-Z]+)/i', strtoupper($structure), $matches, PREG_SET_ORDER);
        $gsm = 0.0;

        foreach ($matches as $part) {
            $micron = (float) $part[1];
            $family = $part[2];
            $density = self::FILM_DENSITY[$family] ?? 1.0;
            $gsm += $micron * $density;
        }

        return $gsm > 0 ? round($gsm, 2) : 100.0;
    }

    /** @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $physical
     * @return array<string, mixed>
     */
    protected function cost(array $input, array $physical): array
    {
        $qty = max(1, (float) ($input['quantity'] ?? 1));
        $waste = ((float) ($input['waste_percent'] ?? $this->defaultWaste((string) ($input['product_type'] ?? 'corrugated')))) / 100;
        $kgEach = $physical['kg_per_unit_net'] * (1 + $waste);
        $kgTotal = $kgEach * $qty;

        if (($input['product_type'] ?? '') === 'label') {
            $areaEach = $physical['area_m2'] * (1 + $waste);
            $material = $areaEach * $qty * $physical['sqm_price'];
        } else {
            $material = $kgTotal * $physical['price_per_kg'];
        }

        $colors = (int) ($input['ink_colors'] ?? 0);
        $inkKg = $qty * $physical['area_m2'] * (1 + $waste) * $colors * 0.0015;
        $ink = $inkKg * (float) config('packflow.rates.ink_price_per_kg');

        $run = $qty / max(1, $physical['speed_per_minute']);
        if (($input['product_type'] ?? '') === 'label' && ($physical['ups'] ?? 1) > 1) {
            $run = ($qty / $physical['ups']) / max(1, $physical['speed_per_minute']);
        }
        $minutes = $physical['setup_minutes'] + $run;
        $machine = $minutes * $physical['machine_rate'];
        $labor = $minutes * (float) config('packflow.rates.labor_per_minute') * $physical['crew'];
        $tooling = (float) ($input['tooling_cost'] ?? 0);
        $conversion = $machine + $labor + $tooling + $ink;
        $subtotal = $material + $conversion;
        $margin = ((float) ($input['margin_percent'] ?? 18)) / 100;
        $sell = $subtotal * (1 + $margin);

        $flags = [];
        if ($waste > 0.2) {
            $flags[] = 'Waste allowance is above 20%. Check grain direction, ups, and makeready before quoting.';
        }
        if ($margin > 0.45) {
            $flags[] = 'Margin is unusually high for a repeat converting job.';
        }
        if ($margin < 0.08) {
            $flags[] = 'Margin is below a typical converting floor. Confirm board or film price.';
        }

        return array_merge($physical, [
            'quantity' => $qty,
            'waste_percent' => round($waste * 100, 2),
            'material_kg' => round($kgTotal, 3),
            'material_cost' => round($material, 2),
            'ink_cost' => round($ink, 2),
            'machine_minutes' => round($minutes, 2),
            'machine_cost' => round($machine, 2),
            'labor_cost' => round($labor, 2),
            'tooling_cost' => round($tooling, 2),
            'subtotal' => round($subtotal, 2),
            'margin_percent' => round($margin * 100, 2),
            'line_total' => round($sell, 2),
            'unit_price' => round($sell / $qty, 4),
            'flags' => $flags,
        ]);
    }

    protected function defaultWaste(string $type): float
    {
        return match ($type) {
            'flexible' => 12,
            'label' => 10,
            'carton' => 8,
            default => 8,
        };
    }
}
