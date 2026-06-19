<?php


   function get_area_name() {
       $current_url = request()->url();
         $area = (explode('/', $current_url)[3]);
         $area = str_replace('-', '_', $area);
         return $area;
   }


if (!function_exists('numberToArabicWords')) {
    function numberToArabicWords(float $number, string $mainUnit = 'دينار', string $subUnit = 'درهم'): string
    {
        if ($number == 0) return 'صفر ' . $mainUnit;

        $intPart = (int) floor(abs($number));
        $decPart = (int) round((abs($number) - $intPart) * 100);

        $result = _arabicInteger($intPart) . ' ' . $mainUnit;

        if ($decPart > 0) {
            $result .= ' و' . _arabicInteger($decPart) . ' ' . $subUnit;
        }

        return $result;
    }
}

if (!function_exists('_arabicInteger')) {
    function _arabicInteger(int $n): string
    {
        if ($n === 0) return 'صفر';

        $ones = [
            '', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة',
            'عشرة', 'أحد عشر', 'اثنا عشر', 'ثلاثة عشر', 'أربعة عشر', 'خمسة عشر',
            'ستة عشر', 'سبعة عشر', 'ثمانية عشر', 'تسعة عشر',
        ];
        $tens     = ['', '', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
        $hundreds = ['', 'مئة', 'مئتان', 'ثلاثمئة', 'أربعمئة', 'خمسمئة', 'ستمئة', 'سبعمئة', 'ثمانمئة', 'تسعمئة'];

        $parts = [];

        // Billions
        if ($n >= 1_000_000_000) {
            $b = (int) ($n / 1_000_000_000);
            $n %= 1_000_000_000;
            $parts[] = match(true) {
                $b === 1 => 'مليار',
                $b === 2 => 'ملياران',
                $b <= 10 => _arabicInteger($b) . ' مليارات',
                default  => _arabicInteger($b) . ' مليار',
            };
        }

        // Millions
        if ($n >= 1_000_000) {
            $m = (int) ($n / 1_000_000);
            $n %= 1_000_000;
            $parts[] = match(true) {
                $m === 1 => 'مليون',
                $m === 2 => 'مليونان',
                $m <= 10 => _arabicInteger($m) . ' ملايين',
                default  => _arabicInteger($m) . ' مليون',
            };
        }

        // Thousands
        if ($n >= 1000) {
            $k = (int) ($n / 1000);
            $n %= 1000;
            $parts[] = match(true) {
                $k === 1 => 'ألف',
                $k === 2 => 'ألفان',
                $k <= 10 => _arabicInteger($k) . ' آلاف',
                default  => _arabicInteger($k) . ' ألف',
            };
        }

        // Hundreds
        if ($n >= 100) {
            $parts[] = $hundreds[(int) ($n / 100)];
            $n %= 100;
        }

        // Tens and ones
        if ($n > 0) {
            if ($n < 20) {
                $parts[] = $ones[$n];
            } else {
                $one = $n % 10;
                $ten = (int) ($n / 10);
                $parts[] = $one > 0 ? $ones[$one] . ' و' . $tens[$ten] : $tens[$ten];
            }
        }

        return implode(' و', $parts);
    }
}