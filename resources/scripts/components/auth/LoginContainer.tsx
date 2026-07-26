import type { FormikHelpers } from 'formik';
import { Formik } from 'formik';
import { useEffect } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { object, string } from 'yup';

import LoginFormContainer from '@/components/auth/LoginFormContainer';
import { Button } from '@/components/ui/button';
import Captcha, { getCaptchaResponse } from '@/components/elements/Captcha';
import Field from '@/components/elements/Field';
import Logo from '@/components/elements/PyroLogo';

import CaptchaManager from '@/lib/captcha';

import login from '@/api/auth/login';

import useFlash from '@/plugins/useFlash';

interface Values {
    user: string;
    password: string;
}

function LoginContainer() {
    const { clearFlashes, clearAndAddHttpError } = useFlash();
    const navigate = useNavigate();

    useEffect(() => {
        clearFlashes();
    }, []);

    const onSubmit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes();

        // Get captcha response if enabled
        let loginData: any = values;
        if (CaptchaManager.isEnabled()) {
            const captchaResponse = getCaptchaResponse();
            const fieldName = CaptchaManager.getProviderInstance().getResponseFieldName();

            console.log('Captcha enabled, response:', captchaResponse, 'fieldName:', fieldName);

            if (fieldName) {
                if (captchaResponse) {
                    loginData = { ...values, [fieldName]: captchaResponse };
                    console.log('Adding captcha to login data:', loginData);
                } else {
                    // Captcha is enabled but no response - show error
                    console.error('Captcha enabled but no response available');
                    clearAndAddHttpError({ error: new Error('Please complete the captcha verification.') });
                    setSubmitting(false);
                    return;
                }
            }
        } else {
            console.log('Captcha not enabled');
        }

        login(loginData)
            .then((response) => {
                if (response.complete) {
                    window.location.href = response.intended || '/';
                    return;
                }
                navigate('/auth/login/checkpoint', { state: { token: response.confirmationToken } });
            })
            .catch((error: any) => {
                setSubmitting(false);

                if (error.code === 'InvalidCredentials') {
                    clearAndAddHttpError({ error: new Error('Invalid username or password. Please try again.') });
                } else if (error.code === 'DisplayException') {
                    clearAndAddHttpError({ error: new Error(error.detail || error.message) });
                } else {
                    clearAndAddHttpError({ error });
                }
            });
    };

    return (
        <Formik
            onSubmit={onSubmit}
            initialValues={{ user: '', password: '' }}
            validationSchema={object().shape({
                user: string().required('A username or email must be provided.'),
                password: string().required('Please enter your account password.'),
            })}
        >
            {({ isSubmitting }) => (
                <LoginFormContainer>
                    <div className='flex justify-center py-2'>
                        <Logo className='size-8 text-foreground' />
                    </div>
                    <div className='space-y-4'>
                        <Field id='user' type='text' label='Username or Email' name='user' disabled={isSubmitting} />

                        <div className='relative'>
                            <Field
                                id='password'
                                type='password'
                                label='Password'
                                name='password'
                                disabled={isSubmitting}
                            />
                            <Link
                                to={'/auth/password'}
                                className='absolute right-0 top-0 text-xs text-muted-foreground hover:text-foreground transition-colors'
                            >
                                Forgot?
                            </Link>
                        </div>

                        <Captcha
                            onError={(error) => {
                                console.error('Captcha error:', error);
                                clearAndAddHttpError({
                                    error: new Error('Captcha verification failed. Please try again.'),
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
                            Login
                        </Button>
                    </div>
                </LoginFormContainer>
            )}
        </Formik>
    );
}

export default LoginContainer;
