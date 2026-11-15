<?php

return [
    'snapshot_path' => '/var/run/development-status/services.json',
    'mailpit_port' => (int) env('MAILPIT_PORT', 8025),
    'garage_ui_port' => (int) env('GARAGE_UI_PORT', 3909),
];
