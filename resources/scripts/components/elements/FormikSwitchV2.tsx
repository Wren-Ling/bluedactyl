import { Field, FieldProps } from 'formik';

import FormikFieldWrapper from '@/components/elements/FormikFieldWrapper';
import ItemContainer from '@/components/elements/ItemContainer';
import { Switch } from '@/components/ui/switch';

interface SwitchProps {
    name: string;
    label: string;
    description: string;
    defaultChecked?: boolean;
    readOnly?: boolean;
    onChange?: (e: React.ChangeEvent<HTMLInputElement>) => void;
}

const FormikSwitch = ({ name, label, description, defaultChecked, readOnly, onChange: onChangeProp, ...props }: SwitchProps) => {
    return (
        <FormikFieldWrapper name={name}>
            <Field name={name}>
                {({ field, form }: FieldProps) => {
                    const handleChange = () => {
                        form.setFieldTouched(name);
                        form.setFieldValue(field.name, !field.value);
                    };

                    return (
                        <ItemContainer title={label} description={description}>
                            <Switch
                                name={name}
                                onCheckedChange={(checked) => {
                                    handleChange();
                                    if (onChangeProp) {
                                        onChangeProp({
                                            target: { checked } as HTMLInputElement,
                                        } as React.ChangeEvent<HTMLInputElement>);
                                    }
                                }}
                                defaultChecked={field.value}
                                disabled={readOnly}
                            />
                        </ItemContainer>
                    );
                }}
            </Field>
        </FormikFieldWrapper>
    );
};

export default FormikSwitch;
