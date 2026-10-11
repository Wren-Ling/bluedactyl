// Provides necessary information for components to function properly
// million-ignore
const BluedactylProvider = ({ children }) => {
    return (
        <div
            data-pyro-bluedactylprovider=''
            data-pyro-bluedactyl-version={import.meta.env.VITE_BLUEDACTYL_VERSION}
            data-pyro-bluedactyl-build={import.meta.env.VITE_BLUEDACTYL_BUILD_NUMBER}
            data-pyro-commit-hash={import.meta.env.VITE_COMMIT_HASH}
            style={{
                display: 'contents',
            }}
        >
            {children}
        </div>
    );
};

export default BluedactylProvider;
