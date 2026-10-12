<?php

/**
 * Stub Wings daemon for previewing the panel AI assistant's file features.
 *
 * Answers just the two endpoints the AI assistant reaches for
 * (`list-directory` and `contents`), reading from a sandbox volume directory so
 * the real DaemonFileRepository -> Guzzle -> daemon path is exercised without
 * installing Wings. Any other Wings route returns 404 with a JSON body.
 *
 * Usage: php tools/mock-wings-daemon.php [port] [volume-root] [expected-token]
 */

$port = (int) ($argv[1] ?? 8080);
$root = rtrim((string) ($argv[2] ?? '/tmp/mock-wings/volumes'), '/');
$expectedToken = (string) ($argv[3] ?? '');

/**
 * Collapse a daemon-relative path into segments, dropping traversal attempts so
 * nothing can climb out of the server's own volume (mirrors Wings' containment).
 */
$segmentsFor = function (string $path): array {
    $segments = [];
    foreach (explode('/', str_replace('\\', '/', $path)) as $segment) {
        if ($segment === '' || $segment === '.') {
            continue;
        }

        if ($segment === '..') {
            array_pop($segments);
            continue;
        }

        $segments[] = $segment;
    }

    return $segments;
};

/**
 * @return array{status: int, headers: array<string, string>, body: string}
 */
$handle = function (string $method, string $path, array $query, array $headers) use ($root, $expectedToken, $segmentsFor): array {
    $json = fn (int $status, array $payload): array => [
        'status' => $status,
        'headers' => ['Content-Type' => 'application/json'],
        'body' => json_encode($payload, JSON_UNESCAPED_SLASHES),
    ];

    // Wings authenticates every request with the node's daemon token.
    $token = '';
    if (isset($headers['authorization']) && preg_match('/^Bearer\s+(.+)$/i', $headers['authorization'], $m)) {
        $token = $m[1];
    }

    if ($expectedToken !== '' && !hash_equals($expectedToken, $token)) {
        return $json(401, ['error' => 'Unauthenticated']);
    }

    // /api/servers/{uuid}/files/{action}
    if (!preg_match('#^/api/servers/([0-9a-f-]{36})/files/(list-directory|contents)$#i', $path, $matches)) {
        return $json(404, ['error' => 'Not found']);
    }

    $uuid = $matches[1];
    $action = $matches[2];

    // Each server's files live under its own volume directory.
    $base = $root . '/' . $uuid;
    $resolve = fn (string $path): string => $base . ($segmentsFor($path) === []
        ? ''
        : '/' . implode('/', $segmentsFor($path)));

    if ($action === 'list-directory') {
        $directory = (string) ($query['directory'] ?? '/');
        $absolute = $resolve($directory);

        if (!is_dir($absolute)) {
            return $json(404, ['error' => 'The requested directory does not exist.']);
        }

        $entries = [];
        foreach (scandir($absolute) ?: [] as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }

            $full = $absolute . '/' . $name;
            $entries[] = [
                'name' => $name,
                'file' => is_file($full),
                'size' => is_file($full) ? (int) filesize($full) : 0,
                'directory' => is_dir($full),
                'modified_at' => gmdate('Y-m-d\TH:i:s\Z', (int) filemtime($full)),
            ];
        }

        return [
            'status' => 200,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode($entries, JSON_UNESCAPED_SLASHES),
        ];
    }

    // contents
    $file = (string) ($query['file'] ?? '');
    $absolute = $resolve($file);

    if (!is_file($absolute)) {
        return $json(404, ['error' => 'The requested file could not be found.']);
    }

    $body = (string) file_get_contents($absolute);

    return [
        'status' => 200,
        'headers' => ['Content-Type' => 'text/plain', 'Content-Length' => (string) strlen($body)],
        'body' => $body,
    ];
};

$server = stream_socket_server("tcp://127.0.0.1:{$port}", $errno, $errstr);

if ($server === false) {
    fwrite(STDERR, "Could not bind port {$port}: {$errstr}\n");
    exit(1);
}

fwrite(STDOUT, "mock wings listening on 127.0.0.1:{$port}, volume root {$root}\n");

while (true) {
    $connection = @stream_socket_accept($server, -1);
    if ($connection === false) {
        continue;
    }

    // Read the request head (we only need the request line and headers).
    $requestLine = '';
    $headers = [];
    while (($line = fgets($connection)) !== false) {
        $line = rtrim($line, "\r\n");
        if ($line === '') {
            break;
        }

        if ($requestLine === '') {
            $requestLine = $line;
        } elseif (str_contains($line, ':')) {
            [$name, $value] = explode(':', $line, 2);
            $headers[strtolower(trim($name))] = trim($value);
        }
    }

    $parts = explode(' ', $requestLine);
    $method = $parts[0] ?? 'GET';
    $target = $parts[1] ?? '/';

    $query = [];
    $path = $target;
    if (str_contains($target, '?')) {
        [$path, $rawQuery] = explode('?', $target, 2);
        parse_str($rawQuery, $query);
    }

    $response = $handle($method, $path, $query, $headers);

    // Log after the response is decided but before writing it, so the log line
    // never pollutes the socket output.
    fwrite(STDOUT, sprintf("%s %s -> %d (%d bytes)\n", $method, $target, $response['status'], strlen($response['body'])));

    $reason = [200 => 'OK', 401 => 'Unauthorized', 404 => 'Not Found', 500 => 'Internal Server Error'][$response['status']] ?? 'OK';

    $head = sprintf("HTTP/1.1 %d %s\r\nConnection: close\r\n", $response['status'], $reason);
    foreach ($response['headers'] as $name => $value) {
        $head .= "{$name}: {$value}\r\n";
    }
    $head .= "\r\n";

    fwrite($connection, $head . $response['body']);
    fclose($connection);
}
