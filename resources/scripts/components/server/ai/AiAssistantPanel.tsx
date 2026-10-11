import { FileText, FolderOpen, Send, Sparkles, Square, Trash2, TriangleAlert, WandSparkles, X } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { Textarea } from '@/components/ui/textarea';

import { takeConsoleLines } from '@/lib/ai/consoleBuffer';
import { cn } from '@/lib/utils';

import { httpErrorToHuman } from '@/api/http';
import { AiMessage, analyzeCrash, getAiDirectory, readAiFile, streamChat } from '@/api/server/ai/assistant';

import { ServerContext } from '@/state/server';

import { useFlashKey } from '@/plugins/useFlash';

import AiMarkdown from './AiMarkdown';

/**
 * Pull a fenced code block out of an assistant reply, if one is present.
 */
const extractDirectives = (text: string) => {
    const files: string[] = [];
    const directories: string[] = [];

    const pattern = /`(?:read|cat)\s+([^\s`]+)`|`(?:ls|list)\s+([^\s`]+)`/gi;

    let match: RegExpExecArray | null;
    while ((match = pattern.exec(text)) !== null) {
        if (match[1]) files.push(match[1]);
        if (match[2]) directories.push(match[2]);
    }

    return { files: files.slice(0, 3), directories: directories.slice(0, 3) };
};

interface ChatTurn extends AiMessage {
    id: string;
    pending?: boolean;
    error?: boolean;
}

interface AiAssistantPanelProps {
    visible: boolean;
    onClose: () => void;
}

const AiAssistantPanel = ({ visible, onClose }: AiAssistantPanelProps) => {
    const { t } = useTranslation();
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const status = ServerContext.useStoreState((state) => state.status.value);
    const serverName = ServerContext.useStoreState((state) => state.server.data!.name);
    const { clearFlashes, clearAndAddHttpError } = useFlashKey('ai');

    const [turns, setTurns] = useState<ChatTurn[]>([]);
    const [input, setInput] = useState('');
    const [busy, setBusy] = useState(false);
    const [attachedContext, setAttachedContext] = useState<{ label: string; content: string }[]>([]);

    const scrollRef = useRef<HTMLDivElement>(null);
    const abortRef = useRef<AbortController | null>(null);
    const idRef = useRef(0);

    const nextId = () => `turn-${(idRef.current += 1)}`;

    useEffect(() => {
        clearFlashes();
    }, [clearFlashes]);

    // Keep the newest message in view as it streams in.
    useEffect(() => {
        scrollRef.current?.scrollTo({ top: scrollRef.current.scrollHeight, behavior: 'smooth' });
    }, [turns]);

    // Abort an in-flight request when the panel closes or the page unmounts.
    useEffect(() => {
        if (!visible) abortRef.current?.abort();
    }, [visible]);

    useEffect(() => () => abortRef.current?.abort(), []);

    const consoleContext = useCallback(() => takeConsoleLines(200), []);

    const respond = useCallback(
        async (conversation: ChatTurn[], action?: 'crash_analysis') => {
            setBusy(true);
            abortRef.current?.abort();
            const controller = new AbortController();
            abortRef.current = controller;

            const placeholderId = nextId();
            setTurns([...conversation, { id: placeholderId, role: 'assistant', content: '', pending: true }]);

            const replace = (update: (turn: ChatTurn) => ChatTurn) =>
                setTurns((current) => current.map((turn) => (turn.id === placeholderId ? update(turn) : turn)));

            try {
                const lines = consoleContext();

                if (action === 'crash_analysis') {
                    const reply = await analyzeCrash(uuid, lines);
                    replace((turn) => ({ ...turn, content: reply, pending: false }));
                } else {
                    const payload = conversation.map(({ role, content }) => ({ role, content }));

                    if (attachedContext.length > 0) {
                        payload[payload.length - 1] = {
                            ...payload[payload.length - 1]!,
                            content:
                                payload[payload.length - 1]!.content +
                                '\n\n' +
                                attachedContext
                                    .map((item) => `## ${item.label}\n\`\`\`\n${item.content}\n\`\`\``)
                                    .join('\n\n'),
                        };
                    }

                    await streamChat(
                        uuid,
                        payload,
                        lines,
                        (delta) => replace((turn) => ({ ...turn, content: turn.content + delta })),
                        controller.signal,
                    );

                    replace((turn) => ({ ...turn, pending: false }));
                }
            } catch (error) {
                if (controller.signal.aborted) {
                    replace((turn) => ({ ...turn, pending: false }));
                    return;
                }

                const message = httpErrorToHuman(error);
                replace((turn) => ({ ...turn, content: message, pending: false, error: true }));
                clearAndAddHttpError(error as Error);
            } finally {
                if (!controller.signal.aborted) setBusy(false);
                if (abortRef.current === controller) abortRef.current = null;
            }
        },
        [uuid, attachedContext, consoleContext, clearAndAddHttpError],
    );

    const send = useCallback(() => {
        const content = input.trim();
        if (!content || busy) return;

        const turn: ChatTurn = { id: nextId(), role: 'user', content };
        const conversation = [...turns, turn];

        setInput('');
        void respond(conversation);
    }, [input, busy, turns, respond]);

    const runCrashAnalysis = useCallback(() => {
        if (busy) return;

        const turn: ChatTurn = {
            id: nextId(),
            role: 'user',
            content: t('ai:crash_prompt'),
        };

        void respond([...turns, turn], 'crash_analysis');
    }, [busy, turns, respond, t]);

    const attachFile = useCallback(
        async (path: string, label?: string) => {
            try {
                const contents = await readAiFile(uuid, path);
                setAttachedContext((current) => [...current, { label: label ?? path, content: contents }]);
                toast.success(t('ai:attached_file', { file: label ?? path }));
            } catch (error) {
                clearAndAddHttpError(error as Error);
            }
        },
        [uuid, clearAndAddHttpError, t],
    );

    const attachDirectory = useCallback(
        async (path: string) => {
            try {
                const listing = await getAiDirectory(uuid, path);
                const rendered = listing.entries
                    .map((entry) => `${entry.is_file ? '-' : 'd'} ${entry.name}`)
                    .join('\n');

                setAttachedContext((current) => [
                    ...current,
                    { label: `listing: ${path}`, content: rendered || '(empty)' },
                ]);
                toast.success(t('ai:attached_directory', { directory: path }));
            } catch (error) {
                clearAndAddHttpError(error as Error);
            }
        },
        [uuid, clearAndAddHttpError, t],
    );

    /**
     * Follow up on any files or directories the assistant referenced by name.
     */
    const followDirectives = useCallback(
        async (text: string) => {
            const { files, directories } = extractDirectives(text);

            for (const file of files) {
                await attachFile(file);
            }

            for (const directory of directories) {
                await attachDirectory(directory);
            }

            if (files.length === 0 && directories.length === 0) {
                toast.info(t('ai:no_directives'));
            }
        },
        [attachFile, attachDirectory, t],
    );

    const onKeyDown = (event: React.KeyboardEvent<HTMLTextAreaElement>) => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            send();
        }
    };

    const stop = () => {
        abortRef.current?.abort();
        setBusy(false);
    };

    const reset = () => {
        abortRef.current?.abort();
        setTurns([]);
        setAttachedContext([]);
        setBusy(false);
    };

    return (
        <Sheet open={visible} onOpenChange={(open) => !open && onClose()}>
            <SheetContent side='right' className='flex w-full flex-col gap-0 p-0 sm:max-w-lg' showCloseButton={false}>
                <SheetHeader className='flex-row items-center justify-between border-b p-4'>
                    <div className='flex items-center gap-2'>
                        <Sparkles className='size-4 text-primary' />
                        <SheetTitle>{t('ai:title')}</SheetTitle>
                    </div>
                    <div className='flex items-center gap-1'>
                        <Button
                            variant='ghost'
                            size='icon-sm'
                            onClick={reset}
                            disabled={turns.length === 0}
                            title={t('ai:clear')}
                        >
                            <Trash2 className='size-4' />
                        </Button>
                        <Button variant='ghost' size='icon-sm' onClick={onClose} title={t('common:close')}>
                            <X className='size-4' />
                        </Button>
                    </div>
                </SheetHeader>

                <div className='flex-1 overflow-y-auto p-4' ref={scrollRef}>
                    {turns.length === 0 ? (
                        <div className='flex h-full flex-col items-center justify-center gap-3 text-center'>
                            <Sparkles className='size-8 text-muted-foreground/50' />
                            <p className='text-sm text-muted-foreground'>
                                {t('ai:empty_state', { server: serverName })}
                            </p>
                            <div className='flex flex-wrap justify-center gap-2'>
                                <Button variant='outline' size='sm' onClick={() => attachDirectory('/')}>
                                    <FolderOpen className='size-3.5' />
                                    {t('ai:attach_root')}
                                </Button>
                                {status === 'offline' && (
                                    <Button variant='outline' size='sm' onClick={runCrashAnalysis}>
                                        <TriangleAlert className='size-3.5' />
                                        {t('ai:analyze_crash')}
                                    </Button>
                                )}
                            </div>
                        </div>
                    ) : (
                        <div className='flex flex-col gap-4'>
                            {turns.map((turn) => (
                                <div
                                    key={turn.id}
                                    className={
                                        turn.role === 'user'
                                            ? 'ml-6 rounded-lg bg-primary/10 px-3 py-2 text-sm'
                                            : 'mr-6 text-sm'
                                    }
                                >
                                    {turn.role === 'assistant' ? (
                                        <div className='flex flex-col gap-2'>
                                            {turn.error || (turn.pending && !turn.content) ? (
                                                <p
                                                    className={cn(
                                                        'text-sm leading-relaxed whitespace-pre-wrap break-words',
                                                        turn.error && 'text-destructive',
                                                        !turn.content && !turn.error && 'text-muted-foreground',
                                                    )}
                                                >
                                                    {turn.content || t('ai:thinking')}
                                                </p>
                                            ) : (
                                                <AiMarkdown content={turn.content} />
                                            )}
                                            {!turn.pending && !turn.error && turn.content && (
                                                <div className='flex flex-wrap gap-1.5'>
                                                    <Button
                                                        variant='ghost'
                                                        size='sm'
                                                        className='h-7 text-xs'
                                                        onClick={() => followDirectives(turn.content)}
                                                    >
                                                        <WandSparkles className='size-3' />
                                                        {t('ai:follow_up_files')}
                                                    </Button>
                                                </div>
                                            )}
                                        </div>
                                    ) : (
                                        <pre className='whitespace-pre-wrap break-words font-sans'>{turn.content}</pre>
                                    )}
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {attachedContext.length > 0 && (
                    <div className='flex flex-wrap gap-1.5 border-t px-4 py-2'>
                        {attachedContext.map((item, index) => (
                            <span
                                key={`${item.label}-${index}`}
                                className='inline-flex items-center gap-1 rounded-md bg-muted px-2 py-1 text-xs'
                            >
                                <FileText className='size-3' />
                                {item.label}
                                <button
                                    type='button'
                                    className='text-muted-foreground hover:text-foreground'
                                    onClick={() =>
                                        setAttachedContext((current) => current.filter((_, i) => i !== index))
                                    }
                                >
                                    <X className='size-3' />
                                </button>
                            </span>
                        ))}
                    </div>
                )}

                <div className='border-t p-3'>
                    <div className='flex items-end gap-2'>
                        <Textarea
                            value={input}
                            onChange={(event) => setInput(event.target.value)}
                            onKeyDown={onKeyDown}
                            placeholder={t('ai:placeholder')}
                            className='max-h-40 min-h-[52px] resize-none font-sans text-sm'
                            disabled={busy}
                            rows={2}
                        />
                        {busy ? (
                            <Button
                                variant='outline'
                                size='icon'
                                className='rounded-full'
                                onClick={stop}
                                title={t('ai:stop')}
                                aria-label={t('ai:stop')}
                            >
                                <Square className='size-4' />
                            </Button>
                        ) : (
                            <Button
                                size='icon'
                                className='rounded-full'
                                onClick={send}
                                disabled={!input.trim()}
                                title={t('ai:send')}
                                aria-label={t('ai:send')}
                            >
                                <Send className='size-4' />
                            </Button>
                        )}
                    </div>
                    {status === 'offline' && turns.length > 0 && (
                        <Button
                            variant='ghost'
                            size='sm'
                            className='mt-2 h-7 w-full text-xs text-muted-foreground'
                            onClick={runCrashAnalysis}
                            disabled={busy}
                        >
                            <TriangleAlert className='size-3' />
                            {t('ai:analyze_crash')}
                        </Button>
                    )}
                </div>
            </SheetContent>
        </Sheet>
    );
};

export default AiAssistantPanel;
