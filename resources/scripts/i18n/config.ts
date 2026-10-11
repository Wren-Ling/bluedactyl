import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

import enAccount from './locales/en/account.json';
import enAi from './locales/en/ai.json';
import enAuth from './locales/en/auth.json';
import enBackups from './locales/en/backups.json';
import enCommon from './locales/en/common.json';
import enConsole from './locales/en/console.json';
import enDashboard from './locales/en/dashboard.json';
import enDatabases from './locales/en/databases.json';
import enFiles from './locales/en/files.json';
import enNetwork from './locales/en/network.json';
import enSchedules from './locales/en/schedules.json';
import enServer from './locales/en/server.json';
import enSettings from './locales/en/settings.json';
import enShell from './locales/en/shell.json';
import enStartup from './locales/en/startup.json';
import enUsers from './locales/en/users.json';
import zhAccount from './locales/zh/account.json';
import zhAi from './locales/zh/ai.json';
import zhAuth from './locales/zh/auth.json';
import zhBackups from './locales/zh/backups.json';
import zhCommon from './locales/zh/common.json';
import zhConsole from './locales/zh/console.json';
import zhDashboard from './locales/zh/dashboard.json';
import zhDatabases from './locales/zh/databases.json';
import zhFiles from './locales/zh/files.json';
import zhNetwork from './locales/zh/network.json';
import zhSchedules from './locales/zh/schedules.json';
import zhServer from './locales/zh/server.json';
import zhSettings from './locales/zh/settings.json';
import zhShell from './locales/zh/shell.json';
import zhStartup from './locales/zh/startup.json';
import zhUsers from './locales/zh/users.json';

function getCookie(name: string): string | null {
    const match = document.cookie.match(new RegExp(`(?:^|;\\s*)${name}=([^;]*)`));
    return match ? decodeURIComponent(match[1]!) : null;
}

function setCookie(name: string, value: string, days = 365) {
    const expires = new Date(Date.now() + days * 864e5).toUTCString();
    document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/; SameSite=Lax`;
}

function getStoredLanguage(): string {
    const fromStorage = localStorage.getItem('language');
    if (fromStorage) return fromStorage;
    const fromCookie = getCookie('language');
    if (fromCookie) return fromCookie;
    return (window as any).PterodactylUser?.language || (window as any).SiteConfiguration?.locale || 'zh';
}

function persistLanguage(lng: string) {
    localStorage.setItem('language', lng);
    setCookie('language', lng);
}

const language = getStoredLanguage();

i18n.use(initReactI18next).init({
    resources: {
        en: {
            common: enCommon,
            server: enServer,
            console: enConsole,
            files: enFiles,
            databases: enDatabases,
            schedules: enSchedules,
            backups: enBackups,
            network: enNetwork,
            startup: enStartup,
            users: enUsers,
            settings: enSettings,
            dashboard: enDashboard,
            account: enAccount,
            auth: enAuth,
            shell: enShell,
            ai: enAi,
        },
        zh: {
            common: zhCommon,
            server: zhServer,
            console: zhConsole,
            files: zhFiles,
            databases: zhDatabases,
            schedules: zhSchedules,
            backups: zhBackups,
            network: zhNetwork,
            startup: zhStartup,
            users: zhUsers,
            settings: zhSettings,
            dashboard: zhDashboard,
            account: zhAccount,
            auth: zhAuth,
            shell: zhShell,
            ai: zhAi,
        },
    },
    lng: language,
    fallbackLng: 'zh',
    ns: [
        'common',
        'server',
        'console',
        'files',
        'databases',
        'schedules',
        'backups',
        'network',
        'startup',
        'users',
        'settings',
        'dashboard',
        'account',
        'auth',
        'shell',
    ],
    defaultNS: 'common',
    interpolation: {
        escapeValue: false,
    },
});

i18n.on('languageChanged', (lng) => {
    persistLanguage(lng);
});

export default i18n;
