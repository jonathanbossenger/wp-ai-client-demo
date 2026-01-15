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

    const updateNotice = (message) => {
        const noticeElement = document.getElementById( 'wp-ai-sdk-demo-notice' );
        noticeElement.innerText = message;
    }

    const onChange = ( edits ) => {
        setInput( ( current ) => ( {
            ...current,
            ...edits,
        } ) );
    };

    const generateFromInput = useCallback( async () => {
        const ability = getAbility( 'wp-ai-sdk-demo/generate-post' );
        if ( ! ability ) {
            updateNotice('Whoops, post generation Ability not found.');
            return;
        }

        try {
            updateNotice('Attempting to execute post generation Ability, please hold for updates...');
            const result = await executeAbility( 'wp-ai-sdk-demo/generate-post', input );
        } catch ( err ) {
            updateNotice('Error during post generation. Check console for details.');
            console.error( err );
        } finally {
            updateNotice('Post generation completed!.');
        }
    }, [ input ] );

    return (
        <VStack spacing={ 4 }>
            <SettingsTitle/>
            <span id={"wp-ai-sdk-demo-notice"}>Ready to generate a post using AI?</span>
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