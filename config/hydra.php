<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Hydra admin url
    |--------------------------------------------------------------------------
    */

    'admin_url' => env('HYDRA_ADMIN_URL'),
    'public_url' => env('HYDRA_PUBLIC_URL'),

    'client_id' => env('HYDRA_CLIENT_ID'),
    'client_secret' => env('HYDRA_CLIENT_SECRET'),

    'remember_for' => (int) env('HYDRA_REMEMBER_FOR', 60),
];
