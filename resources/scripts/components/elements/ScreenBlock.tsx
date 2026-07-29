import { useTranslation } from 'react-i18next';
import { Link } from 'react-router-dom';

const ScreenBlock = ({ title, message }) => {
    return (
        <>
            <div className='w-full h-full flex gap-12 items-center p-8 max-w-3xl mx-auto'>
                <div className='flex flex-col gap-8 max-w-sm text-left'>
                    <h1 className='text-[32px] font-extrabold leading-[98%] tracking-[-0.11rem]'>{title}</h1>
                    <p className=''>{message}</p>
                </div>
            </div>
        </>
    );
};

const ServerError = ({ title, message }) => {
    return (
        <>
            <div className='w-full h-full flex gap-12 items-center p-8 max-w-3xl mx-auto'>
                <div className='flex flex-col gap-8 max-w-sm text-left'>
                    <h1 className='text-[32px] font-extrabold leading-[98%] tracking-[-0.11rem]'>{title}</h1>
                    <p className=''>{message}</p>
                </div>
            </div>
        </>
    );
};

const NotFound = () => {
    const { t } = useTranslation();

    return (
        <>
            <div className='w-full h-full flex gap-12 items-center p-8 max-w-3xl mx-auto'>
                <div className='flex flex-col gap-8 max-w-sm text-left'>
                    <h1 className='text-[32px] font-extrabold leading-[98%] tracking-[-0.11rem]'>{t('common:page_not_found')}</h1>
                    <p className=''>
                        {t('common:page_not_found_message')}
                    </p>
                    <div className='flex flex-col gap-2'>
                        <Link to={'/'} className='text-foreground'>
                            {t('common:your_servers')}
                        </Link>
                    </div>
                </div>
                <img
                    alt=''
                    className='w-64 rounded-2xl'
                    height='256'
                    src='https://media.tenor.com/scX-kVPwUn8AAAAC/this-is-fine.gif'
                    width='256'
                    loading='lazy'
                    decoding='async'
                />
            </div>
        </>
    );
};

export { ServerError, NotFound };
export default ScreenBlock;
