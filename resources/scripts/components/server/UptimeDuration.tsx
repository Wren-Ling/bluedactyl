import { useTranslation } from 'react-i18next';

const UptimeDuration = ({ uptime }: { uptime: number }) => {
    const { t } = useTranslation();
    const uptimeDiv = uptime / 1000;
    const days = Math.floor(uptimeDiv / (24 * 60 * 60));
    const hours = Math.floor((Math.floor(uptimeDiv) / 60 / 60) % 24);
    const remainder = Math.floor(uptimeDiv - hours * 60 * 60);
    const minutes = Math.floor((remainder / 60) % 60);
    const seconds = remainder % 60;

    if (days > 0) {
        return (
            <>
                {days}{t('server:day')} {hours}{t('server:hour')} {minutes}{t('server:minute')}
            </>
        );
    }

    return (
        <>
            {hours}{t('server:hour')} {minutes}{t('server:minute')} {seconds}{t('server:second')}
        </>
    );
};

export default UptimeDuration;
