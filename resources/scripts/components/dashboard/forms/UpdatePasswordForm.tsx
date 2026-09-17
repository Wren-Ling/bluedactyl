import { Actions, State, useStoreActions, useStoreState } from 'easy-peasy';
import { Form, Formik, FormikHelpers } from 'formik';
import { Fragment } from 'react';
import { useTranslation } from 'react-i18next';
import * as Yup from 'yup';

import { Button } from '@/components/ui/button';
import Field from '@/components/elements/Field';
import Spinner from '@/components/elements/Spinner';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';

import updateAccountPassword from '@/api/account/updateAccountPassword';
import { httpErrorToHuman } from '@/api/http';

import { ApplicationStore } from '@/state';

interface Values {
    current: string;
    password: string;
    confirmPassword: string;
}

const UpdatePasswordForm = () => {
    const { t } = useTranslation();
    const user = useStoreState((state: State<ApplicationStore>) => state.user.data);
    const { clearFlashes, addFlash } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);

    const schema = Yup.object().shape({
        current: Yup.string().min(1).required(t('account:password_required')),
        password: Yup.string().min(8).required(),
        confirmPassword: Yup.string().test('password', t('account:password_mismatch'), function (value) {
            return value === this.parent.password;
        }),
    });

    if (!user) {
        return null;
    }

    const submit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        clearFlashes('account:password');
        updateAccountPassword({ ...values })
            .then(() => {
                // @ts-expect-error this is valid
                window.location = '/auth/login';
            })
            .catch((error) =>
                addFlash({
                    key: 'account:password',
                    type: 'error',
                    title: 'Error',
                    message: httpErrorToHuman(error),
                }),
            )
            .then(() => setSubmitting(false));
    };

    return (
        <Fragment>
            <Formik
                onSubmit={submit}
                validationSchema={schema}
                initialValues={{ current: '', password: '', confirmPassword: '' }}
            >
                {({ isSubmitting, isValid }) => (
                    <Fragment>
                        <SpinnerOverlay size={'large'} visible={isSubmitting} />
                        <Form className={`m-0`}>
                            <Field
                                id={'current_password'}
                                type={'password'}
                                name={'current'}
                                label={t('account:current_password_label')}
                            />
                            <div className={`mt-6`}>
                                <Field
                                    id={'new_password'}
                                    type={'password'}
                                    name={'password'}
                                    label={t('account:new_password_label')}
                                    description={t('account:password_help')}
                                />
                            </div>
                            <div className={`mt-6`}>
                                <Field
                                    id={'confirm_new_password'}
                                    type={'password'}
                                    name={'confirmPassword'}
                                    label={t('account:confirm_new_password_label')}
                                />
                            </div>
                            <div className={`mt-6`}>
                                <Button disabled={isSubmitting || !isValid}>
                                    {isSubmitting && <Spinner size='small' />}
                                    {isSubmitting ? t('account:updating') : t('account:update_password')}
                                </Button>
                            </div>
                        </Form>
                    </Fragment>
                )}
            </Formik>
        </Fragment>
    );
};

export default UpdatePasswordForm;
