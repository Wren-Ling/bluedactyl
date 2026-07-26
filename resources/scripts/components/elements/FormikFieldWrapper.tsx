import { Field, FieldProps } from 'formik';
import { FormikErrors, FormikTouched } from 'formik';

import Label from '@/components/elements/Label';

import { capitalize } from '@/lib/strings';

interface Props {
    id?: string;
    name: string;
    children: React.ReactNode;
    className?: string;
    label?: string;
    description?: string;
    validate?: (value: any) => undefined | string | Promise<any>;
}

const InputError = ({
    errors,
    touched,
    name,
    children,
}: {
    errors: FormikErrors<any>;
    touched: FormikTouched<any>;
    name: string;
    children?: string | number | null | undefined;
}) =>
    touched[name] && errors[name] ? (
        <p className='text-xs text-red-400 pt-2'>
            {typeof errors[name] === 'string'
                ? capitalize(errors[name] as string)
                : capitalize((errors[name] as unknown as string[])[0] ?? '')}
        </p>
    ) : (
        <>{children ? <p className='text-xs text-zinc-400 pt-2'>{children}</p> : null}</>
    );

const FormikFieldWrapper = ({ id, name, label, className, description, validate, children }: Props) => (
    <Field name={name} validate={validate}>
        {({ field, form: { errors, touched } }: FieldProps) => (
            <div className={`${className} ${touched[field.name] && errors[field.name] ? 'has-error' : undefined}`}>
                {label && (
                    <Label className='text-sm text-muted-foreground' htmlFor={id}>
                        {label}
                    </Label>
                )}
                {children}
                <InputError errors={errors} touched={touched} name={field.name}>
                    {description || null}
                </InputError>
            </div>
        )}
    </Field>
);

export default FormikFieldWrapper;
