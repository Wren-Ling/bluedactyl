import { FitAddon } from '@xterm/addon-fit';
import { SearchAddon } from '@xterm/addon-search';
import { WebLinksAddon } from '@xterm/addon-web-links';
import { ITerminalOptions, Terminal } from '@xterm/xterm';
import '@xterm/xterm/css/xterm.css';
import debounce from 'debounce';

import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';
import { SocketEvent, SocketRequest } from '@/components/server/events';

import { ServerContext } from '@/state/server';

import useEventListener from '@/plugins/useEventListener';
import { usePermissions } from '@/plugins/usePermissions';
import { usePersistedState } from '@/plugins/usePersistedState';

const theme = {
    // background: 'rgba(0, 0, 0, 0)',
    background: '#131313',
    cursor: 'transparent',
    black: '#000000',
    red: '#E54B4B',
    green: '#9ECE58',
    yellow: '#FAED70',
    blue: '#396FE2',
    magenta: '#BB80B3',
    cyan: '#2DDAFD',
    white: '#d0d0d0',
    brightBlack: 'rgba(255, 255, 255, 0.2)',
    brightRed: '#FF5370',
    brightGreen: '#C3E88D',
    brightYellow: '#FFCB6B',
    brightBlue: '#82AAFF',
    brightMagenta: '#C792EA',
    brightCyan: '#89DDFF',
    brightWhite: '#ffffff',
    selection: '#FAF089',
};

const terminalProps: ITerminalOptions = {
    disableStdin: true,
    cursorStyle: 'underline',
    allowTransparency: true,
    fontSize: window.innerWidth < 640 ? 11 : 12,
    fontFamily: 'ui-monospace, SFMono-Regular, "SF Mono", Consolas, "Liberation Mono", Menlo, monospace',
    theme: theme,
};

const Console = () => {
    const { t } = useTranslation();
    const TERMINAL_PRELUDE = '\u001b[1m\u001b[33mcontainer@pyrodactyl~ \u001b[0m';
    const ref = useRef<HTMLDivElement>(null);
    const terminal = useMemo(
        () =>
            new Terminal({
                ...terminalProps,
                rows: window.innerWidth < 640 ? 20 : 25,
            }),
        [],
    );
    const fitAddon = new FitAddon();
    const searchAddon = new SearchAddon();
    const webLinksAddon = new WebLinksAddon();
    const { connected, instance } = ServerContext.useStoreState((state) => state.socket);
    const [canSendCommands] = usePermissions(['control.console']);
    const serverId = ServerContext.useStoreState((state) => state.server.data!.id);
    const isTransferring = ServerContext.useStoreState((state) => state.server.data!.isTransferring);
    const [history, setHistory] = usePersistedState<string[]>(`${serverId}:command_history`, []);
    const [historyIndex, setHistoryIndex] = useState(-1);

    const handleConsoleOutput = (line: string, prelude = false) =>
        terminal.writeln((prelude ? TERMINAL_PRELUDE : '') + line.replace(/(?:\r\n|\r|\n)$/im, '') + '\u001b[0m');

    const handleTransferStatus = (status: string) => {
        switch (status) {
            // Sent by either the source or target node if a failure occurs.
            case 'failure':
                terminal.writeln(TERMINAL_PRELUDE + 'Transfer has failed.\u001b[0m');
                return;
        }
    };

    const handleDaemonErrorOutput = (line: string) =>
        terminal.writeln(
            TERMINAL_PRELUDE + '\u001b[1m\u001b[41m' + line.replace(/(?:\r\n|\r|\n)$/im, '') + '\u001b[0m',
        );

    const handlePowerChangeEvent = (state: string) =>
        terminal.writeln(TERMINAL_PRELUDE + 'Server marked as ' + state + '...\u001b[0m');

    const inputRef = useRef<HTMLInputElement>(null);

    const sendCommand = useCallback(() => {
        const input = inputRef.current;
        if (!input) return;
        const command = input.value;
        if (command.length === 0) return;
        setHistory((prevHistory) => [command, ...prevHistory!].slice(0, 32));
        setHistoryIndex(-1);
        if (instance) instance.send('send command', command);
        input.value = '';
    }, [instance]);

    const handleCommandKeyDown = useCallback((e: React.KeyboardEvent<HTMLInputElement>) => {
        const input = e.currentTarget;
        if (e.key === 'ArrowUp') {
            const newIndex = Math.min(historyIndex + 1, history!.length - 1);
            setHistoryIndex(newIndex);
            input.value = history![newIndex] || '';
            e.preventDefault();
        } else if (e.key === 'ArrowDown') {
            const newIndex = Math.max(historyIndex - 1, -1);
            setHistoryIndex(newIndex);
            input.value = history![newIndex] || '';
        } else if (e.key === 'Enter' && input.value.length > 0) {
            sendCommand();
        }
    }, [historyIndex, history, sendCommand]);

    useEffect(() => {
        if (connected && ref.current && !terminal.element) {
            terminal.loadAddon(fitAddon);
            terminal.loadAddon(searchAddon);
            terminal.loadAddon(webLinksAddon);

            terminal.open(ref.current);
            fitAddon.fit();

            // Add support for capturing keys
            terminal.attachCustomKeyEventHandler((e: KeyboardEvent) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'c') {
                    document.execCommand('copy');
                    return false;
                }
                // } else if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                //     e.preventDefault();
                //     searchBar.show();
                //     return false;
                // } else if (e.key === 'Escape') {
                //     searchBar.hidden();
                // }
                return true;
            });
        }
    }, [terminal, connected]);

    useEventListener(
        'resize',
        debounce(() => {
            if (terminal.element) {
                // Update font size based on window width
                const newFontSize = window.innerWidth < 640 ? 11 : 12;
                terminal.options.fontSize = newFontSize;
                fitAddon.fit();
            }
        }, 100),
    );

    useEffect(() => {
        const listeners: Record<string, (s: string) => void> = {
            [SocketEvent.STATUS]: handlePowerChangeEvent,
            [SocketEvent.CONSOLE_OUTPUT]: handleConsoleOutput,
            [SocketEvent.INSTALL_OUTPUT]: handleConsoleOutput,
            [SocketEvent.TRANSFER_LOGS]: handleConsoleOutput,
            [SocketEvent.TRANSFER_STATUS]: handleTransferStatus,
            [SocketEvent.DAEMON_MESSAGE]: (line) => handleConsoleOutput(line, true),
            [SocketEvent.DAEMON_ERROR]: handleDaemonErrorOutput,
        };

        if (connected && instance) {
            // Do not clear the console if the server is being transferred.
            if (!isTransferring) {
                terminal.clear();
            }

            Object.keys(listeners).forEach((key: string) => {
                const listener = listeners[key];
                if (listener === undefined) {
                    return;
                }

                instance.addListener(key, listener);
            });
            instance.send(SocketRequest.SEND_LOGS);
        }

        return () => {
            if (instance) {
                Object.keys(listeners).forEach((key: string) => {
                    const listener = listeners[key];
                    if (listener === undefined) {
                        return;
                    }

                    instance.removeListener(key, listener);
                });
            }
        };
    }, [connected, instance]);

    return (
        <div className='bg-muted/30 border border-border rounded-xl hover:border-border/40 transition-all duration-150 overflow-hidden'>
            <div className='relative'>
                <SpinnerOverlay visible={!connected} size={'large'} />
                <div className='bg-[#131313] min-h-[280px] sm:min-h-[380px] p-3 sm:p-4 font-mono overflow-hidden'>
                    <div className='h-full w-full'>
                        <div ref={ref} className='h-full w-full' />
                    </div>
                </div>
                {canSendCommands && (
                    <div className='relative flex items-center gap-2 border-t border-border p-2'>
                        <Input
                            ref={inputRef}
                            className='w-full rounded-lg bg-muted/30 font-mono text-xs sm:text-sm h-9 focus-visible:ring-0'
                            type='text'
                            placeholder={t('console:enter_command')}
                            aria-label={t('console:console_command_input')}
                            disabled={!instance || !connected}
                            onKeyDown={handleCommandKeyDown}
                            autoCorrect='off'
                            autoCapitalize='none'
                        />
                        <Button
                            disabled={!instance || !connected}
                            onClick={sendCommand}
                        >
                            {t('console:execute')}
                        </Button>
                    </div>
                )}
            </div>
        </div>
    );
};

export default Console;
