import { Check, Link, TriangleAlert } from 'lucide-react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { toast } from 'sonner';
import { useTranslation } from 'react-i18next';

import { Button } from '@/components/ui/button';
import Modal from '@/components/elements/Modal';
import Spinner from '@/components/elements/Spinner';
import { Alert } from '@/components/elements/alert';
import { SocketEvent } from '@/components/server/events';

import { debounce, isCrashLine } from '@/lib/mclogsUtils';

import { MclogsInsight, analyzeLogs } from '@/api/mclo.gs/mclogsApi';
import getFileContents from '@/api/server/files/getFileContents';

import { ServerContext } from '@/state/server';

import useWebsocketEvent from '@/plugins/useWebsocketEvent';

const CRASH_DETECTION_DEBOUNCE = 1500; // 1.5 seconds
const MANUAL_ANALYZE_DEBOUNCE = 1000; // 1 second for manual clicks
const LOG_FILE_PATH = '/logs/latest.log';
const MAX_CONSOLE_BUFFER = 300;

// Shared analysis logic hook
const useLogAnalysis = () => {
    const { t } = useTranslation();
    const [analyzing, setAnalyzing] = useState(false);
    const [analysis, setAnalysis] = useState<MclogsInsight | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [showCard, setShowCard] = useState(false);

    const uuid = ServerContext.useStoreState((state) => state.server.data!.uuid);
    const status = ServerContext.useStoreState((state) => state.status.value);

    const consoleBufferRef = useRef<string[]>([]);
    const previousStatusRef = useRef(status);
    const mountedRef = useRef(true);

    // Keep console buffer trimmed and split chunks into lines
    useWebsocketEvent(SocketEvent.CONSOLE_OUTPUT, (data: string) => {
        const lines = String(data).split(/\r?\n/).filter(Boolean);
        if (lines.length === 0) return;

        consoleBufferRef.current.push(...lines);
        if (consoleBufferRef.current.length > MAX_CONSOLE_BUFFER) {
            // Trim to last MAX_CONSOLE_BUFFER lines
            consoleBufferRef.current = consoleBufferRef.current.slice(-MAX_CONSOLE_BUFFER);
        }
    });

    const analyzeCrash = useCallback(
        async (showToast = false) => {
            setAnalyzing(true);
            setError(null);

            try {
                const logContent = await getFileContents(uuid, LOG_FILE_PATH);

                if (!logContent || logContent.trim().length === 0) {
                    throw new Error('No log content found in latest.log');
                }

                const result = await analyzeLogs(logContent);
                if (!mountedRef.current) return;

                setAnalysis(result);
                setShowCard(true);

                // Show toast notifications for manual analysis
                if (showToast) {
                    if (result.analysis?.problems?.length > 0) {
                        toast.success(t('server:analysis_complete_issues', { count: result.analysis.problems.length }));
                    } else {
                        toast.info(t('server:analysis_complete_no_issues'));
                    }
                }
            } catch (err) {
                if (!mountedRef.current) return;

                const errorMessage = err instanceof Error ? err.message : t('server:failed_to_analyze_logs');
                setError(errorMessage);
                console.error('Mclogs analysis failed:', err);

                // Show card even on error for auto-analysis
                setShowCard(true);

                // Only show error toast for manual analysis and unexpected errors
                const looksLikeMissingLog =
                    /latest\.log/i.test(errorMessage) ||
                    /not found/i.test(errorMessage) ||
                    /no log content/i.test(errorMessage);

                if (!looksLikeMissingLog && showToast) {
                    toast.error(t('server:failed_to_analyze_logs'));
                }
            } finally {
                if (mountedRef.current) setAnalyzing(false);
            }
        },
        [uuid],
    );

    // Debounced auto-analysis used when we detect crash indicators
    const analyzeCrashDebounced = useMemo(() => {
        const fn = debounce(() => {
            // Note: run the immediate version internally
            void analyzeCrash();
        }, CRASH_DETECTION_DEBOUNCE);

        return fn;
    }, [analyzeCrash]);

    // Monitor server status changes to detect crashes
    useEffect(() => {
        // If server just went offline, check recent console output for crash indicators
        if (previousStatusRef.current !== 'offline' && status === 'offline') {
            const hasCrashIndicators = consoleBufferRef.current.some((line) => isCrashLine(line));
            if (hasCrashIndicators) {
                analyzeCrashDebounced();
            }
        }

        // Update previous status
        previousStatusRef.current = status;
    }, [status, analyzeCrashDebounced]);

    // Manual analysis (debounced to prevent rapid clicking)
    const manualAnalyze = useMemo(() => {
        return debounce(() => {
            void analyzeCrash(true); // Show toast for manual analysis
        }, MANUAL_ANALYZE_DEBOUNCE);
    }, [analyzeCrash]);

    // Dismiss card
    const dismissCard = () => {
        setShowCard(false);
    };

    // Cleanup on unmount
    useEffect(() => {
        mountedRef.current = true;
        return () => {
            mountedRef.current = false;
            // Best-effort cancel if debounce util provides cancel()
            try {
                (analyzeCrashDebounced as { cancel?: () => void })?.cancel?.();
                (manualAnalyze as { cancel?: () => void })?.cancel?.();
            } catch {
                // no-op
            }
        };
    }, [analyzeCrashDebounced, manualAnalyze]);

    return {
        analyzing,
        analysis,
        error,
        showCard,
        manualAnalyze,
        dismissCard,
        consoleBufferRef,
        previousStatusRef,
        mountedRef,
        analyzeCrashDebounced,
    };
};

// Crash Analysis Card Component
export const CrashAnalysisCard = () => {
    const { t } = useTranslation();
    const { analyzing, analysis, error, showCard, dismissCard } = useLogAnalysis();

    const [modalVisible, setModalVisible] = useState(false);

    if (!showCard) return null;

    const getCardMessage = () => {
        if (analyzing) {
            return t('server:analyzing_crash_logs');
        }

        if (error) {
            const looksLikeMissingLog =
                /latest\.log/i.test(error) || /not found/i.test(error) || /no log content/i.test(error);

            if (looksLikeMissingLog) {
                return t('server:crash_no_log_file');
            }
            return t('server:crash_analysis_failed');
        }

        if (!analysis) {
            return t('server:crash_analysis_in_progress');
        }

        const problems = analysis.analysis?.problems ?? [];
        if (problems.length > 0) {
            return t('server:crash_issues_found', { count: problems.length });
        }

        return t('server:crash_no_issues_found');
    };

    const getCardType = (): 'warning' | 'danger' => {
        if (analyzing) return 'warning';
        if (error) return 'danger';
        if (!analysis) return 'warning';
        const problems = analysis.analysis?.problems ?? [];
        return problems.length > 0 ? 'danger' : 'warning';
    };

    const canViewAnalysis = analysis && !error && !analyzing;

    return (
        <>
            <div className='bg-muted/30 border border-border rounded-xl p-3 sm:p-4 hover:border-border/40 transition-all duration-150'>
                <Alert type={getCardType()}>
                    <div className='flex items-center justify-between gap-3'>
                        <div className='flex-1'>
                            <p className='font-medium text-sm'>{t('server:crash_analysis_title')}</p>
                            <p className='text-sm mt-1'>{getCardMessage()}</p>
                        </div>
                        <div className='flex items-center gap-2 flex-shrink-0'>
                            {canViewAnalysis && (
                                <Button variant='secondary' onClick={() => setModalVisible(true)} size='sm'>
                                    {t('server:view_details')}
                                </Button>
                            )}
                            <Button variant='secondary' onClick={dismissCard} size='sm'>
                                {t('server:dismiss')}
                            </Button>
                        </div>
                    </div>
                </Alert>
            </div>

            {/* Analysis Modal */}
            {modalVisible && (
                <AnalysisModal
                    visible={modalVisible}
                    onClose={() => setModalVisible(false)}
                    analysis={analysis}
                    error={error}
                    analyzing={analyzing}
                />
            )}
        </>
    );
};

// Analysis Modal Component
const AnalysisModal = ({
    visible,
    onClose,
    analysis,
    error,
    analyzing,
}: {
    visible: boolean;
    onClose: () => void;
    analysis: MclogsInsight | null;
    error: string | null;
    analyzing: boolean;
}) => {
    const { t } = useTranslation();
    const { manualAnalyze } = useLogAnalysis();

    const closeModal = () => {
        if (analyzing) return;
        onClose();
    };

    // Render loading state
    const renderLoadingState = () => (
        <div className='flex flex-col items-center justify-center py-12' aria-busy='true'>
            <Spinner size='large' />
            <h3 className='text-lg font-medium text-neutral-200 mt-4'>{t('server:analyzing_server_logs')}</h3>
            <p className='text-neutral-400 mt-2 text-center max-w-md'>
                {t('server:analyzing_logs_description')}
            </p>
        </div>
    );

    // Render error state
    const renderErrorState = () => (
        <div className='space-y-6'>
            <div className='bg-red-500/10 border border-red-500/20 rounded-lg p-4'>
                <div className='flex items-start gap-3'>
                    <TriangleAlert
                        size={22}
                        className='w-6 h-6 text-red-400 flex-shrink-0 mt-0.5'
                    />
                    <div className='flex-1'>
                        <h3 className='font-semibold text-red-400 text-lg'>{t('server:analysis_failed')}</h3>
                        <p className='text-neutral-300 mt-2'>{error}</p>
                        {(/latest\.log/i.test(error!) || /no log content/i.test(error!)) && (
                            <p className='text-neutral-400 mt-3 text-sm'>
                                {t('server:no_log_file_hint')}
                            </p>
                        )}
                    </div>
                </div>
            </div>
        </div>
    );

    // Render server information header
    const renderServerInfo = () => {
        if (!analysis) return null;

        const information = analysis.analysis?.information ?? [];
        const serverVersion = analysis.version;
        const serverType = analysis.title;

        return (
            <div className='bg-blue-500/10 border border-blue-500/20 rounded-lg p-4 mb-6'>
                <div className='flex items-center justify-between mb-3'>
                    <h3 className='text-lg font-semibold text-blue-400'>{t('server:server_information')}</h3>
                    <a
                        href='https://mclo.gs'
                        target='_blank'
                        rel='noopener noreferrer'
                        className='text-sm text-blue-400 hover:text-blue-300 flex items-center gap-1.5 transition-colors'
                    >
                        <Link size={22} className='w-4 h-4' />
                        {t('server:powered_by_mclogs')}
                    </a>
                </div>

                <div className='grid grid-cols-1 md:grid-cols-2 gap-3'>
                    <div className='bg-blue-500/5 rounded-lg p-3'>
                        <p className='text-blue-400 font-medium text-sm mb-1'>{t('server:server_type')}</p>
                        <p className='text-neutral-200'>
                            {serverType} {serverVersion}
                        </p>
                    </div>

                    {information.slice(0, 3).map((info, idx) => (
                        <div key={idx} className='bg-blue-500/5 rounded-lg p-3'>
                            <p className='text-blue-400 font-medium text-sm mb-1'>{info.label}</p>
                            <p className='text-neutral-200 break-all'>{info.value}</p>
                        </div>
                    ))}
                </div>

                {information.length > 3 && (
                    <details className='mt-3'>
                        <summary className='text-blue-400 text-sm cursor-pointer hover:text-blue-300 transition-colors'>
                            {t('server:show_more_details', { count: information.length - 3 })}
                        </summary>
                        <div className='grid grid-cols-1 md:grid-cols-2 gap-3 mt-3'>
                            {information.slice(3).map((info, idx) => (
                                <div key={idx} className='bg-blue-500/5 rounded-lg p-3'>
                                    <p className='text-blue-400 font-medium text-sm mb-1'>{info.label}</p>
                                    <p className='text-neutral-200 break-all'>{info.value}</p>
                                </div>
                            ))}
                        </div>
                    </details>
                )}
            </div>
        );
    };

    // Render errors section
    const renderErrors = () => {
        if (!analysis) return null;

        const problems = analysis.analysis?.problems ?? [];

        if (problems.length === 0) {
            return (
                <div className='bg-green-500/10 border border-green-500/20 rounded-lg p-4 mb-6'>
                    <div className='flex items-start gap-3'>
                        <Check
                            size={22}
                            className='w-6 h-6 text-green-400 flex-shrink-0 mt-0.5'
                        />
                        <div>
                            <h3 className='font-semibold text-green-400 text-lg'>{t('server:no_issues_detected')}</h3>
                            <p className='text-neutral-300 mt-2'>
                                {t('server:no_issues_message')}
                            </p>
                        </div>
                    </div>
                </div>
            );
        }

        return (
            <div className='space-y-4 mb-6'>
                <h3 className='text-lg font-semibold text-red-400'>{t('server:issues_found', { count: problems.length })}</h3>

                <div className='space-y-3'>
                    {problems.map((problem, idx) => (
                        <div key={idx} className='bg-red-500/10 border border-red-500/20 rounded-lg overflow-hidden'>
                            <div className='p-4'>
                                <div className='flex items-start gap-3'>
                                    <TriangleAlert
                                        size={22}
                                        className='w-5 h-5 text-red-400 flex-shrink-0 mt-0.5'
                                    />
                                    <div className='flex-1'>
                                        <h4 className='font-medium text-red-400 mb-2'>{problem.message}</h4>

                                        {!!problem.entry?.lines?.length && (
                                            <div className='bg-red-500/5 border border-red-500/10 rounded-lg p-3 mb-3'>
                                                <p className='text-red-400/70 text-sm mb-2 font-medium'>{t('server:error_log')}</p>
                                                <div className='max-h-40 overflow-y-auto font-mono text-sm space-y-1'>
                                                    {problem.entry.lines.map((line, lineIdx) => (
                                                        <div key={lineIdx} className='flex'>
                                                            <span className='text-red-500/50 mr-3 select-none w-10 text-right flex-shrink-0'>
                                                                {line.number}
                                                            </span>
                                                            <span className='text-red-300/90 break-all'>
                                                                {line.content}
                                                            </span>
                                                        </div>
                                                    ))}
                                                </div>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </div>
        );
    };

    // Render recommendations section
    const renderRecommendations = () => {
        if (!analysis) return null;

        const problems = analysis.analysis?.problems ?? [];
        const allSolutions = problems.flatMap((problem) => problem.solutions || []);

        if (allSolutions.length === 0) return null;

        return (
            <div className='space-y-4'>
                <h3 className='text-lg font-semibold text-green-400'>{t('server:recommended_solutions', { count: allSolutions.length })}</h3>

                <div className='bg-green-500/10 border border-green-500/20 rounded-lg p-4'>
                    <div className='space-y-3'>
                        {allSolutions.map((solution, idx) => (
                            <div key={idx} className='flex items-start gap-3'>
                                <div className='bg-green-500/20 rounded-full p-1 flex-shrink-0 mt-0.5'>
                                    <Check
                                        size={22}
                                        className='w-4 h-4 text-green-400'
                                    />
                                </div>
                                <div className='flex-1'>
                                    <p className='text-neutral-200 leading-relaxed'>{solution.message}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        );
    };

    // Main content renderer
    const renderContent = () => {
        if (analyzing) return renderLoadingState();
        if (error) return renderErrorState();
        if (!analysis) {
            return (
                <div className='text-center py-12'>
                    <p className='text-neutral-400'>{t('server:no_analysis_data')}</p>
                </div>
            );
        }

        return (
            <div className='space-y-6'>
                {renderServerInfo()}
                {renderErrors()}
                {renderRecommendations()}
            </div>
        );
    };

    return (
        <Modal
            visible={visible}
            onDismissed={closeModal}
            closeOnBackground={!analyzing}
            title={t('server:server_log_analysis')}
            showSpinnerOverlay={false}
        >
            <div className='w-full max-w-4xl'>
                {renderContent()}

                <div className='flex justify-center gap-3 mt-8 pt-4 border-t border-neutral-700'>
                    <Button variant='secondary' onClick={manualAnalyze} disabled={analyzing}>
                        {analyzing ? t('server:analyzing') : t('server:analyze_again')}
                    </Button>
                    <Button onClick={closeModal} disabled={analyzing}>
                        {t('server:close')}
                    </Button>
                </div>
            </div>
        </Modal>
    );
};
