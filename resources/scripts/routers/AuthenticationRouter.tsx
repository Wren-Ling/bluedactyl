import { Route, Routes } from 'react-router-dom';

import ForgotPasswordContainer from '@/components/auth/ForgotPasswordContainer';
import LoginCheckpointContainer from '@/components/auth/LoginCheckpointContainer';
import LoginContainer from '@/components/auth/LoginContainer';
import ResetPasswordContainer from '@/components/auth/ResetPasswordContainer';
import { NotFound } from '@/components/elements/ScreenBlock';

const AuthenticationRouter = () => {
    return (
        <Routes>
            <Route path='login' element={<LoginContainer />} />
            <Route path='login/checkpoint/*' element={<LoginCheckpointContainer />} />
            <Route path='password' element={<ForgotPasswordContainer />} />
            <Route path='password/reset/:token' element={<ResetPasswordContainer />} />
            <Route path='*' element={<NotFound />} />
        </Routes>
    );
};

export default AuthenticationRouter;
