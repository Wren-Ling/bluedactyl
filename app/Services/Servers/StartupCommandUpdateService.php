<?php

namespace Pterodactyl\Services\Servers;

use Pterodactyl\Models\Server;
use Pterodactyl\Facades\Activity;
use Illuminate\Database\ConnectionInterface;
use Pterodactyl\Repositories\Wings\DaemonServerRepository;

class StartupCommandUpdateService
{
    public function __construct(
        private ConnectionInterface $connection,
        private DaemonServerRepository $daemonServerRepository,
    ) {
    }

    /**
     * Updates the startup command for a server and syncs the configuration with Wings.
     *
     * @throws \Throwable
     */
    public function handle(Server $server, string $startup): Server
    {
        $original = $server->startup;

        $server = $this->connection->transaction(function () use ($server, $startup, $original) {
            $server->update(['startup' => $startup]);

            // Log the activity
            Activity::event('server:startup.command')
                ->subject($server)
                ->property([
                    'old' => $original,
                    'new' => $startup,
                ])
                ->log();

            return $server->refresh();
        });

        // Attempt to sync with Wings daemon, but don't break the request if it fails.
        // The sync can time out when Wings is busy (e.g. installing), and we don't
        // want that to prevent the database update from completing.
        try {
            $this->daemonServerRepository->setServer($server)->sync();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to sync startup command to Wings', [
                'server' => $server->uuid,
                'error' => $e->getMessage(),
            ]);
        }

        return $server;
    }
}