@include('partials/admin.settings.notice')

@section('settings::nav')
    @yield('settings::notice')
    <div class="tabs w-full" id="settings-tabs">
        <nav role="tablist" aria-orientation="horizontal" class="w-full">
            <a href="{{ route('admin.settings') }}" role="tab" id="settings-tab-basic" aria-selected="{{ $activeTab === 'basic' ? 'true' : 'false' }}" tabindex="{{ $activeTab === 'basic' ? '0' : '-1' }}">General</a>
            <a href="{{ route('admin.settings.mail') }}" role="tab" id="settings-tab-mail" aria-selected="{{ $activeTab === 'mail' ? 'true' : 'false' }}" tabindex="{{ $activeTab === 'mail' ? '0' : '-1' }}">Mail</a>
            <a href="{{ route('admin.settings.captcha') }}" role="tab" id="settings-tab-captcha" aria-selected="{{ $activeTab === 'captcha' ? 'true' : 'false' }}" tabindex="{{ $activeTab === 'captcha' ? '0' : '-1' }}">Captcha</a>
            <a href="{{ route('admin.settings.domains.index') }}" role="tab" id="settings-tab-domains" aria-selected="{{ $activeTab === 'domains' ? 'true' : 'false' }}" tabindex="{{ $activeTab === 'domains' ? '0' : '-1' }}">Domains</a>
            <a href="{{ route('admin.settings.advanced') }}" role="tab" id="settings-tab-advanced" aria-selected="{{ $activeTab === 'advanced' ? 'true' : 'false' }}" tabindex="{{ $activeTab === 'advanced' ? '0' : '-1' }}">Advanced</a>
        </nav>
    </div>
@endsection
