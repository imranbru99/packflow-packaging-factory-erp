<?php

namespace App\Support;

use Tarikh\Tarikh;

/**
 * Working calendar for a Bangladesh converting plant.
 * Weekend is Friday and Saturday. Tarikh supplies the date math;
 * the holiday list is local because the installed Tarikh build
 * does not yet ship a holiday calendar.
 */
class FactoryCalendar
{
    /** @return list<string> */
    public function holidays(int $year = 2026): array
    {
        return [
            "$year-02-21",
            "$year-03-17",
            "$year-03-26",
            "$year-04-14",
            "$year-05-01",
            "$year-08-15",
            "$year-12-16",
            "$year-12-25",
        ];
    }

    public function isWorkingDay(Tarikh|string $date): bool
    {
        $day = $date instanceof Tarikh ? $date : Tarikh::parse($date, 'Asia/Dhaka');
        $dow = (int) $day->format('N');

        if (in_array($dow, [5, 6], true)) {
            return false;
        }

        return ! in_array($day->format('Y-m-d'), $this->holidays((int) $day->format('Y')), true);
    }

    public function addWorkingDays(Tarikh|string $date, int $days): Tarikh
    {
        $cursor = $date instanceof Tarikh ? $date : Tarikh::parse($date, 'Asia/Dhaka');
        $added = 0;

        while ($added < $days) {
            $cursor = $cursor->addDays(1);
            if ($this->isWorkingDay($cursor)) {
                $added++;
            }
        }

        return $cursor;
    }
}
