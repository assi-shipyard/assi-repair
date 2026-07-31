<?php

namespace App\Helpers;

use DateTime;

class DateCheckerHelper {
    public static function isValidIndonesianDate($date) {
        $months = [
            'Januari' => 'January', 'Februari' => 'February', 'Maret' => 'March',
            'April' => 'April', 'Mei' => 'May', 'Juni' => 'June',
            'Juli' => 'July', 'Agustus' => 'August', 'September' => 'September',
            'Oktober' => 'October', 'November' => 'November', 'Desember' => 'December'
        ];

        $parts = explode(' ', $date);
        if (count($parts) !== 3) {
            return false; // Incorrect format
        }

        $day = $parts[0];
        $monthName = $parts[1];
        $year = $parts[2];

        if (!isset($months[$monthName])) {
            return false; // Invalid month name
        }

        $month = $months[$monthName];

        // Ensure the day is two digits
        $day = str_pad($day, 2, '0', STR_PAD_LEFT);

        $formattedDate = "$day $month $year";

        // Check if the formatted date is valid
        $d = DateTime::createFromFormat('d F Y', $formattedDate);
        return $d && $d->format('d F Y') === $formattedDate;
    }
}
