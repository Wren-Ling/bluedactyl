import { useStoreState } from 'easy-peasy';
import { Sparkles, TriangleAlert } from 'lucide-react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Alert } from '@/components/elements/alert';
import { SocketEvent } from '@/components/server/events';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';

import { takeConsoleLines } from '@/lib/ai/consoleBuffer';
import { isCrashLine } from '@/lib/mclogsUtils';

import { httpErrorToHuman } from '@/api/http';
import { analyzeCrash } from '@/api/server/ai/assistant';

import { ServerContext } from '@/state/server';

import useWebsocketEvent from '@/plugins/useWebsocketEvent';

const ANALYSIS_DEBOUNCE = 1500;

/**
 * One-click crash diagnosis.
 *
 * Replaces the external log-upload flow: the console buffer is already being
 * collected client side, so a detected stop can be sent to the assistant along
 * with whatever log files the server keeps on disk.
 */
const AiCrashAnalysisCard = () => {
    const { t } = useTranslation();
    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const status = ServerContext.useStoreState((state) => state.status.value);
    const ai = useStoreState((state) => state.settings.data?.ai);

    const [visible, setVisible] = useState(false);
    const [analyzing, setAnalyzing] = useState(false);
    const [reply, setReply] = useState<string | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [open, setOpen] = useState(false);

    const crashIndicators = useRef<string[]>([]);
    const previousStatus = useRef(status);
    const mounted = useRef(true);

    useEffect(() => {
        mounted.current = true;

        return () => {
            mounted.current = false;
        };
    }, []);

    // Collect only the lines that look like crash evidence, so the prompt stays
    // focused even on servers that log heavily.
    useWebsocketEvent(SocketEvent.CONSOLE_OUTPUT, (data: string) => {
        const lines = String(data).split(/\r?\n/);

        for (const line of lines) {
            if (line && isCrashLine(line)) {
                crashIndicators.current = [...crashIndicators.current, line].slice(-50);
            }
        }
    });

    const run = useCallback(async () => {
        setAnalyzing(true);
        setError(null);
        setReply(null);
        setOpen(true);

        try {
            const console = takeConsoleLines(200);
            const result = await analyzeCrash(uuid, console);

            if (!mounted.current) return;

            setReply(result);
        } catch (exception) {
            if (!mounted.current) return;
            setError(httpErrorToHuman(exception));
        } finally {
            if (mounted.current) setAnalyzing(false);
        }
    }, [uuid]);

    const debouncedRun = useMemo(() => {
        let timer: ReturnType<typeof setTimeout> | null = null;

        return () => {
            if (timer) clearTimeout(timer);
            timer = setTimeout(() => void run(), ANALYSIS_DEBOUNCE);
        };
    }, [run]);

    // Offer the action when the server drops after printing crash evidence.
    useEffect(() => {
        if (previousStatus.current !== 'offline' && status === 'offline' && crashIndicators.current.length > 0) {
            setVisible(true);
        }

        previousStatus.current = status;
    }, [status]);

    // The button is only useful with the feature enabled and a stopped server.
    const enabled = Boolean(ai?.enabled && ai.allow_crash_analysis);

    if (!enabled) return null;

    if (!visible && status !== 'offline') return null;

    return (
        <>
            <div className='rounded-xl border border-border bg-muted/30 p-3 transition-all duration-150 hover:border-border/40 sm:p-4'>
                <Alert type={error ? 'danger' : 'warning'}>
                    <div className='flex items-center justify-between gap-3'>
                        <div className='flex-1'>
                            <p className='text-sm font-medium'>{t('server:crash_analysis_title')}</p>
                            <p className='mt-1 text-sm'>
                                {analyzing
                                    ? t('ai:thinking')
                                    : error
                                      ? t('server:crash_analysis_failed')
                                      : crashIndicators.current.length > 0
                                        ? t('ai:crash_hint')
                                        : t('ai:crash_hint_generic')}
                            </p>
                        </div>
                        <div className='flex flex-shrink-0 items-center gap-2'>
                            <Button variant='secondary' size='sm' onClick={debouncedRun} disabled={analyzing}>
                                <Sparkles className='size-3.5' />
                                {t('ai:analyze_crash')}
                            </Button>
                            <Button variant='ghost' size='sm' onClick={() => setVisible(false)}>
                                {t('server:dismiss')}
                            </Button>
                        </div>
                    </div>
                </Alert>
            </div>

            <Dialog open={open} onOpenChange={(next) => !analyzing && setOpen(next)}>
                <DialogContent className='max-w-2xl'>
                    <DialogHeader>
                        <DialogTitle className='flex items-center gap-2'>
                            {error ? (
                                <TriangleAlert className='size-4 text-destructive' />
                            ) : (
                                <Sparkles className='size-4' />
                            )}
                            {t('ai:crash_title')}
                        </DialogTitle>
                        <DialogDescription>{t('ai:crash_description')}</DialogDescription>
                    </DialogHeader>

                    <div className='max-h-[60vh] overflow-y-auto'>
                        {analyzing ? (
                            <p className='py-8 text-center text-sm text-muted-foreground'>{t('ai:thinking')}</p>
                        ) : error ? (
                            <p className='py-4 text-sm text-destructive'>{error}</p>
                        ) : (
                            <pre className='whitespace-pre-wrap break-words font-sans text-sm leading-relaxed'>
                                {reply}
                            </pre>
                        )}
                    </div>
                </DialogContent>
            </Dialog>
        </>
    );
};

export default AiCrashAnalysisCard;
