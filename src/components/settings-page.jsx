import { __ } from '@wordpress/i18n';
import {
    // eslint-disable-next-line @wordpress/no-unsafe-wp-apis
    __experimentalHeading as Heading,
    // eslint-disable-next-line @wordpress/no-unsafe-wp-apis
    __experimentalVStack as VStack,
    Button,
    Notice
} from '@wordpress/components';
import { useState, useEffect, useCallback } from "@wordpress/element";
import { DataForm } from '@wordpress/dataviews/wp';
import( '@wordpress/core-abilities' );


const SettingsTitle = () => {
    return (
        <Heading level={ 1 }>
            { __( 'WP AI SDK Demo', 'wp-ai-client-demo' ) }
        </Heading>
    );
};

const GenerateButton = ( { onClick } ) => {
    return (
        <div>
            <Button variant="primary" onClick={ onClick } __next40pxDefaultSize>
                { __( 'Generate', 'wp-ai-client-demo' ) }
            </Button>
        </div>
    );
};

const SettingsPage = () => {
    const [ abilitiesAPI, setAbilitiesAPI ] = useState();

    useEffect( () => {
        import( '@wordpress/abilities' ).then( ( module ) => {
            setAbilitiesAPI( module );
        } );
    }, [] );


    const [ noticeStatus, setNoticeStatus ] = useState( 'info' );
    const [ noticeMessage, setNoticeMessage ] = useState( 'Ready...' );

    const [input, setInput] = useState({
        title: "",
        prompt: "",
    });

    const fields = [
        {
            id: 'title',
            label: __( 'Title', 'wp-ai-client-demo' ),
            type: 'text',
        },
        {
            id: 'prompt',
            label: __( 'Prompt', 'wp-ai-client-demo' ),
            type: 'text',
            Edit: 'textarea',
        }
    ];

    const form = {
        fields: [ 'title', 'prompt' ],
    };

    useEffect( async () => {
        const text = await wp.aiClient.prompt('A simple sentence encouraging the user to create a WordPress Post using AI.').generateText();
        setNoticeMessage( text );
    }, [] );

    const updateNotice = ( message, status = 'info' ) => {
        setNoticeMessage( message );
        setNoticeStatus( status );
    }

    const onChange = ( edits ) => {
        setInput( ( current ) => ( {
            ...current,
            ...edits,
        } ) );
    };

    const generateFromInput = useCallback( async () => {
        const ability = getAbility( 'wp-ai-client-demo/generate-post' );
        if ( ! ability ) {
            updateNotice('Whoops, post generation Ability not found.', 'error' );
            return;
        }
        try {
            updateNotice('Attempting to execute post generation Ability, please hold for updates...', 'info' );
            const result = await executeAbility( 'wp-ai-client-demo/generate-post', input );
        } catch ( err ) {
            updateNotice('Error during post generation. Check console for details.', 'error' );
            console.error( err );
        } finally {
            updateNotice('Post generation completed!.', 'success' );
        }
    }, [ input ] );

    if( ! abilitiesAPI ) {
        return null;
    }
    console.log( 'Abilities API:', abilitiesAPI );
        setTimeout(
        () => console.log( 'Abilities API timeout:', abilitiesAPI.getAbilities() )    
        , 1000);

    return (
        <VStack spacing={ 4 }>
            <SettingsTitle/>
            <Notice status={ noticeStatus }>
                { noticeMessage }
            </Notice>
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