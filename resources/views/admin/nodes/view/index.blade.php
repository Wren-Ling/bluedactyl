@extends('layouts.admin')

@section('title')
  {{ $node->name }}
@endsection

@section('content-header')
  <h1 class="text-xl font-bold">{{ $node->name }}</h1>
  <p class="text-sm text-muted-foreground">@lang('admin/nodes.about.header_subtitle')</p>
  <nav class="flex items-center gap-1 text-sm text-muted-foreground">
    <a href="{{ route('admin.index') }}">@lang('admin/nodes.common.admin')</a>
    <x-icon name="chevron-right" class="size-3" />
    <a href="{{ route('admin.nodes') }}">@lang('admin/nodes.common.nodes')</a>
    <x-icon name="chevron-right" class="size-3" />
    <span>{{ $node->name }}</span>
  </nav>
@endsection

@section('content')
  <div class="grid gap-6">
    <div class="col-span-full">
    <div class="tabs">
      <nav role="tablist" aria-orientation="horizontal" data-variant="line">
      <a href="{{ route('admin.nodes.view', $node->id) }}" role="tab" aria-selected="true" tabindex="0">@lang('admin/nodes.common.about')</a>
      <a href="{{ route('admin.nodes.view.settings', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.settings')</a>
      <a href="{{ route('admin.nodes.view.configuration', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.configuration')</a>
      <a href="{{ route('admin.nodes.view.allocation', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.allocation')</a>
      <a href="{{ route('admin.nodes.view.servers', $node->id) }}" role="tab" aria-selected="false" tabindex="-1">@lang('admin/nodes.common.servers')</a>
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
        <h3 class="text-lg font-semibold">@lang('admin/nodes.about.information')</h3>
        </header>
        <section class="table-container no-padding">
        <table class="table">
          <tr>
          <td>@lang('admin/nodes.about.daemon_version')</td>
          <td><code data-attr="info-version"><x-icon name="refresh-cw" class="size-4 animate-spin" /></code> (@lang('admin/nodes.about.latest')
            <code>{{ $version->getDaemon() }}</code>)
          </td>
          </tr>
          <tr>
          <td>@lang('admin/nodes.about.system_information')</td>
          <td data-attr="info-system"><x-icon name="refresh-cw" class="size-4 animate-spin" /></td>
          </tr>
          <tr>
          <td>@lang('admin/nodes.about.total_cpu_threads')</td>
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
      @lang('admin/nodes.about.description')
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
        <h3 class="text-lg font-semibold">@lang('admin/nodes.about.delete_node')</h3>
        </header>
        <section>
        <p class="no-margin">@lang('admin/nodes.about.delete_node_help')</p>
        </section>
        <footer>
        <form action="{{ route('admin.nodes.view.delete', $node->id) }}" method="POST">
          {!! csrf_field() !!}
          {!! method_field('DELETE') !!}
          <button type="submit" class="btn ml-auto" data-variant="destructive" data-size="sm" {{ ($node->servers_count < 1) ?: 'disabled' }}>@lang('admin/nodes.about.yes_delete')</button>
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
          <h3 class="text-lg font-semibold">@lang('admin/nodes.about.at_a_glance')</h3>
        </header>
        <section>
          <div class="grid gap-6">
            @if($node->maintenance_mode)
              <div class="alert" data-variant="warning" role="alert">
                <x-icon name="wrench" class="size-4" />
                <span>@lang('admin/nodes.about.maintenance_mode')</span>
              </div>
            @endif
            <div class="w-full min-w-0 [&_canvas]:!max-w-full [&_.chart]:w-full">
              <canvas id="disk-chart" aria-label="@lang('admin/nodes.about.disk_aria_label')"></canvas>
            </div>
            <div class="w-full min-w-0 [&_canvas]:!max-w-full [&_.chart]:w-full">
              <canvas id="memory-chart" aria-label="@lang('admin/nodes.about.memory_aria_label')"></canvas>
            </div>
          </div>
        </section>
        <div>
          <div class="card" data-variant="outline">
            <section class="text-center">
              <p class="text-sm text-muted-foreground">@lang('admin/nodes.about.total_servers')</p>
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
          { label: '@lang("admin.nodes.about.disk_chart_label")', used: diskPercent, free: 100 - diskPercent },
        ],
        series: {
          used: { label: '@lang("admin.nodes.about.used", ["value" => $allocatedDisk])', color: 'var(--chart-1)' },
          free: { label: '@lang("admin.nodes.about.free", ["value" => $totalDisk])', color: 'var(--chart-2)' },
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
          { label: '@lang("admin.nodes.about.memory_chart_label")', used: memoryPercent, free: 100 - memoryPercent },
        ],
        series: {
          used: { label: '@lang("admin.nodes.about.used", ["value" => $allocatedMemory])', color: 'var(--chart-1)' },
          free: { label: '@lang("admin.nodes.about.free", ["value" => $totalMemory])', color: 'var(--chart-2)' },
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
