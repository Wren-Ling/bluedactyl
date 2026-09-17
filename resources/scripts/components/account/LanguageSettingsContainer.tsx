import i18n from '@/i18n/config';
import { useTranslation } from 'react-i18next';

const LANGUAGES: Record<string, string> = {
    en: 'English',
    zh: '中文',
};

const LanguageSettingsContainer = () => {
    const { t } = useTranslation();

    const currentLanguage = i18n.language;

    const handleChange = (lang: string) => {
        i18n.changeLanguage(lang);
    };

    return (
        <div className='mx-auto flex w-full max-w-[120rem] flex-1 flex-col gap-4 px-2 py-2 sm:px-14 sm:py-14'>
            <div className='flex flex-col gap-6 mb-10 select-none'>
                <div className='flex justify-between gap-4 flex-col sm:flex-row sm:items-start'>
                    <div className='flex items-center gap-3 min-w-0 flex-wrap flex-1'>
                        <h1 className='text-2xl sm:text-3xl font-semibold tracking-tight'>{t('account:language_title')}</h1>
                    </div>
                </div>
                <div>
                    <p className='text-sm leading-relaxed text-muted-foreground'>{t('account:language_desc')}</p>
                </div>
            </div>

            <div className='rounded-xl border bg-card p-3 text-card-foreground shadow-sm sm:p-4'>
                <div className='space-y-4'>
                    {Object.entries(LANGUAGES).map(([code, label]) => (
                        <label
                            key={code}
                            className={`flex items-center gap-3 rounded-lg border p-4 cursor-pointer transition-colors hover:bg-muted/50 ${
                                currentLanguage === code
                                    ? 'border-primary bg-primary/5'
                                    : 'border-border'
                            }`}
                        >
                            <input
                                type='radio'
                                name='language'
                                value={code}
                                checked={currentLanguage === code}
                                onChange={() => handleChange(code)}
                                className='size-4'
                            />
                            <p className='font-medium'>{label}</p>
                        </label>
                    ))}
                </div>
            </div>
        </div>
    );
};

export default LanguageSettingsContainer;
