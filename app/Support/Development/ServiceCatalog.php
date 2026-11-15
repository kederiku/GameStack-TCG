<?php

namespace App\Support\Development;

class ServiceCatalog
{
    /** @return array<string, array{label: string, url: string}> */
    public function links(): array
    {
        return [
            'mailpit' => ['label' => 'Mailpit', 'url' => 'http://localhost:'.config('development.mailpit_port')],
            'garage-webui' => ['label' => 'Garage Web UI', 'url' => 'http://localhost:'.config('development.garage_ui_port')],
        ];
    }
}
