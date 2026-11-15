<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Tarikh\Tarikh;

class DocumentNumber
{
    /**
     * Plant documents share one counter per prefix and calendar year
     * (QT-2026-0007). The row lock keeps two clerks from taking the same number.
     */
    public static function next(string $prefix): string
    {
        $year = Tarikh::now(config('app.timezone', 'Asia/Dhaka'))->format('Y');
        $key = $prefix.'-'.$year;

        $seq = DB::transaction(function () use ($key) {
            $row = DB::table('sequences')->where('key', $key)->lockForUpdate()->first();

            if ($row === null) {
                DB::table('sequences')->insert(['key' => $key, 'value' => 1]);

                return 1;
            }

            $next = (int) $row->value + 1;
            DB::table('sequences')->where('key', $key)->update(['value' => $next]);

            return $next;
        });

        return sprintf('%s-%s-%04d', $prefix, $year, $seq);
    }
}
