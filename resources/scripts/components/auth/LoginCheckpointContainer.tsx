import type { ActionCreator } from 'easy-peasy';
import { useFormikContext, withFormik } from 'formik';
import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import type { Location, RouteProps } from 'react-router-dom';
import { Link, useLocation, useNavigate } from 'react-router-dom';

import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import Field from '@/components/elements/Field';

import loginCheckpoint from '@/api/auth/loginCheckpoint';

import type { FlashStore } from '@/state/flashes';

import useFlash from '@/plugins/useFlash';

import Logo from '../elements/PyroLogo';

interface Values {
    code: string;
    recoveryCode: '';
}

type OwnProps = RouteProps;

type Props = OwnProps & {
    clearAndAddHttpError: ActionCreator<FlashStore['clearAndAddHttpError']['payload']>;
};

function LoginCheckpointForm() {
    const { t } = useTranslation();
    const { isSubmitting, setFieldValue } = useFormikContext<Values>();
    const [isMissingDevice, setIsMissingDevice] = useState(false);

    return (
        <Card className='w-full max-w-sm mx-auto'>
            <CardContent className='p-6'>
                <LoginFormContainer>
                    <Link to='/'>
                        <div className='flex justify-center py-2'>
                            <Logo className='size-8 text-foreground' />
                        </div>
                    </Link>
                    <h2 className='text-lg font-semibold tracking-tight'>{t('auth:two_factor_auth')}</h2>
                    <p className='text-sm text-muted-foreground'>{t('auth:two_factor_check_device')}</p>

                    <Field
                        name={isMissingDevice ? 'recoveryCode' : 'code'}
                        title={isMissingDevice ? t('auth:recovery_code') : t('auth:auth_code')}
                        placeholder='000000'
                        description={
                            isMissingDevice
                                ? t('auth:recovery_code_desc')
                                : t('auth:two_factor_desc')
                        }
                        type='text'
                        autoComplete='one-time-code'
                        autoFocus
                    />

                    <Button
                        className='w-full'
                        size='lg'
                        type='submit'
                        disabled={isSubmitting}
                        isLoading={isSubmitting}
                    >
                        {t('auth:login')}
                    </Button>

                    <span
                        onClick={() => {
                            setFieldValue('code', '');
                            setFieldValue('recoveryCode', '');
                            setIsMissingDevice((s) => !s);
                        }}
                        className='block text-center text-xs text-muted-foreground hover:text-foreground transition-colors cursor-pointer'
                    >
                        {!isMissingDevice ? t('auth:lost_device') : t('auth:have_device')}
                    </span>

                    <Link
                        to={'/auth/login'}
                        className='block text-center text-xs text-muted-foreground hover:text-foreground transition-colors'
                    >
                        {t('auth:return_to_login')}
                    </Link>
                </LoginFormContainer>
            </CardContent>
        </Card>
    );
}

const EnhancedForm = withFormik<Props & { location: Location }, Values>({
    handleSubmit: ({ code, recoveryCode }, { setSubmitting, props: { clearAndAddHttpError, location } }) => {
        loginCheckpoint(location.state?.token || '', code, recoveryCode)
            .then((response) => {
                if (response.complete) {
                    window.location = response.intended || '/';
                    return;
                }

                setSubmitting(false);
            })
            .catch((error) => {
                console.error(error);
                setSubmitting(false);
                clearAndAddHttpError({ error });
            });
    },

    mapPropsToValues: () => ({
        code: '',
        recoveryCode: '',
    }),
})(LoginCheckpointForm);

const LoginCheckpointContainer = ({ ...props }: OwnProps) => {
    const { clearAndAddHttpError } = useFlash();

    const location = useLocation();
    const navigate = useNavigate();

    if (!location.state?.token) {
        navigate('/auth/login');

        return null;
    }

    return <EnhancedForm clearAndAddHttpError={clearAndAddHttpError} location={location} {...props} />;
};

export default LoginCheckpointContainer;
