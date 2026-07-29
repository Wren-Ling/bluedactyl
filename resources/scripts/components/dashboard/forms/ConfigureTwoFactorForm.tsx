import { useStoreState } from 'easy-peasy';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';

import DisableTOTPDialog from '@/components/dashboard/forms/DisableTOTPDialog';
import RecoveryTokensDialog from '@/components/dashboard/forms/RecoveryTokensDialog';
import SetupTOTPDialog from '@/components/dashboard/forms/SetupTOTPDialog';
import { Button } from '@/components/ui/button';

import { ApplicationStore } from '@/state';

import useFlash from '@/plugins/useFlash';

const ConfigureTwoFactorForm = () => {
    const { t } = useTranslation();
    const [tokens, setTokens] = useState<string[]>([]);
    const [visible, setVisible] = useState<'enable' | 'disable' | null>(null);
    const isEnabled = useStoreState((state: ApplicationStore) => state.user.data!.useTotp);
    const { clearFlashes } = useFlash();

    useEffect(() => {
        return () => {
            clearFlashes('account:two-step');
        };
    }, [visible]);

    const onTokens = (tokens: string[]) => {
        setTokens(tokens);
        setVisible(null);
    };

    return (
        <div className='contents'>
            <SetupTOTPDialog open={visible === 'enable'} onClose={() => setVisible(null)} onTokens={onTokens} />
            <RecoveryTokensDialog tokens={tokens} open={tokens.length > 0} onClose={() => setTokens([])} />
            <DisableTOTPDialog open={visible === 'disable'} onClose={() => setVisible(null)} />
            <p className={`text-sm`}>
                {isEnabled ? t('account:2fa_enabled') : t('account:2fa_disabled')}
            </p>
            <div className={`mt-6`}>
                {isEnabled ? (
                    <Button variant='destructive' onClick={() => setVisible('disable')}>
                        {t('account:remove_authenticator')}
                    </Button>
                ) : (
                    <Button onClick={() => setVisible('enable')}>
                        {t('account:enable_authenticator')}
                    </Button>
                )}
            </div>
        </div>
    );
};

export default ConfigureTwoFactorForm;
