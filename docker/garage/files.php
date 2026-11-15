<?php

// Shared by the local Docker setup scripts; never print credential contents.
function writeGarageFile(string $path, string $contents): void
{
    if (is_file($path) && file_get_contents($path) === $contents) {
        chmod($path, 0600);

        return;
    }

    $temporary = tempnam(dirname($path), '.garage-');
    if ($temporary === false) {
        throw new RuntimeException('Impossible de créer un fichier temporaire Garage.');
    }
    try {
        chmod($temporary, 0600);
        if (file_put_contents($temporary, $contents) !== strlen($contents) || ! rename($temporary, $path)) {
            throw new RuntimeException('Impossible d’enregistrer la configuration Garage.');
        }
    } finally {
        if (is_file($temporary)) {
            unlink($temporary);
        }
    }
}
