import type { ActionCreator } from 'easy-peasy';
import { useFormikContext, withFormik } from 'formik';
import { useState } from 'react';
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
                    <h2 className='text-lg font-semibold tracking-tight'>Two-Factor Authentication</h2>
                    <p className='text-sm text-muted-foreground'>Check your device for the authentication code.</p>

                    <Field
                        name={isMissingDevice ? 'recoveryCode' : 'code'}
                        title={isMissingDevice ? 'Recovery Code' : 'Authentication Code'}
                        placeholder='000000'
                        description={
                            isMissingDevice
                                ? 'Enter one of the recovery codes generated when you setup 2-Factor authentication on this account in order to continue.'
                                : 'Enter the two-factor token displayed by your device.'
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
                        Login
                    </Button>

                    <span
                        onClick={() => {
                            setFieldValue('code', '');
                            setFieldValue('recoveryCode', '');
                            setIsMissingDevice((s) => !s);
                        }}
                        className='block text-center text-xs text-muted-foreground hover:text-foreground transition-colors cursor-pointer'
                    >
                        {!isMissingDevice ? "I've Lost My Device" : 'I Have My Device'}
                    </span>

                    <Link
                        to={'/auth/login'}
                        className='block text-center text-xs text-muted-foreground hover:text-foreground transition-colors'
                    >
                        Return to Login
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
