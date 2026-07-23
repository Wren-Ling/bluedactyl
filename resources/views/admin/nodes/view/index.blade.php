@extends('layouts.admin')

@section('title')
  {{ $node->name }}
@endsection

@section('content-header')
  <h1 class="text-xl font-bold">{{ $node->name }}</h1>
  <p class="text-sm text-muted-foreground">A quick overview of your node.</p>
  <nav class="flex items-center gap-1 text-sm text-muted-foreground">
    <a href="{{ route('admin.index') }}">Admin</a>
    <x-icon name="chevron-right" class="size-3" />
    <a href="{{ route('admin.nodes') }}">Nodes</a>
    <x-icon name="chevron-right" class="size-3" />
    <span>{{ $node->name }}</span>
  </nav>
@endsection

@section('content')
  <div class="grid gap-6">
    <div class="col-span-full">
    <div class="tabs">
      <nav role="tablist" aria-orientation="horizontal" data-variant="line">
      <a href="{{ route('admin.nodes.view', $node->id) }}" role="tab" aria-selected="true" tabindex="0">About</a>
      <a href="{{ route('admin.nodes.view.settings', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">Settings</a>
      <a href="{{ route('admin.nodes.view.configuration', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">Configuration</a>
      <a href="{{ route('admin.nodes.view.allocation', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">Allocation</a>
      <a href="{{ route('admin.nodes.view.servers', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">Servers</a>
      </nav>
    </div>
    </div>
  </div>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="md:col-span-2">
    <div class="grid gap-6">
      <div>
      <div class="card">
        <header>
        <h3 class="text-lg font-semibold">Information</h3>
        </header>
        <section class="table-container no-padding">
        <table class="table">
          <tr>
          <td>Daemon Version</td>
          <td><code data-attr="info-version"><x-icon name="refresh-cw" class="size-4 animate-spin" /></code> (Latest:
            <code>{{ $version->getDaemon() }}</code>)
          </td>
          </tr>
          <tr>
          <td>System Information</td>
          <td data-attr="info-system"><x-icon name="refresh-cw" class="size-4 animate-spin" /></td>
          </tr>
          <tr>
          <td>Total CPU Threads</td>
          <td data-attr="info-cpus"><x-icon name="refresh-cw" class="size-4 animate-spin" /></td>
          </tr>
        </table>
        </section>
      </div>
      </div>
      @if ($node->description)
      <div>
      <div class="card" data-variant="outline">
      <header>
      Description
      </header>
      <section class="table-container">
      <pre>{{ $node->description }}</pre>
      </section>
      </div>
      </div>
    @endif
      <div>
      <div class="card" data-variant="destructive">
        <header>
        <h3 class="text-lg font-semibold">Delete Node</h3>
        </header>
        <section>
        <p class="no-margin">Deleting a node is a irreversible action and will immediately remove this node from the
          panel. There must be no servers associated with this node in order to continue.</p>
        </section>
        <footer>
        <form action="{{ route('admin.nodes.view.delete', $node->id) }}" method="POST">
          {!! csrf_field() !!}
          {!! method_field('DELETE') !!}
          <button type="submit" class="btn ml-auto" data-variant="destructive" data-size="sm" {{ ($node->servers_count < 1) ?: 'disabled' }}>Yes, Delete This Node</button>
        </form>
        </footer>
      </div>
      </div>
    </div>
    </div>
    @php
      $stats = app('Pterodactyl\Repositories\Eloquent\NodeRepository')->getUsageStatsRaw($node);
      $memoryPercent = ($stats['memory']['value'] / $stats['memory']['base_limit']) * 100;
      $diskPercent = ($stats['disk']['value'] / $stats['disk']['base_limit']) * 100;
      $allocatedMemory = humanizeSize($stats['memory']['value'] * 1024 * 1024);
      $totalMemory = humanizeSize($stats['memory']['max'] * 1024 * 1024);
      $allocatedDisk = humanizeSize($stats['disk']['value'] * 1024 * 1024);
      $totalDisk = humanizeSize($stats['disk']['max'] * 1024 * 1024);
    @endphp
    <div>
      <div class="card">
        <header>
          <h3 class="text-lg font-semibold">At-a-Glance</h3>
        </header>
        <section>
          <div class="grid gap-6">
            @if($node->maintenance_mode)
              <div class="alert" data-variant="warning" role="alert">
                <x-icon name="wrench" class="size-4" />
                <span>This node is under maintenance</span>
              </div>
            @endif
            <div class="w-full min-w-0 [&_canvas]:!max-w-full [&_.chart]:w-full">
              <canvas id="disk-chart" aria-label="Disk space allocation"></canvas>
            </div>
            <div class="w-full min-w-0 [&_canvas]:!max-w-full [&_.chart]:w-full">
              <canvas id="memory-chart" aria-label="Memory allocation"></canvas>
            </div>
          </div>
        </section>
        <div>
          <div class="card" data-variant="outline">
            <section class="text-center">
              <p class="text-sm text-muted-foreground">Total Servers</p>
              <p class="text-3xl font-bold">{{ $node->servers_count }}</p>
            </section>
          </div>
        </div>
      </div>
    </div>
  </div>
  </div>
  </div>
@endsection

@section('footer-scripts')
  @parent
  <script src="https://cdn.jsdelivr.net/npm/chart.js/dist/chart.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/basecoat-css@1.0.2/dist/js/chart.min.js" defer></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var diskPercent = {{ $diskPercent }};
      var memoryPercent = {{ $memoryPercent }};

      window.basecoat.chart('#disk-chart', {
        type: 'bar',
        labelKey: 'label',
        data: [
          { label: 'Disk', used: diskPercent, free: 100 - diskPercent },
        ],
        series: {
          used: { label: 'Used ({{ $allocatedDisk }})', color: 'var(--chart-1)' },
          free: { label: 'Free ({{ $totalDisk }})', color: 'var(--chart-2)' },
        },
        legend: true,
        options: {
          indexAxis: 'y',
          scales: {
            x: { stacked: true, min: 0, max: 100 },
            y: { stacked: true },
          },
        },
      });

      window.basecoat.chart('#memory-chart', {
        type: 'bar',
        labelKey: 'label',
        data: [
          { label: 'Memory', used: memoryPercent, free: 100 - memoryPercent },
        ],
        series: {
          used: { label: 'Used ({{ $allocatedMemory }})', color: 'var(--chart-1)' },
          free: { label: 'Free ({{ $totalMemory }})', color: 'var(--chart-2)' },
        },
        legend: true,
        options: {
          indexAxis: 'y',
          scales: {
            x: { stacked: true, min: 0, max: 100 },
            y: { stacked: true },
          },
        },
      });
    });

    function escapeHtml(str) {
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
    }

    (function getInformation() {
    $.ajax({
      method: 'GET',
      url: '/admin/nodes/view/{{ $node->id }}/system-information',
      timeout: 5000,
    }).done(function (data) {
      $('[data-attr="info-version"]').html(escapeHtml(data.version));
      $('[data-attr="info-system"]').html(escapeHtml(data.system.type) + ' (' + escapeHtml(data.system.arch) + ') <code>' + escapeHtml(data.system.release) + '</code>');
      $('[data-attr="info-cpus"]').html(data.system.cpus);
    }).fail(function (jqXHR) {

    }).always(function () {
      setTimeout(getInformation, 10000);
    });
    })();
  </script>
@endsection
