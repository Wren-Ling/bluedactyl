import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

import enCommon from './locales/en/common.json';
import enServer from './locales/en/server.json';
import enConsole from './locales/en/console.json';
import enFiles from './locales/en/files.json';
import enDatabases from './locales/en/databases.json';
import enSchedules from './locales/en/schedules.json';
import enBackups from './locales/en/backups.json';
import enNetwork from './locales/en/network.json';
import enStartup from './locales/en/startup.json';
import enUsers from './locales/en/users.json';
import enSettings from './locales/en/settings.json';
import enDashboard from './locales/en/dashboard.json';
import enAccount from './locales/en/account.json';
import enAuth from './locales/en/auth.json';
import enShell from './locales/en/shell.json';

import zhCommon from './locales/zh/common.json';
import zhServer from './locales/zh/server.json';
import zhConsole from './locales/zh/console.json';
import zhFiles from './locales/zh/files.json';
import zhDatabases from './locales/zh/databases.json';
import zhSchedules from './locales/zh/schedules.json';
import zhBackups from './locales/zh/backups.json';
import zhNetwork from './locales/zh/network.json';
import zhStartup from './locales/zh/startup.json';
import zhUsers from './locales/zh/users.json';
import zhSettings from './locales/zh/settings.json';
import zhDashboard from './locales/zh/dashboard.json';
import zhAccount from './locales/zh/account.json';
import zhAuth from './locales/zh/auth.json';
import zhShell from './locales/zh/shell.json';



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
    return (window as any).PterodactylUser?.language || 'en';
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
        },

    },
    lng: language,
    fallbackLng: 'en',
    ns: [
        'common', 'server', 'console', 'files', 'databases', 'schedules',
        'backups', 'network', 'startup', 'users', 'settings',
        'dashboard', 'account', 'auth', 'shell',
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
