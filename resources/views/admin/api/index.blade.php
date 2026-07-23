@extends('layouts.admin')

@section('title')
    Application API
@endsection

@section('content-header')
    <h1 class="text-xl font-bold">Application API</h1>
    <p class="text-sm text-muted-foreground">Control access credentials for managing this Panel via the API.</p>
    <nav class="flex items-center gap-1 text-sm text-muted-foreground">
        <a href="{{ route('admin.index') }}">Admin</a>
        <x-icon name="chevron-right" class="size-3" />
        <span>Application API</span>
    </nav>
@endsection

@section('content')
    <div class="grid gap-6">
        <div class="col-span-full">
            <div class="card">
                <header>
                    <h3 class="text-lg font-semibold">Credentials List</h3>
                    <div class="card-action">
                        <a href="{{ route('admin.api.new') }}" class="btn" data-size="sm">Create New</a>
                    </div>
                </header>
                <section>
                    <div class="table-container">
                        <table class="table">
                            <tr>
                                <th>Key</th>
                                <th>Memo</th>
                                <th>Last Used</th>
                                <th>Created</th>
                                <th></th>
                            </tr>
                            @foreach($keys as $key)
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-1">
                                            <code class="key-display" data-full="{{ $key->identifier }}{{ decrypt($key->token) }}">
                                                <span class="key-masked">{{ $key->identifier }}••••••••••••</span>
                                                <span class="key-full break-all hidden"></span>
                                            </code>
                                            <button type="button" class="btn key-toggle" data-size="icon-sm" data-variant="ghost" title="Show">
                                                <span class="icon-eye"><x-icon name="eye" class="size-3" /></span>
                                                <span class="icon-eye-off hidden"><x-icon name="eye-off" class="size-3" /></span>
                                            </button>
                                            <button type="button" class="btn key-copy" data-size="icon-sm" data-variant="ghost" title="Copy to clipboard">
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
                    $(this).attr('title', 'Hide');
                } else {
                    full.addClass('hidden');
                    masked.removeClass('hidden');
                    $(this).find('.icon-eye, .icon-eye-off').toggleClass('hidden');
                    $(this).attr('title', 'Show');
                }
            });

            $('.key-copy').on('click', function() {
                var full = $(this).closest('td').find('.key-display').data('full');
                navigator.clipboard.writeText(full).then(function() {
                    alert('API key copied to clipboard.');
                }).catch(function() {
                    alert('Failed to copy API key.');
                });
            });

            $('[data-action="revoke-key"]').click(function (event) {
                var self = $(this);
                event.preventDefault();
                if (confirm('Once this API key is revoked any applications currently using it will stop working. Are you sure?')) {
                    $.ajax({
                        method: 'DELETE',
                        url: '/admin/api/revoke/' + self.data('attr'),
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).done(function () {
                        alert('API Key has been revoked.');
                        self.parent().parent().slideUp();
                    }).fail(function (jqXHR) {
                        console.error(jqXHR);
                        alert('An error occurred while attempting to revoke this key.');
                    });
                }
            });
        });
    </script>
@endsection
