<?php

namespace Modules\ApiExplorer\Services;

class EnvironmentParser
{
    /**
     * Parse the vars { ... } format into key-value pairs.
     *
     * @return array<string, string>
     */
    public function parse(string $contents): array
    {
        // Match the block between "vars {" and closing "}"
        if (! preg_match('/vars\s*\{([^}]*)\}/s', $contents, $matches)) {
            return [];
        }

        $vars = [];
        foreach (explode("\n", $matches[1]) as $line) {
            $line = trim($line);

            // Skip empty lines and comments
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Split on first ": " (to allow colons in values)
            $parts = explode(': ', $line, 2);
            if (count($parts) === 2) {
                $vars[trim($parts[0])] = trim($parts[1]);
            }
        }

        return $vars;
    }

    /**
     * Parse the baseUrl line from the environment file.
     */
    public function parseBaseUrl(string $contents): ?string
    {
        if (! preg_match('/^baseUrl:\s*(.+)$/m', $contents, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    /**
     * Format key-value pairs into the vars { ... } format.
     *
     * @param  array<string, string>  $vars
     */
    public function format(?string $baseUrl, array $vars): string
    {
        $lines = [];

        if ($baseUrl) {
            $lines[] = "baseUrl: {$baseUrl}";
            $lines[] = '';
        }

        $lines[] = 'vars {';
        foreach ($vars as $key => $value) {
            $lines[] = "  {$key}: {$value}";
        }
        $lines[] = '}';

        return implode("\n", $lines);
    }
}
