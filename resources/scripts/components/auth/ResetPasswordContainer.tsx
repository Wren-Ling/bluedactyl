import { Formik, FormikHelpers } from 'formik';
import { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Link, useParams } from 'react-router-dom';
import { object, ref, string } from 'yup';

import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { Button } from '@/components/ui/button';
import Captcha, { getCaptchaResponse } from '@/components/elements/Captcha';
import { Card, CardContent } from '@/components/ui/card';
import Field from '@/components/elements/Field';
import { Input } from '@/components/ui/input';

import CaptchaManager from '@/lib/captcha';

import performPasswordReset from '@/api/auth/performPasswordReset';

import useFlash from '@/plugins/useFlash';

import Logo from '../elements/PyroLogo';

interface Values {
    password: string;
    password_confirmation: string;
}

function ResetPasswordContainer() {
    const { t } = useTranslation();
    const [email, setEmail] = useState('');

    const { clearFlashes, clearAndAddHttpError } = useFlash();

    useEffect(() => {
        clearFlashes();
    }, []);

    const parsed = new URLSearchParams(location.search);
    if (email.length === 0 && parsed.get('email')) {
        setEmail(parsed.get('email') || '');
    }

    const params = useParams<'token'>();

    const submit = ({ password, password_confirmation }: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes();

        // Get captcha response if enabled
        const captchaResponse = getCaptchaResponse();

        let resetData: any = { token: params.token ?? '', password, password_confirmation };
        if (CaptchaManager.isEnabled()) {
            const fieldName = CaptchaManager.getProviderInstance().getResponseFieldName();

            console.log('Captcha enabled, response:', captchaResponse, 'fieldName:', fieldName);

            if (fieldName) {
                if (captchaResponse) {
                    resetData = {
                        ...resetData,
                        [fieldName]: captchaResponse,
                    };

                    console.log('Adding captcha to reset data:');
                    console.debug(resetData);
                } else {
                    console.error('Captcha enabled but no response available');
                    console.log(captchaResponse);
                    clearAndAddHttpError({ error: new Error(t('auth:please_complete_captcha')) });
                    setSubmitting(false);
                    return;
                }
            }
        } else {
            console.log('Captcha not enabled');
        }

        performPasswordReset(email, resetData)
            .then(() => {
                // @ts-expect-error this is valid
                window.location = '/';
            })
            .catch((error) => {
                console.error(error);

                setSubmitting(false);
                clearAndAddHttpError({
                    error: new Error(error),
                });
            });
    };

    return (
        <Card className='w-full max-w-sm mx-auto'>
            <CardContent className='p-6'>
                <Formik
                    onSubmit={submit}
                    initialValues={{
                        password: '',
                        password_confirmation: '',
                    }}
                    validationSchema={object().shape({
                        password: string()
                            .required(t('auth:validation_new_password_required'))
                            .min(8, t('auth:validation_password_min_length')),
                        password_confirmation: string()
                            .required(t('auth:validation_password_confirmation_required'))
                            .oneOf([ref('password')], t('auth:validation_password_match')),
                    })}
                >
                    {({ isSubmitting }) => (
                        <LoginFormContainer>
                            <Link to='/'>
                                <div className='flex justify-center py-2'>
                                    <Logo className='size-8 text-foreground' />
                                </div>
                            </Link>

                            <div className='space-y-4'>
                                <Input className='text-center' value={email} disabled />

                                <Field
                                    label={t('auth:new_password')}
                                    name='password'
                                    type='password'
                                    description={t('auth:password_length_hint')}
                                />

                                <Field label={t('auth:confirm_new_password')} name='password_confirmation' type='password' />

                                <Captcha
                                    onError={(error) => {
                                        console.error('Captcha error:', error);
                                        clearAndAddHttpError({
                                            error: new Error(t('auth:captcha_verification_failed')),
                                        });
                                    }}
                                />

                                <Button
                                    className='w-full'
                                    size='lg'
                                    type='submit'
                                    disabled={isSubmitting}
                                    isLoading={isSubmitting}
                                >
                                    {t('auth:reset_password')}
                                </Button>

                                <div className='text-center'>
                                    <Link
                                        to={'/auth/login'}
                                        className='text-xs text-muted-foreground hover:text-foreground transition-colors'
                                    >
                                        {t('auth:return_to_login')}
                                    </Link>
                                </div>
                            </div>
                        </LoginFormContainer>
                    )}
                </Formik>
            </CardContent>
        </Card>
    );
}

export default ResetPasswordContainer;
