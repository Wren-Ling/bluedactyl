// Because of how react-router, react lazy, and signals work with each other
// the only way to prevent mismatching and weird errors is to import the lib
// in the root first. The github issue for this is still open. Stupid.
// https://github.com/preactjs/signals/issues/414
import '@/assets/tailwind.css';
import '@preact/signals-react';
import '@/i18n/config';
import { StoreProvider } from 'easy-peasy';
import { lazy } from 'react';
import { BrowserRouter, Route, Routes } from 'react-router-dom';
import { Toaster } from '@/components/ui/sonner';
import { ThemeProvider } from '@/components/ui/theme-provider';
import { TooltipProvider } from '@/components/ui/tooltip';

import AuthenticatedRoute from '@/components/elements/AuthenticatedRoute';
import { NotFound } from '@/components/elements/ScreenBlock';
import Spinner from '@/components/elements/Spinner';

import { store } from '@/state';
import { ServerContext } from '@/state/server';
import { SiteSettings } from '@/state/settings';

import PyrodactylProvider from './PyrodactylProvider';

const DashboardRouter = lazy(() => import('@/routers/DashboardRouter'));
const ServerRouter = lazy(() => import('@/routers/ServerRouter'));
const AuthenticationRouter = lazy(() => import('@/routers/AuthenticationRouter'));

interface ExtendedWindow extends Window {
    SiteConfiguration?: SiteSettings;
    PterodactylUser?: {
        uuid: string;
        username: string;
        email: string;

        root_admin: boolean;
        use_totp: boolean;
        language: string;
        updated_at: string;
        created_at: string;
    };
}

const App = () => {
    const { PterodactylUser, SiteConfiguration } = window as ExtendedWindow;
    if (PterodactylUser && !store.getState().user.data) {
        store.getActions().user.setUserData({
            uuid: PterodactylUser.uuid,
            username: PterodactylUser.username,
            email: PterodactylUser.email,
            language: PterodactylUser.language,
            rootAdmin: PterodactylUser.root_admin,
            useTotp: PterodactylUser.use_totp,
            createdAt: new Date(PterodactylUser.created_at),
            updatedAt: new Date(PterodactylUser.updated_at),
        });
    }

    if (!store.getState().settings.data) {
        store.getActions().settings.setSettings(SiteConfiguration!);
    }

    return (
        <>
            <StoreProvider store={store}>
                <ThemeProvider defaultTheme='dark'>
                    <TooltipProvider>
                    <PyrodactylProvider>
                        <Toaster />
                        <BrowserRouter>
                            <Routes>
                                <Route
                                    path='/auth/*'
                                    element={
                                        <Spinner.Suspense>
                                            <AuthenticationRouter />
                                        </Spinner.Suspense>
                                    }
                                />

                                <Route
                                    path='/server/:id/*'
                                    element={
                                        <AuthenticatedRoute>
                                            <Spinner.Suspense>
                                                <ServerContext.Provider>
                                                    <ServerRouter />
                                                </ServerContext.Provider>
                                            </Spinner.Suspense>
                                        </AuthenticatedRoute>
                                    }
                                />

                                <Route
                                    path='/*'
                                    element={
                                        <AuthenticatedRoute>
                                            <Spinner.Suspense>
                                                <DashboardRouter />
                                            </Spinner.Suspense>
                                        </AuthenticatedRoute>
                                    }
                                />

                                <Route path='*' element={<NotFound />} />
                            </Routes>
                        </BrowserRouter>
                    </PyrodactylProvider>
                    </TooltipProvider>
                </ThemeProvider>
            </StoreProvider>
        </>
    );
};

export default App;
