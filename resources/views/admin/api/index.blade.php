@extends('layouts.admin')

@section('title')
    @lang('admin/api.title')
@endsection

@section('contentWidth', 'max-w-none')

@section('content-header')
    <h1 class="text-xl font-bold">@lang('admin/api.header')</h1>
    <p class="text-sm text-muted-foreground">@lang('admin/api.header_subtitle')</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">@lang('admin/api.breadcrumb_admin')</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>@lang('admin/api.breadcrumb_api')</span>
    </nav>
@endsection

@section('content')
    <div class="grid min-w-0 gap-6">
        <div class="col-span-full min-w-0">
            <div class="server-list-card card min-w-0 w-full">
                <header>
                    <h3 class="text-lg font-semibold">@lang('admin/api.credentials_list')</h3>
                    <div class="card-action">
                        <a href="{{ route('admin.api.new') }}" class="btn" data-size="sm">@lang('admin/api.create_new')</a>
                    </div>
                </header>
                <section class="min-w-0">
                    <div class="table-container w-full max-w-full">
                        <table class="table w-full min-w-[760px] table-fixed">
                            <thead>
                            <tr>
                                <th class="w-[34%]">@lang('admin/api.key')</th>
                                <th class="w-[20%]">@lang('admin/api.memo')</th>
                                <th class="w-[20%]">@lang('admin/api.last_used')</th>
                                <th class="w-[20%]">@lang('admin/api.created')</th>
                                <th class="w-[6%]"></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($keys as $key)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-1">
                                            <code class="key-display" data-full="{{ $key->identifier }}{{ decrypt($key->token) }}">
                                                <span class="key-masked">{{ $key->identifier }}••••••••••••</span>
                                                <span class="key-full break-all hidden"></span>
                                            </code>
                                            <button type="button" class="btn key-toggle" data-size="icon-sm" data-variant="ghost" title="@lang('admin/api.show')">
                                                <span class="icon-eye"><x-icon name="eye" class="size-3" /></span>
                                                <span class="icon-eye-off hidden"><x-icon name="eye-off" class="size-3" /></span>
                                            </button>
                                            <button type="button" class="btn key-copy" data-size="icon-sm" data-variant="ghost" title="@lang('admin/api.copy_to_clipboard')">
                                                <x-icon name="copy" class="size-3" />
                                            </button>
                                        </div>
                                    </td>
                                    <td>{{ $key->memo }}</td>
                                    <td>
                                        @if(!is_null($key->last_used_at))
                                            {{ $key->last_used_at->format('M j, Y g:i A') }}
                                        @else
                                            &mdash;
                                        @endif
                                    </td>
                                    <td>{{ $key->created_at->format('M j, Y g:i A') }}</td>
                                    <td>
                                        <a href="#" data-action="revoke-key" data-attr="{{ $key->identifier }}">
                                            <x-icon name="trash-2" class="size-4" />
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.key-toggle').on('click', function() {
                var container = $(this).closest('td');
                var code = container.find('.key-display');
                var masked = code.find('.key-masked');
                var full = code.find('.key-full');
                var isHidden = masked.is(':visible');

                if (isHidden) {
                    full.text(code.data('full')).removeClass('hidden');
                    masked.addClass('hidden');
                    $(this).find('.icon-eye, .icon-eye-off').toggleClass('hidden');
                    $(this).attr('title', '{{ trans('admin/api.hide') }}');
                } else {
                    full.addClass('hidden');
                    masked.removeClass('hidden');
                    $(this).find('.icon-eye, .icon-eye-off').toggleClass('hidden');
                    $(this).attr('title', '{{ trans('admin/api.show') }}');
                }
            });

            $('.key-copy').on('click', function() {
                var full = $(this).closest('td').find('.key-display').data('full');
                navigator.clipboard.writeText(full).then(function() {
                    alert('{{ trans('admin/api.api_key_copied') }}');
                }).catch(function() {
                    alert('{{ trans('admin/api.api_key_copy_failed') }}');
                });
            });

            $('[data-action="revoke-key"]').click(function (event) {
                var self = $(this);
                event.preventDefault();
                if (confirm('{{ trans('admin/api.revoke_confirm') }}')) {
                    $.ajax({
                        method: 'DELETE',
                        url: '/admin/api/revoke/' + self.data('attr'),
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).done(function () {
                        alert('{{ trans('admin/api.api_key_revoked') }}');
                        self.parent().parent().slideUp();
                    }).fail(function (jqXHR) {
                        console.error(jqXHR);
                        alert('{{ trans('admin/api.revoke_error') }}');
                    });
                }
            });
        });
    </script>
@endsection
