import { Code, Copy } from 'lucide-react';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';

import { Button } from '@/components/ui/button';
import { Dialog } from '@/components/elements/dialog';

import { formatObjectToIdentString } from '@/lib/objects';

const ActivityLogMetaButton = ({ meta }: { meta: Record<string, unknown> }) => {
    const { t } = useTranslation();
    const [open, setOpen] = useState(false);
    const [copied, setCopied] = useState(false);

    const copyToClipboard = async () => {
        try {
            await navigator.clipboard.writeText(JSON.stringify(meta, null, 2));
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        } catch (err) {
            console.error('Failed to copy metadata:', err);
        }
    };

    const metadataString = formatObjectToIdentString(meta);
    const metadataJson = JSON.stringify(meta, null, 2);

    return (
        <>
            <Dialog open={open} onClose={() => setOpen(false)} hideCloseIcon title={t('common:event_metadata')}>
                <div className='space-y-4'>
                    <div className='flex items-center justify-between'>
                        <h4 className='text-sm font-medium text-foreground'>{t('common:formatted_view')}</h4>
                        <Button
                            variant='secondary'
                            onClick={copyToClipboard}
                            className='flex items-center gap-2 text-xs'
                        >
                            <Copy size={22} />
                            {copied ? t('common:copied') : t('common:copy_json')}
                        </Button>
                    </div>

                    <div className='bg-card rounded-lg p-4 border border-border max-h-96 overflow-auto'>
                        <pre className='font-mono text-sm leading-relaxed whitespace-pre-wrap text-foreground/80'>
                            {metadataString}
                        </pre>
                    </div>

                    <div>
                        <h4 className='text-sm font-medium text-foreground mb-2'>{t('common:raw_json')}</h4>
                        <div className='bg-card rounded-lg p-4 border border-border max-h-64 overflow-auto'>
                            <pre className='font-mono text-xs leading-relaxed whitespace-pre-wrap text-muted-foreground'>
                                {metadataJson}
                            </pre>
                        </div>
                    </div>
                </div>

                <Dialog.Footer>
                    <Button variant='secondary' onClick={() => setOpen(false)}>
                        {t('common:close')}
                    </Button>
                </Dialog.Footer>
            </Dialog>

            <button
                aria-label={t('common:event_metadata')}
                className='w-6 h-6 rounded text-muted-foreground hover:text-foreground hover:bg-muted/50 transition-colors duration-150 flex items-center justify-center'
                onClick={() => setOpen(true)}
            >
                <Code size={22} />
            </button>
        </>
    );
};

export default ActivityLogMetaButton;
