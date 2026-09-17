import type { FormikHelpers } from 'formik';
import { Formik } from 'formik';
import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';
import { object, string } from 'yup';

import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { Button } from '@/components/ui/button';
import Captcha, { getCaptchaResponse } from '@/components/elements/Captcha';
import { Card, CardContent } from '@/components/ui/card';
import Field from '@/components/elements/Field';

import CaptchaManager from '@/lib/captcha';

import { httpErrorToHuman } from '@/api/http';
import http from '@/api/http';

import useFlash from '@/plugins/useFlash';

import Logo from '../elements/PyroLogo';

interface Values {
    email: string;
}

const ForgotPasswordContainer = () => {
    const { t } = useTranslation();
    const { clearFlashes, addFlash } = useFlash();

    const handleSubmission = ({ email }: Values, { setSubmitting, resetForm }: FormikHelpers<Values>) => {
        clearFlashes();

        // Get captcha response if enabled
        const captchaResponse = getCaptchaResponse();

        let requestData: any = { email };
        if (CaptchaManager.isEnabled() && captchaResponse) {
            const fieldName = CaptchaManager.getProviderInstance().getResponseFieldName();
            if (fieldName) {
                requestData = { ...requestData, [fieldName]: captchaResponse };
            }
        }

        http.post('/auth/password', requestData)
            .then((response) => {
                resetForm();
                addFlash({ type: 'success', title: t('auth:success'), message: response.data.status || t('auth:email_sent') });
            })
            .catch((error) => {
                console.error(error);
                addFlash({ type: 'error', title: t('auth:error'), message: httpErrorToHuman(error) });
            })
            .finally(() => {
                setSubmitting(false);
            });
    };

    return (
        <Card className='w-full max-w-sm mx-auto'>
            <CardContent className='p-6'>
                <Formik
                    onSubmit={handleSubmission}
                    initialValues={{ email: '' }}
                    validationSchema={object().shape({
                        email: string().email(t('auth:validation_email_format')).required(t('auth:validation_email_required')),
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
                                <Field id='email' label={t('auth:email')} name='email' type='email' />

                                <Captcha
                                    onError={(error) => {
                                        console.error('Captcha error:', error);
                                        addFlash({
                                            type: 'error',
                                            title: t('auth:error'),
                                            message: t('auth:captcha_verification_failed'),
                                        });
                                    }}
                                />

                                <Button
                                    className='w-full'
                                    type='submit'
                                    size='lg'
                                    isLoading={isSubmitting}
                                    disabled={isSubmitting}
                                >
                                    {t('auth:send_email')}
                                </Button>

                                <div className='text-center'>
                                    <Link
                                        to='/auth/login'
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
};

export default ForgotPasswordContainer;
