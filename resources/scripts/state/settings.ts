import { Action, action } from 'easy-peasy';

export interface AiSiteSettings {
    enabled: boolean;
    allow_console: boolean;
    allow_crash_analysis: boolean;
}

export interface SiteSettings {
    name: string;
    locale: string;
    timezone: string;
    captcha: {
        enabled: boolean;
        provider: string;
        siteKey: string;
        scriptIncludes: string[];
    };
    ai?: AiSiteSettings;
}

export interface SettingsStore {
    data?: SiteSettings;
    setSettings: Action<SettingsStore, SiteSettings>;
}

const settings: SettingsStore = {
    data: undefined,
    setSettings: action((state, payload) => {
        state.data = payload;
    }),
};

export default settings;
