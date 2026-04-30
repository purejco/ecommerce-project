<?php

function envValue(string $key, ?string $default = null): ?string
{
    static $loaded = false;

    if (!$loaded) {
        $envPath = dirname(__DIR__) . '/.env';

        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $line = trim($line);

                if ($line === '' || str_starts_with($line, '#')) {
                    continue;
                }

                [$envKey, $envVal] = array_pad(explode('=', $line, 2), 2, '');
                $_ENV[trim($envKey)] = trim($envVal);
            }
        }

        $loaded = true;
    }

    return $_ENV[$key] ?? $default;
}

function isProduction(): bool
{
    return envValue('APP_ENV', 'development') === 'production';
}