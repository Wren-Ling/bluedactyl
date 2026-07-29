import { Actions, useStoreActions } from 'easy-peasy';
import { QRCodeSVG } from 'qrcode.react';
import { useContext, useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';

import FlashMessageRender from '@/components/FlashMessageRender';
import { Button } from '@/components/ui/button';
import CopyOnClick from '@/components/elements/CopyOnClick';
import Spinner from '@/components/elements/Spinner';
import { Dialog, DialogWrapperContext } from '@/components/elements/dialog';
import { Input } from '@/components/elements/inputs';

import asDialog from '@/hoc/asDialog';

import enableAccountTwoFactor from '@/api/account/enableAccountTwoFactor';
import getTwoFactorTokenData, { TwoFactorTokenData } from '@/api/account/getTwoFactorTokenData';

import { ApplicationStore } from '@/state';

import { useFlashKey } from '@/plugins/useFlash';

interface Props {
    onTokens: (tokens: string[]) => void;
}

const ConfigureTwoFactorForm = ({ onTokens }: Props) => {
    const { t } = useTranslation();
    const [submitting, setSubmitting] = useState(false);
    const [value, setValue] = useState('');
    const [password, setPassword] = useState('');
    const [token, setToken] = useState<TwoFactorTokenData | null>(null);
    const { clearAndAddHttpError } = useFlashKey('account:two-step');
    const updateUserData = useStoreActions((actions: Actions<ApplicationStore>) => actions.user.updateUserData);

    const { close, setProps } = useContext(DialogWrapperContext);

    useEffect(() => {
        setProps((state) => ({
            ...state,
            title: t('account:setup_totp_title'),
            description: t('account:setup_totp_description'),
        }));
    }, []);

    useEffect(() => {
        getTwoFactorTokenData()
            .then(setToken)
            .catch((error) => clearAndAddHttpError(error));
    }, []);

    useEffect(() => {
        setProps((state) => ({ ...state, preventExternalClose: submitting }));
    }, [submitting]);

    const submit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        e.stopPropagation();

        if (submitting) return;

        setSubmitting(true);
        clearAndAddHttpError();
        enableAccountTwoFactor(value, password)
            .then((tokens) => {
                updateUserData({ useTotp: true });
                onTokens(tokens);
            })
            .catch((error) => {
                clearAndAddHttpError(error);
                setSubmitting(false);
            });
    };

    return (
        <form id={'enable-totp-form'} onSubmit={submit}>
            <FlashMessageRender byKey={'account:two-step'} />
            <div className={'flex items-center justify-center w-56 h-56 p-2 bg-zinc-50 shadow-sm mx-auto mt-6'}>
                {!token ? (
                    <Spinner />
                ) : (
                    <QRCodeSVG value={token.image_url_data} className={`w-full h-full shadow-none`} />
                )}
            </div>
            <CopyOnClick text={token?.secret}>
                <p className={'font-mono text-sm text-zinc-100 text-center mt-2'}>
                    {token?.secret.match(/.{1,4}/g)!.join(' ') || t('common:loading')}
                </p>
            </CopyOnClick>
            <p id={'totp-code-description'} className={'mt-6'}>
                {t('account:setup_totp_instructions')}
            </p>
            <Input.Text
                aria-labelledby={'totp-code-description'}
                variant={Input.Text.Variants.Loose}
                value={value}
                onChange={(e) => setValue(e.currentTarget.value)}
                className={'mt-3'}
                placeholder={'000000'}
                type={'text'}
                inputMode={'numeric'}
                autoComplete={'one-time-code'}
                pattern={'\\d{6}'}
            />
            <label htmlFor={'totp-password'} className={'block mt-3'}>
                {t('account:account_password')}
            </label>
            <Input.Text
                variant={Input.Text.Variants.Loose}
                className={'mt-1'}
                type={'password'}
                value={password}
                onChange={(e) => setPassword(e.currentTarget.value)}
            />
            <Dialog.Footer>
                <Button variant='secondary' onClick={close}>
                    {t('common:cancel')}
                </Button>
                <Button
                    disabled={!token || value.length !== 6 || !password.length}
                    type={'submit'}
                    form={'enable-totp-form'}
                >
                    {t('account:setup_totp_enable')}
                </Button>
            </Dialog.Footer>
        </form>
    );
};

export default asDialog({
    title: 'Enable Authenticator App',
    description: "You'll be required to enter a verification code each time you sign in.",
})(ConfigureTwoFactorForm);
