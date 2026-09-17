import { Component, useCallback, useState, type ReactNode } from 'react';
import i18n from '@/i18n/config';
import {
    AlertCircle,
    ArrowLeft,
    Check,
    Copy,
    RefreshCw,
} from 'lucide-react';

import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

function CopyButton({ text }: { text: string }) {
    const [copied, setCopied] = useState(false);

    const handleCopy = useCallback(async () => {
        await navigator.clipboard.writeText(text);
        setCopied(true);
        setTimeout(() => setCopied(false), 2000);
    }, [text]);

    return (
        <Button
            variant='ghost'
            size='icon-xs'
            onClick={handleCopy}
            className='absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity'
        >
            {copied ? <Check className='size-3.5' /> : <Copy className='size-3.5' />}
        </Button>
    );
}

interface Props {
    children?: ReactNode;
    fallback?: ReactNode;
}

interface State {
    hasError: boolean;
    error: Error | null;
    errorInfo: React.ErrorInfo | null;
}

class ErrorBoundary extends Component<Props, State> {
    constructor(props: Props) {
        super(props);
        this.state = { hasError: false, error: null, errorInfo: null };
    }

    static getDerivedStateFromError(error: Error): Partial<State> {
        return { hasError: true, error };
    }

    override componentDidCatch(error: Error, errorInfo: React.ErrorInfo): void {
        this.setState({ errorInfo });
        console.error('ErrorBoundary caught an error:', error, errorInfo);
    }

    handleReset = (): void => {
        this.setState({ hasError: false, error: null, errorInfo: null });
    };

    override render(): ReactNode {
        if (this.state.hasError) {
            if (this.props.fallback) {
                return this.props.fallback;
            }

            const errorDetails = this.state.error
                ? `${this.state.error.name}: ${this.state.error.message}\n\n${this.state.error.stack ?? ''}${this.state.errorInfo ? `\n\nComponent Stack:\n${this.state.errorInfo.componentStack}` : ''}`
                : '';

            return (
                <div className='flex h-full w-full items-center justify-center p-6'>
                    <Card className='w-full max-w-lg shadow-lg ring-1 ring-border'>
                        <CardHeader className='relative text-center space-y-3 pt-8 pb-4'>
                            <div className='mx-auto mb-2 flex h-14 w-14 items-center justify-center rounded-full bg-destructive/10 ring-1 ring-destructive/20'>
                                <AlertCircle className='size-7 text-destructive' />
                            </div>
                            <CardTitle className='text-lg font-semibold'>
                                {i18n.t('common:something_went_wrong')}
                            </CardTitle>
                            <CardDescription className='text-muted-foreground'>
                                {i18n.t('common:unexpected_error_description')}
                            </CardDescription>
                        </CardHeader>
                        <CardContent className='space-y-4 px-6 pb-6'>
                            {import.meta.env.DEV && this.state.error && (
                                <div className='group relative overflow-hidden rounded-lg border border-destructive/20 bg-destructive/5 p-3'>
                                    <div className='mb-2 flex items-center justify-between'>
                                        <p className='text-xs font-medium text-destructive'>
                                            {this.state.error.name}: {this.state.error.message}
                                        </p>
                                        <CopyButton text={errorDetails} />
                                    </div>
                                    <pre className='text-xs text-muted-foreground/80 whitespace-pre-wrap break-all max-h-32 overflow-auto leading-relaxed'>
                                        {this.state.error.stack}
                                    </pre>
                                    {this.state.errorInfo && (
                                        <pre className='mt-3 pt-3 border-t border-destructive/10 text-xs text-muted-foreground/80 whitespace-pre-wrap break-all max-h-32 overflow-auto leading-relaxed'>
                                            {this.state.errorInfo.componentStack}
                                        </pre>
                                    )}
                                </div>
                            )}

                            <div className='flex gap-3 pt-2'>
                                <Button
                                    variant='outline'
                                    className='flex-1'
                                    onClick={() => window.history.back()}
                                >
                                    <ArrowLeft className='size-4 mr-2' />
                                    {i18n.t('common:go_back')}
                                </Button>
                                <Button className='flex-1' onClick={this.handleReset}>
                                    <RefreshCw className='size-4 mr-2' />
                                    {i18n.t('common:try_again')}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            );
        }

        return this.props.children;
    }
}

export default ErrorBoundary;
