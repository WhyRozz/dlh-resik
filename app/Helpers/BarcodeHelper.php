<?php

namespace App\Helpers;

class BarcodeHelper
{
    /**
     * Generate barcode ID tanpa karakter ambigu (0, 1, I, O)
     * @param int $length Panjang string acak (default: 13)
     * @param string $prefix Prefix barcode (default: 'RK')
     * @return string
     */
    public static function generate($length = 13, $prefix = 'RK')
    {
        // ✅ Karakter yang DIIZINKAN (tanpa 0, 1, I, O)
        $characters = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomIndex = random_int(0, $charactersLength - 1);
            $randomString .= $characters[$randomIndex];
        }

        return $prefix . strtoupper($randomString);
    }
}
