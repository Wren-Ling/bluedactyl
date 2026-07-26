import { Formik, FormikHelpers } from 'formik';
import { useEffect, useState } from 'react';
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
                    clearAndAddHttpError({ error: new Error('Please complete the captcha verification.') });
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
                            .required('A new password is required.')
                            .min(8, 'Your new password should be at least 8 characters in length.'),
                        password_confirmation: string()
                            .required('Your new password does not match.')
                            .oneOf([ref('password')], 'Your new password does not match.'),
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
                                    label='New Password'
                                    name='password'
                                    type='password'
                                    description='Passwords must be at least 8 characters in length.'
                                />

                                <Field label='Confirm New Password' name='password_confirmation' type='password' />

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
                                    size='lg'
                                    type='submit'
                                    disabled={isSubmitting}
                                    isLoading={isSubmitting}
                                >
                                    Reset Password
                                </Button>

                                <div className='text-center'>
                                    <Link
                                        to={'/auth/login'}
                                        className='text-xs text-muted-foreground hover:text-foreground transition-colors'
                                    >
                                        Return to Login
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
