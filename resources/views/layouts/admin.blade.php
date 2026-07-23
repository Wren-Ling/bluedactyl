<!DOCTYPE html>
<html lang="en" class="dark">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{{ config('app.name', 'Panel') }} - @yield('title')</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <meta name="_token" content="{{ csrf_token() }}">

  <link rel="icon" type="image/png" href="/favicons/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="/favicons/favicon.svg" />
  <link rel="shortcut icon" href="/favicons/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="/favicons/apple-touch-icon.png" />
  <meta name="apple-mobile-web-app-title" content="Pyrodactyl" />
  <link rel="manifest" href="/favicons/site.webmanifest" />
  <meta name="theme-color" content="#000000">
  <meta name="darkreader-lock">

  <script>
    (() => {
      try {
        const stored = localStorage.getItem('themeMode');
        if (stored ? stored === 'dark' : matchMedia('(prefers-color-scheme: dark)').matches) {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
      } catch (_) {}
    })();
  </script>

  @include('layouts.scripts')

  @section('scripts')
  {!! Theme::css('css/admin.css?t={cache-version}') !!}
  @show
</head>

<body>
  <aside id="sidebar" class="sidebar" data-side="left">
    <nav>
      <header>
        <a href="{{ route('index') }}" class="btn h-12 w-full justify-start p-2" data-variant="ghost" data-size="lg">
          <div class="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
            <x-icon name="server" class="size-4" />
          </div>
          <div class="grid flex-1 text-left text-sm leading-tight">
            <span class="truncate font-semibold">{{ config('app.name', 'Panel') }}</span>
            <span class="truncate text-xs text-muted-foreground">v{{ config('app.version') }}</span>
          </div>
        </a>
      </header>

      <section class="scrollbar">
        <div role="group">
          <h3>BASIC ADMINISTRATION</h3>
          <ul>
            <li><a href="{{ route('admin.index') }}" @class(['active' => Route::currentRouteName() === 'admin.index'])><x-icon name="house" class="size-4" /><span>Overview</span></a></li>
            <li><a href="{{ route('admin.settings') }}" @class(['active' => str_starts_with(Route::currentRouteName(), 'admin.settings')])><x-icon name="settings" class="size-4" /><span>Settings</span></a></li>
            <li><a href="{{ route('admin.api.index') }}" @class(['active' => str_starts_with(Route::currentRouteName(), 'admin.api')])><x-icon name="globe" class="size-4" /><span>Application API</span></a></li>
          </ul>
        </div>

        <div role="group">
          <h3>MANAGEMENT</h3>
          <ul>
            <li><a href="{{ route('admin.databases') }}" @class(['active' => str_starts_with(Route::currentRouteName(), 'admin.databases')])><x-icon name="database" class="size-4" /><span>Databases</span></a></li>
            <li><a href="{{ route('admin.locations') }}" @class(['active' => str_starts_with(Route::currentRouteName(), 'admin.locations')])><x-icon name="globe" class="size-4" /><span>Locations</span></a></li>
            <li><a href="{{ route('admin.nodes') }}" @class(['active' => str_starts_with(Route::currentRouteName(), 'admin.nodes')])><x-icon name="hard-drive" class="size-4" /><span>Nodes</span></a></li>
            <li><a href="{{ route('admin.servers') }}" @class(['active' => str_starts_with(Route::currentRouteName(), 'admin.servers')])><x-icon name="server" class="size-4" /><span>Servers</span></a></li>
            <li><a href="{{ route('admin.users') }}" @class(['active' => str_starts_with(Route::currentRouteName(), 'admin.users')])><x-icon name="users" class="size-4" /><span>Users</span></a></li>
          </ul>
        </div>

        <div role="group">
          <h3>SERVICE MANAGEMENT</h3>
          <ul>
            <li><a href="{{ route('admin.mounts') }}" @class(['active' => str_starts_with(Route::currentRouteName(), 'admin.mounts')])><x-icon name="wand" class="size-4" /><span>Mounts</span></a></li>
            <li><a href="{{ route('admin.nests') }}" @class(['active' => str_starts_with(Route::currentRouteName(), 'admin.nests')])><x-icon name="egg" class="size-4" /><span>Nests</span></a></li>
          </ul>
        </div>
      </section>

      <footer class="flex flex-row items-center justify-start gap-2 p-2">
        <a href="{{ route('index') }}" class="btn" data-variant="ghost" data-size="icon" aria-label="Exit Admin" data-tooltip="Exit Admin" data-side="right"><x-icon name="server" class="size-5" /></a>
        <a href="{{ route('auth.logout') }}" id="logoutButton" class="btn" data-variant="ghost" data-size="icon" aria-label="Logout" data-tooltip="Logout" data-side="right"><x-icon name="log-out" class="size-5" /></a>
      </footer>
    </nav>
  </aside>

  <main id="content">
    <header class="sticky inset-x-0 top-0 z-30 flex shrink-0 items-center gap-2 border-b bg-background">
      <div class="flex h-14 w-full items-center justify-between gap-2 px-4">
        <button type="button" onclick="document.getElementById('sidebar')?.toggle()" aria-label="Toggle sidebar" class="btn" data-variant="ghost" data-size="icon-sm">
          <x-icon name="panel-left" class="size-4" />
        </button>

        <div class="flex items-center gap-2">
          <button type="button" aria-label="Toggle dark mode" onclick="window.basecoat?.theme.toggle()" class="btn" data-variant="ghost" data-size="icon-sm">
            <span class="hidden dark:block"><x-icon name="sun" class="size-4" /></span>
            <span class="block dark:hidden"><x-icon name="moon" class="size-4" /></span>
          </button>

          <a href="{{ route('account') }}" class="badge">{{ Auth::user()->username }}</a>
        </div>
      </div>
    </header>

    <div class="flex gap-10 p-4 md:p-6 xl:p-12">
      <article class="min-w-0 flex-1 wrap-break-word">
        <div class="mx-auto w-full @yield('contentWidth', 'max-w-4xl') flex-1 space-y-6">
          <header class="space-y-2">
            @section('content-header')
            @show
          </header>

          @if (count($errors) > 0)
            <div class="alert" data-variant="destructive" role="alert">
              <p class="font-medium">There was an error validating the data provided.</p>
              <ul class="mt-2 list-inside list-disc text-sm">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          @foreach (Alert::getMessages() as $type => $messages)
            @foreach ($messages as $message)
              <div class="alert" role="alert" data-variant="{{ $type === 'danger' ? 'destructive' : ($type === 'info' ? 'info' : ($type === 'success' ? 'success' : ($type === 'warning' ? 'warning' : 'info'))) }}">
                {{ $message }}
              </div>
            @endforeach
          @endforeach

          @yield('content')
        </div>
      </article>
    </div>
  </main>

  @section('footer-scripts')
  <script src="/js/keyboard.polyfill.js"></script>
  <script>keyboardeventKeyPolyfill.polyfill();</script>

  {!! Theme::js('vendor/jquery/jquery.min.js?t={cache-version}') !!}
  {!! Theme::js('js/admin/functions.js?t={cache-version}') !!}
  <script src="/js/autocomplete.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/basecoat-css@1.0.2/dist/js/basecoat.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/basecoat-css@1.0.2/dist/js/sidebar.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/basecoat-css@1.0.2/dist/js/select.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/basecoat-css@1.0.2/dist/js/popover.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/basecoat-css@1.0.2/dist/js/dropdown-menu.min.js" defer></script>

  @if(Auth::user()->root_admin)
    <script>
      document.getElementById('logoutButton')?.addEventListener('click', function (event) {
        event.preventDefault();
        if (confirm('Do you want to log out?')) {
          var form = document.createElement('form');
          form.method = 'POST';
          form.action = '{{ route('auth.logout') }}';
          var input = document.createElement('input');
          input.type = 'hidden';
          input.name = '_token';
          input.value = '{{ csrf_token() }}';
          form.appendChild(input);
          document.body.appendChild(form);
          form.submit();
        }
      });
    </script>
  @endif
  @show
</body>

</html>
