<?php

namespace App\Helpers;

use Carbon\CarbonInterface;

/**
 * Date parts for the front office date chip. Thai uses the Buddhist year,
 * every other language uses the Gregorian year. Month names come from the web JSON strings.
 */
class WebDate
{
    /**
     * @return array{iso: string, day: string, month: string, year: int, label: string}
     */
    public static function chip(CarbonInterface $date): array
    {
        $isThai = app()->getLocale() === 'th';
        $year = $isThai ? $date->year + 543 : $date->year;
        $fullMonth = __web('date.full_'.$date->month);

        return [
            'iso' => $date->toDateString(),
            'day' => str_pad((string) $date->day, 2, '0', STR_PAD_LEFT),
            'month' => __web('date.short_'.$date->month),
            'year' => $year,
            'label' => $isThai ? "{$date->day} {$fullMonth} {$year}" : "{$fullMonth} {$date->day}, {$year}",
        ];
    }

    /**
     * Short written date: "13 ส.ค. 2569" in Thai (Buddhist year), "13 Aug 2026" in other languages.
     */
    public static function plain(CarbonInterface $date): string
    {
        $isThai = app()->getLocale() === 'th';
        $month = __web('date.short_'.$date->month);
        $month = $isThai ? $month : ucfirst(strtolower($month));

        return $date->day.' '.$month.' '.($isThai ? $date->year + 543 : $date->year);
    }
}
