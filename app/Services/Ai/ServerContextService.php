<?php

namespace Pterodactyl\Services\Ai;

use Throwable;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\EggVariable;
use Illuminate\Support\Facades\Log;
use Pterodactyl\Repositories\Wings\DaemonFileRepository;

/**
 * Collects server state to hand to the model.
 *
 * Everything here is derived server-side from data the requester is already
 * allowed to see. Nothing is trusted from the browser: a client cannot ask the
 * assistant to read a path it could not read itself, and no path escapes the
 * server's own data directory because the daemon resolves them.
 */
class ServerContextService
{
    /**
     * Files worth reading when diagnosing a crash, in the order they are tried.
     */
    private const DIAGNOSTIC_FILES = [
        'logs/latest.log',
        'logs/fml-server-latest.log',
        'crash-reports',
        'logs/kubejs/server.txt',
        'server.log',
        'latest.log',
        'logs/latest.log.1',
    ];

    /**
     * Hard ceiling on how much of a file is read into a prompt.
     */
    private const MAX_FILE_BYTES = 262144;

    public function __construct(
        private AiSettingsService $settings,
        private DaemonFileRepository $fileRepository,
    ) {}

    /**
     * A compact description of the server itself.
     */
    public function describeServer(Server $server): string
    {
        $server->loadMissing(['egg', 'nest', 'node', 'variables']);

        // Server::variables() yields EggVariable models carrying the resolved
        // per-server value in server_value.
        $variables = $server->variables
            ->map(fn (EggVariable $variable) => sprintf(
                '%s=%s',
                $variable->env_variable,
                $variable->server_value ?? $variable->default_value,
            ))
            ->take(30)
            ->implode(', ');

        $lines = [
            'Name: ' . $server->name,
            'UUID: ' . $server->uuid,
            'Status: ' . ($server->isSuspended() ? 'suspended' : ($server->status ?? 'unknown')),
            'Egg: ' . ($server->egg->name ?? 'unknown'),
            'Nest: ' . ($server->nest->name ?? 'unknown'),
            'Docker image: ' . $server->image,
            'Startup command: ' . $server->startup,
            'Memory limit: ' . ($server->memory === 0 ? 'unlimited' : $server->memory . ' MiB'),
            'Disk limit: ' . ($server->disk === 0 ? 'unlimited' : $server->disk . ' MiB'),
            'CPU limit: ' . ($server->cpu === 0 ? 'unlimited' : $server->cpu . '%'),
            'Startup variables: ' . ($variables !== '' ? $variables : 'none'),
        ];

        if ($server->description) {
            $lines[] = 'Description: ' . $server->description;
        }

        return implode("\n", $lines);
    }

    /**
     * A directory listing rendered as text for the prompt.
     */
    public function describeDirectory(Server $server, string $directory = '/'): string
    {
        $directory = $this->normalizePath($directory);
        $entries = $this->listDirectory($server, $directory);

        if ($entries === []) {
            return "The directory {$directory} is empty.";
        }

        $limit = $this->settings->fileListingLimit();
        $truncated = count($entries) > $limit;
        $entries = array_slice($entries, 0, $limit);

        $lines = ["Contents of {$directory}:"];
        foreach ($entries as $entry) {
            $lines[] = sprintf(
                '%s %s %s',
                $entry['is_file'] ? '-' : 'd',
                $this->formatBytes($entry['size']),
                $entry['name'],
            );
        }

        if ($truncated) {
            $lines[] = sprintf('(listing truncated at %d entries)', $limit);
        }

        return implode("\n", $lines);
    }

    /**
     * Read a text file for the prompt.
     */
    public function describeFile(Server $server, string $path, ?int $maxBytes = null): string
    {
        $path = $this->normalizePath($path, false);

        if (!$this->isReadablePath($path)) {
            return "The file {$path} cannot be read by the assistant.";
        }

        try {
            $contents = $this->fileRepository->setServer($server)->getContent($path, $maxBytes ?? self::MAX_FILE_BYTES);
        } catch (Throwable $exception) {
            return "The file {$path} could not be read: " . $exception->getMessage();
        }

        if (trim($contents) === '') {
            return "The file {$path} is empty.";
        }

        return "Contents of {$path}:\n" . $contents;
    }

    /**
     * Build the crash diagnosis payload: recent console output plus the log tail.
     *
     * @param  string[]  $consoleLines
     */
    public function buildCrashReport(Server $server, array $consoleLines): string
    {
        $sections = ['## Server configuration', $this->describeServer($server)];

        $console = $this->trimConsole($consoleLines);
        if ($console !== '') {
            $sections[] = "## Recent console output\n```\n{$console}\n```";
        }

        $log = $this->readCrashLog($server);
        if ($log !== null) {
            $sections[] = $log;
        }

        return implode("\n\n", $sections);
    }

    /**
     * Try the known log locations until one yields content.
     */
    public function readCrashLog(Server $server): ?string
    {
        foreach (self::DIAGNOSTIC_FILES as $candidate) {
            // Skip the directory-style entries, they are handled by the caller.
            if (!str_contains($candidate, '.')) {
                continue;
            }

            try {
                $contents = $this->fileRepository->setServer($server)->getContent($candidate, self::MAX_FILE_BYTES);
            } catch (Throwable) {
                continue;
            }

            if (trim($contents) !== '') {
                // Keep the tail: crashes are at the end of the file.
                $contents = $this->tail($contents, 400);

                return "## Log file ({$candidate})\n```\n{$contents}\n```";
            }
        }

        return null;
    }

    /**
     * Newest crash report, when the egg writes them.
     */
    public function readLatestCrashReport(Server $server): ?string
    {
        try {
            $entries = $this->listDirectory($server, '/crash-reports');
        } catch (Throwable) {
            return null;
        }

        $reports = collect($entries)
            ->filter(fn (array $entry) => $entry['is_file'] && str_ends_with(strtolower($entry['name']), '.txt'))
            ->sortByDesc('modified_at')
            ->values();

        if ($reports->isEmpty()) {
            return null;
        }

        $newest = $reports->first();

        return $this->describeFile($server, '/crash-reports/' . $newest['name'], self::MAX_FILE_BYTES);
    }

    /**
     * @return array<int, array{name: string, is_file: bool, size: int, modified_at: ?string}>
     */
    private function listDirectory(Server $server, string $directory): array
    {
        $raw = $this->fileRepository->setServer($server)->getDirectory($directory);

        $entries = [];
        foreach ($raw as $entry) {
            if (!is_array($entry) || !isset($entry['name'])) {
                continue;
            }

            $entries[] = [
                'name' => (string) $entry['name'],
                'is_file' => (bool) ($entry['file'] ?? false),
                'size' => (int) ($entry['size'] ?? 0),
                'modified_at' => $entry['modified_at'] ?? null,
            ];
        }

        usort($entries, fn (array $a, array $b) => $a['is_file'] === $b['is_file']
            ? strcasecmp($a['name'], $b['name'])
            : ($a['is_file'] ? 1 : -1));

        return $entries;
    }

    /**
     * @param  string[]  $lines
     */
    private function trimConsole(array $lines): string
    {
        $lines = array_values(array_filter(array_map(
            fn ($line) => rtrim((string) $line, "\r\n"),
            $lines,
        ), fn (string $line) => $line !== ''));

        $limit = $this->settings->consoleBufferLines();

        if (count($lines) > $limit) {
            $lines = array_slice($lines, -$limit);
        }

        return implode("\n", $lines);
    }

    /**
     * Keep only the last N lines of a blob of text.
     */
    private function tail(string $contents, int $lines): string
    {
        $parts = preg_split('/\r\n|\r|\n/', $contents) ?: [];

        if (count($parts) <= $lines) {
            return $contents;
        }

        return implode("\n", array_slice($parts, -$lines));
    }

    private function normalizePath(string $path, bool $isDirectory = true): string
    {
        $path = '/' . ltrim(str_replace('\\', '/', $path), '/');

        // Collapse traversal attempts before they reach the daemon.
        $segments = [];
        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($segments);
                continue;
            }

            $segments[] = $segment;
        }

        $normalized = '/' . implode('/', $segments);

        return $normalized === '/' && !$isDirectory ? '/' : $normalized;
    }

    /**
     * Refuse paths that are clearly not server diagnostics.
     */
    private function isReadablePath(string $path): bool
    {
        $lower = strtolower($path);

        foreach (['/../', "\0"] as $needle) {
            if (str_contains($lower, $needle)) {
                return false;
            }
        }

        return true;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;

        while ($bytes >= 1024 && $index < count($units) - 1) {
            $bytes /= 1024;
            $index++;
        }

        return $index === 0 ? "{$bytes}B" : sprintf('%.1f%s', $bytes, $units[$index]);
    }
}
