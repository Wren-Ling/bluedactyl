import { Actions, useStoreActions } from 'easy-peasy';
import { Field, Form, Formik, FormikHelpers } from 'formik';
import { useState } from 'react';
import { object, string } from 'yup';

import FlashMessageRender from '@/components/FlashMessageRender';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import FormikFieldWrapper from '@/components/elements/FormikFieldWrapper';
import { Input } from '@/components/ui/input';
import SpinnerOverlay from '@/components/elements/SpinnerOverlay';

import { createSSHKey } from '@/api/account/ssh-keys';
import { useSSHKeys } from '@/api/account/ssh-keys';
import { httpErrorToHuman } from '@/api/http';

import { ApplicationStore } from '@/state';

interface Values {
    name: string;
    publicKey: string;
}

const CreateSSHKeyForm = () => {
    const [sshKey, setSshKey] = useState('');
    const { addError, clearFlashes } = useStoreActions((actions: Actions<ApplicationStore>) => actions.flashes);
    const { mutate } = useSSHKeys();

    const submit = (values: Values, { setSubmitting, resetForm }: FormikHelpers<Values>) => {
        clearFlashes('ssh-keys');
        createSSHKey(values.name, values.publicKey)
            .then((key) => {
                resetForm();
                setSubmitting(false);
                setSshKey(`${key.name}`);
                mutate((data) => (data || []).concat(key));
            })
            .catch((error) => {
                console.error(error);
                addError({ key: 'ssh-keys', message: httpErrorToHuman(error) });
                setSubmitting(false);
            });
    };

    return (
        <>
            <FlashMessageRender byKey='account' />

            <Card>
                <CardContent className='pt-6'>
                    <Formik
                        onSubmit={submit}
                        initialValues={{ name: '', publicKey: '' }}
                        validationSchema={object().shape({
                            name: string().required('SSH Key Name is required'),
                            publicKey: string().required('Public Key is required'),
                        })}
                    >
                        {({ isSubmitting }) => (
                            <Form className='space-y-6'>
                                <SpinnerOverlay visible={isSubmitting} />

                                <FormikFieldWrapper
                                    label='SSH Key Name'
                                    name='name'
                                    description='A name to identify this SSH key.'
                                >
                                    <Field name='name' as={Input} className='w-full' />
                                </FormikFieldWrapper>

                                <FormikFieldWrapper
                                    label='Public Key'
                                    name='publicKey'
                                    description='Enter your public SSH key.'
                                >
                                    <Field name='publicKey' as={Input} className='w-full' />
                                </FormikFieldWrapper>

                                <div className='flex justify-end'>
                                    <Button type='submit' disabled={isSubmitting}>
                                        {isSubmitting ? 'Creating...' : 'Create SSH Key'}
                                    </Button>
                                </div>
                            </Form>
                        )}
                    </Formik>
                </CardContent>
            </Card>
        </>
    );
};

export default CreateSSHKeyForm;
