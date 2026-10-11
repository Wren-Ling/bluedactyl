<?php

/**
 * Local mock AI provider for previewing the panel AI assistant.
 *
 * Speaks the Anthropic Messages dialect with streaming, so the panel exercises
 * the same code path a real provider would. Point the admin settings at
 * http://127.0.0.1:11434 with this running.
 *
 * Usage: php tools/mock-ai-provider.php [port]
 */

$port = (int) ($argv[1] ?? 11434);

$router = function (string $path, string $method): array {
    if ($method === 'GET' && $path === '/v1/models') {
        return [
            'data' => [
                ['id' => 'mock-bluemoon-1', 'object' => 'model'],
                ['id' => 'mock-bluemoon-mini', 'object' => 'model'],
            ],
        ];
    }

    return [];
};

$reply = function (array $body): string {
    $system = (string) ($body['system'] ?? '');
    $messages = $body['messages'] ?? [];
    $last = '';
    foreach (array_reverse($messages) as $message) {
        if (($message['role'] ?? '') === 'user') {
            $last = (string) ($message['content'] ?? '');
            break;
        }
    }

    $isCrash = str_contains($system, 'stopped unexpectedly') || str_contains($system, 'Diagnose the cause');

    // Pull whatever evidence the panel actually sent, so the preview shows the
    // real console and file content flowing through rather than canned text.
    $consoleLines = [];
    if (preg_match('/## Recent console output\s*```\s*(.*?)```/s', $system, $matches)) {
        $consoleLines = array_values(array_filter(explode("\n", trim($matches[1]))));
    }

    $serverName = 'the server';
    if (preg_match('/^Name:\s*(.+)$/m', $system, $matches)) {
        $serverName = trim($matches[1]);
    }

    $logHint = 'No log file was attached, so this is based on console output alone.';
    if (preg_match('/## Log file \(([^)]+)\)/', $system, $matches)) {
        $logHint = "Read from `{$matches[1]}`.";
    }

    if ($isCrash) {
        $tail = implode("\n", array_slice($consoleLines, -6));

        return <<<TEXT
        **Cause** — The JVM ran out of heap while `{$serverName}` was generating terrain. The console shows `java.lang.OutOfMemoryError: Java heap space` immediately before the watchdog kicks the process.

        **Why** — The configured memory limit is being exceeded by chunk generation. This is a resource ceiling, not a corrupted world: the same seed will load fine once the heap is large enough.

        **Fix**
        1. Raise the memory limit by at least 1 GiB in the server's Startup tab.
        2. If the container cannot grow, lower `view-distance` in `server.properties` to `8`.
        3. Restart the server and confirm the heap banner reports the new maximum.

        **Prevention** — Watch heap usage after installing datapacks; terrain generation is the usual first casualty.

        {$logHint}

        Last console lines the panel sent:
        ```
        {$tail}
        ```
        TEXT;
    }

    $lower = strtolower($last);
    $attachHint = $consoleLines === []
        ? 'I do not have console output attached right now — open the console panel and I will pick it up automatically.'
        : sprintf('I can see the last %d console lines from `%s`.', count($consoleLines), $serverName);

    if (str_contains($lower, 'crash') || str_contains($lower, '崩溃') || str_contains($lower, '报错') || str_contains($lower, '错误')) {
        return "Let me look at what the server logged.\n\n{$attachHint}\n\nIf you press **Analyze the crash** I will read the console plus any `logs/latest.log` on disk and name the failing component.";
    }

    // Emit the directive form the panel's follow-up button parses, so the
    // attach-on-request flow can be exercised without a real provider.
    if (str_contains($lower, '文件') || str_contains($lower, 'file') || str_contains($lower, 'log')) {
        return <<<TEXT
        I can pull those in directly.

        The two things worth reading are `read logs/latest.log` for the recent server output and `read server.properties` for the configured limits. The `ls plugins` listing will show which plugins are loaded.
        TEXT;
    }

    return <<<TEXT
    **{$serverName}** is currently reachable and I have the console attached.

    {$attachHint}

    A few things I can do from here:
    - Diagnose a stack trace or `Exception` you paste in
    - Explain a startup variable or flag in the Startup tab
    - Walk through a mod or plugin that is failing to load

    Ask me anything — or attach a file with the button above and I will read it.
    TEXT;
};

$server = stream_socket_server("tcp://127.0.0.1:{$port}", $errno, $errstr);

if ($server === false) {
    fwrite(STDERR, "Could not bind port {$port}: {$errstr}\n");
    exit(1);
}

fwrite(STDOUT, "Mock AI provider listening on http://127.0.0.1:{$port}\n");
fwrite(STDOUT, "  POST /v1/messages   (Anthropic Messages, streaming)\n");
fwrite(STDOUT, "  GET  /v1/models\n\n");

while (true) {
    $client = @stream_socket_accept($server, -1);
    if ($client === false) {
        continue;
    }

    // Read the request head.
    $head = '';
    while (($line = fgets($client)) !== false) {
        $head .= $line;
        if (rtrim($line) === '') {
            break;
        }
    }

    [$requestLine, $headers] = array_pad(explode("\r\n", $head, 2), 2, '');
    [$method, $path] = array_pad(explode(' ', $requestLine), 3, '');

    $contentLength = 0;
    if (preg_match('/Content-Length:\s*(\d+)/i', $headers, $matches)) {
        $contentLength = (int) $matches[1];
    }

    $raw = '';
    while (strlen($raw) < $contentLength) {
        $chunk = fread($client, $contentLength - strlen($raw));
        if ($chunk === '' || $chunk === false) {
            break;
        }
        $raw .= $chunk;
    }

    $pathOnly = parse_url($path, PHP_URL_PATH) ?: '/';
    $static = $router($pathOnly, $method);

    if ($static !== []) {
        $payload = json_encode($static);
        fwrite($client, "HTTP/1.1 200 OK\r\nContent-Type: application/json\r\nContent-Length: " . strlen($payload) . "\r\nConnection: close\r\n\r\n{$payload}");
        fclose($client);
        continue;
    }

    $body = json_decode($raw, true) ?: [];
    $text = $reply($body);
    $streaming = (bool) ($body['stream'] ?? false);

    if (!$streaming) {
        $payload = json_encode([
            'id' => 'msg_mock',
            'type' => 'message',
            'role' => 'assistant',
            'content' => [['type' => 'text', 'text' => $text]],
            'stop_reason' => 'end_turn',
        ]);
        fwrite($client, "HTTP/1.1 200 OK\r\nContent-Type: application/json\r\nContent-Length: " . strlen($payload) . "\r\nConnection: close\r\n\r\n{$payload}");
        fclose($client);
        continue;
    }

    fwrite($client, "HTTP/1.1 200 OK\r\nContent-Type: text/event-stream\r\nCache-Control: no-cache\r\nConnection: close\r\n\r\n");

    $event = fn (array $data) => fwrite($client, 'event: ' . $data['type'] . "\ndata: " . json_encode($data) . "\n\n");

    $event(['type' => 'message_start', 'message' => ['id' => 'msg_mock', 'role' => 'assistant', 'content' => []]]);
    $event(['type' => 'content_block_start', 'index' => 0, 'content_block' => ['type' => 'text', 'text' => '']]);

    // Emit word by word so the panel's streaming renderer is exercised.
    foreach (preg_split('/(\s+)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE) as $token) {
        if ($token === '') {
            continue;
        }

        $event([
            'type' => 'content_block_delta',
            'index' => 0,
            'delta' => ['type' => 'text_delta', 'text' => $token],
        ]);

        usleep(18000);
    }

    $event(['type' => 'content_block_stop', 'index' => 0]);
    $event(['type' => 'message_delta', 'delta' => ['stop_reason' => 'end_turn']]);
    $event(['type' => 'message_stop']);

    fclose($client);
}
