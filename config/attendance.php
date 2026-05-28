<?php

return [
    /*
     * Prefix IP jaringan lokal WiFi carwash (MensekakStaff).
     * Sesuaikan dengan range IP router kamu.
     * Contoh: '192.168.1.' atau '10.0.0.'
     */
    'local_ip_prefix' => env('ATTENDANCE_LOCAL_IP', '192.168.1.'),

    /*
     * Jam buka carwash (format H:i:s)
     */
    'open_time' => '08:00:00',

    /*
     * Batas telat — lebih dari ini dihitung terlambat
     */
    'late_threshold' => '08:30:00',

    /*
     * Bonus per rating bintang 5 (rupiah)
     */
    'rating_bonus_amount' => env('RATING_BONUS_AMOUNT', 2000),
];
