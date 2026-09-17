<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LocalDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment('local') || DB::connection()->getDatabaseName() !== 'pyrodactyl_test_0901') {
            throw new \RuntimeException('Demo data is restricted to the local test database.');
        }

        DB::transaction(function () {
            $now = now();
            $egg = DB::table('eggs')->where('name', 'Paper')->first();
            if (!$egg) {
                throw new \RuntimeException('Seed the built-in eggs before creating demo data.');
            }
            $location = DB::table('locations')->where('short', 'local-demo')->value('id');
            $location ??= DB::table('locations')->insertGetId([
                'short' => 'local-demo', 'long' => 'Panel-only demo location; no daemon deployed.',
                'created_at' => $now, 'updated_at' => $now,
            ]);
            $nodes = [];
            for ($i = 1; $i <= 2; ++$i) {
                $fqdn = "demo-node-{$i}.invalid";
                $nodes[] = DB::table('nodes')->where('fqdn', $fqdn)->value('id')
                    ?? DB::table('nodes')->insertGetId([
                        'uuid' => (string) Str::uuid(), 'name' => "Demo Node 0{$i}",
                        'description' => 'Panel-only demo node; no real backend.',
                        'location_id' => $location, 'fqdn' => $fqdn, 'scheme' => 'https',
                        'public' => 0, 'maintenance_mode' => 1,
                        'memory' => 131072, 'disk' => 1048576,
                        'daemon_token_id' => Str::random(16), 'daemon_token' => encrypt(Str::random(64)),
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
            }
            foreach ($nodes as $index => $node) {
                for ($port = 25600; $port < 25610; ++$port) {
                    DB::table('allocations')->insertOrIgnore([
                        'node_id' => $node, 'ip' => '192.0.2.' . ($index + 1), 'port' => $port,
                        'notes' => 'Demo only; port is not bound.', 'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            }
            $password = Hash::make('TestUser0901!Local42');
            for ($i = 1; $i <= 30; ++$i) {
                $suffix = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                $username = "demouser{$suffix}";
                $user = DB::table('users')->where('username', $username)->value('id')
                    ?? DB::table('users')->insertGetId([
                        'uuid' => (string) Str::uuid(), 'username' => $username,
                        'email' => "{$username}@example.test", 'name_first' => 'Demo', 'name_last' => "User {$suffix}",
                        'password' => $password, 'language' => 'zh', 'root_admin' => 0, 'use_totp' => 0,
                        'gravatar' => 0, 'created_at' => $now, 'updated_at' => $now,
                    ]);
                if (DB::table('servers')->where('external_id', "local-demo-{$suffix}")->exists()) {
                    continue;
                }
                $node = $nodes[($i - 1) % 2];
                $allocation = DB::table('allocations')->insertGetId([
                    'node_id' => $node, 'ip' => '192.0.2.' . (($i - 1) % 2 + 1),
                    'port' => 25565 + $i - 1, 'notes' => 'Demo only; port is not bound.',
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                $uuid = (string) Str::uuid();
                $server = DB::table('servers')->insertGetId([
                    'external_id' => "local-demo-{$suffix}", 'uuid' => $uuid, 'uuidShort' => substr($uuid, 0, 8),
                    'node_id' => $node, 'owner_id' => $user, 'name' => "Demo Minecraft {$suffix}",
                    'description' => 'Panel-only demo server; no container or game process exists.',
                    'status' => 'suspended', 'skip_scripts' => 1,
                    'memory' => 1024 * (1 + $i % 4), 'swap' => 0, 'disk' => 10240, 'io' => 500, 'cpu' => 100,
                    'allocation_id' => $allocation, 'nest_id' => $egg->nest_id, 'egg_id' => $egg->id,
                    'startup' => $egg->startup, 'image' => array_values(json_decode($egg->docker_images, true))[0],
                    'allocation_limit' => 1, 'database_limit' => 0, 'backup_limit' => 0,
                    'created_at' => $now, 'updated_at' => $now,
                ]);
                DB::table('allocations')->where('id', $allocation)->update(['server_id' => $server]);
                foreach (DB::table('egg_variables')->where('egg_id', $egg->id)->get() as $variable) {
                    DB::table('server_variables')->insert([
                        'server_id' => $server, 'variable_id' => $variable->id,
                        'variable_value' => $variable->default_value,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            }
        });
    }
}
