<?php

namespace App\OpenIDConnect\Helpers;

class HydraConfigHelper
{
    public $adminUrl;
    public $rememberFor;

    public function __construct(string $adminUrl, string $rememberFor)
    {
        $this->rememberFor = $rememberFor;
        $this->adminUrl = $adminUrl;
    }
}
