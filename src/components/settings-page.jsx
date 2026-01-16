import { __ } from '@wordpress/i18n';
import {
    // eslint-disable-next-line @wordpress/no-unsafe-wp-apis
    __experimentalHeading as Heading,
    // eslint-disable-next-line @wordpress/no-unsafe-wp-apis
    __experimentalVStack as VStack,
    Button,
} from '@wordpress/components';
import { useState, useCallback } from "@wordpress/element";
import { DataForm } from '@wordpress/dataviews/wp';
import { getAbility, executeAbility } from '@wordpress/abilities';
import { updateNotice, CustomNotice } from './notice';

const SettingsTitle = () => {
    return (
        <Heading level={ 1 }>
            { __( 'WP AI SDK Demo', 'wp-ai-sdk-demo' ) }
        </Heading>
    );
};

const GenerateButton = ( { onClick } ) => {
    return (
        <div>
            <Button variant="primary" onClick={ onClick } __next40pxDefaultSize>
                { __( 'Generate', 'wp-ai-sdk-demo' ) }
            </Button>
        </div>
    );
};

const SettingsPage = () => {

    const [input, setInput] = useState({
        title: "",
        prompt: "",
    });

    const fields = [
        {
            id: 'title',
            label: __( 'Title', 'wp-ai-sdk-demo' ),
            type: 'text',
        },
        {
            id: 'prompt',
            label: __( 'Prompt', 'wp-ai-sdk-demo' ),
            type: 'text',
            Edit: 'textarea',
        }
    ];

    const form = {
        fields: [ 'title', 'prompt' ],
    };



    const onChange = ( edits ) => {
        setInput( ( current ) => ( {
            ...current,
            ...edits,
        } ) );
    };

    const generateFromInput = useCallback( async () => {
        const ability = getAbility( 'wp-ai-sdk-demo/generate-post' );
        if ( ! ability ) {
            updateNotice('Whoops, post generation Ability not found.', 'error' );
            return;
        }

        try {
            updateNotice('Attempting to execute post generation Ability, please hold for updates...', 'info' );
            const result = await executeAbility( 'wp-ai-sdk-demo/generate-post', input );
        } catch ( err ) {
            updateNotice('Error during post generation. Check console for details.', 'error' );
            console.error( err );
        } finally {
            updateNotice('Post generation completed!.', 'success' );
        }
    }, [ input ] );

    return (
        <VStack spacing={ 4 }>
            <SettingsTitle/>
            <CustomNotice />
            <DataForm
                data={ input }
                fields={ fields }
                form={ form }
                onChange={ onChange }
            />
            <GenerateButton onClick={ generateFromInput }/>
        </VStack>
    );
};

export { SettingsPage };