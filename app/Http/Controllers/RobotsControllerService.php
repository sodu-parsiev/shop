<?php

namespace App\Http\Controllers;

class RobotsControllerService
{
    public function contents(): string
    {
        return implode("\n", [
            'User-agent: *',
            'Disallow:',
            'Sitemap: '.route('sitemap'),
            '',
        ]);
    }
}
