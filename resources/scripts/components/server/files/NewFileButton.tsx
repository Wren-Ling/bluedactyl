import { NavLink } from 'react-router-dom';

import { Button } from '@/components/ui/button';

const NewFileButton = ({ id }: { id: string }) => {
    return (
        <NavLink to={`/server/${id}/files/new${window.location.hash}`}>
            <Button variant='secondary'>
                New File
            </Button>
        </NavLink>
    );
};

export default NewFileButton;
