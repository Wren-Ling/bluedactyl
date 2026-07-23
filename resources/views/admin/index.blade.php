@extends('layouts.admin')

@section('title')
    Administration
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">Administrative Overview</h1>
    <p class="text-sm text-muted-foreground">A quick glance at your system.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Index</span>
    </nav>
@endsection

@section('content')
    <div class="grid gap-6">
        <div class="col-span-full">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">System Information</h3>
                </header>
                <section>
                    You are running Pyrodactyl panel version <code>{{ config('app.version') }}</code>.
                </section>


            </div>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="text-center">
            <a href="https://discord.gg/UhuYKKK2uM"><button class="btn w-full" data-variant="secondary"><x-icon name="headphones" class="size-4" /> Get Help <small>(via Discord)</small></button></a>
        </div>
        <div class="text-center">
            <a href="https://pyrodactyl.dev"><button class="btn w-full"><x-icon name="link" class="size-4" /> Documentation</button></a>
        </div>
        <div class="text-center">
            <a href="https://github.com/pyrohost/pyrodactyl"><button class="btn w-full"><x-icon name="headphones" class="size-4" /> Github</button></a>
        </div>
        <div class="text-center">
            <a href="{{ $version->getDonations() }}"><button class="btn w-full"><x-icon name="dollar-sign" class="size-4" /> Support the Project</button></a>
        </div>
    </div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $(document).ready(function () {
            function formatBytes(bytes, decimals = 2) {
                if (!bytes) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return `${parseFloat((bytes / Math.pow(k, i)).toFixed(decimals))} ${sizes[i]}`;
            }

            function formatUptime(seconds) {
                const days = Math.floor(seconds / 86400);
                const hours = Math.floor((seconds % 86400) / 3600);
                const minutes = Math.floor((seconds % 3600) / 60);
                return `${days}d ${hours}h ${minutes}m`;
            }

            function updateSystemMetrics() {
                $.ajax({
                    url: '/api/application/panel/status',
                    method: 'GET',
                    success: function (data) {
                        $('#cpu-load').text(`${data.metrics.cpu.toFixed(1)}%`);
                        $('#ram-usage').html(
                            `${formatBytes(data.metrics.memory.used)} Used <br><small>of ${formatBytes(data.metrics.memory.total)}</small>`
                        );
                        $('#disk-usage').html(
                            `${formatBytes(data.metrics.disk.used)} Used <br><small>of ${formatBytes(data.metrics.disk.total)}</small>`
                        );
                        $('#uptime').text(formatUptime(data.metrics.uptime));
                    },
                    error: function (xhr) {
                        console.error('Failed to fetch system metrics:', xhr.responseText);
                    }
                });
            }

            // Initial update
            // updateSystemMetrics();

            // Update every 60 seconds
            // setInterval(updateSystemMetrics, 60000);
        });
    </script>


@endsection
