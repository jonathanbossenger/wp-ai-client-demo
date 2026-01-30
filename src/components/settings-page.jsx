import { __ } from '@wordpress/i18n';
import {
    // eslint-disable-next-line @wordpress/no-unsafe-wp-apis
    __experimentalHeading as Heading,
    // eslint-disable-next-line @wordpress/no-unsafe-wp-apis
    __experimentalVStack as VStack,
    Button,
    Notice,
    Spinner
} from '@wordpress/components';
import { useState, useEffect, useCallback } from "@wordpress/element";
import { DataForm } from '@wordpress/dataviews/wp';
import { getAbility, executeAbility } from '@wordpress/abilities';

const SettingsTitle = () => {
    return (
        <Heading level={ 1 }>
            { __( 'WP AI SDK Demo', 'wp-ai-client-demo' ) }
        </Heading>
    );
};

const GenerateButton = ( { onClick, disabled } ) => {
    return (
        <div>
            <Button variant="primary" onClick={ onClick } disabled={ disabled } __next40pxDefaultSize>
                { __( 'Generate', 'wp-ai-client-demo' ) }
            </Button>
        </div>
    );
};

const SettingsPage = () => {

    const [ noticeStatus, setNoticeStatus ] = useState( 'info' );
    const [ noticeMessage, setNoticeMessage ] = useState( 'Ready...' );
    const [ isGenerating, setIsGenerating ] = useState( false );

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

    useEffect( () => {
        const fetchInitialMessage = async () => {
            try {
                const text = await wp.aiClient.prompt('A short sentence encouraging the user to create a WordPress Post using AI.').generateText();
                setNoticeMessage( text );
            } catch ( error ) {
                console.error( 'Error fetching initial message:', error );
                setNoticeMessage( 'Ready to generate content!' );
            }
        };
        fetchInitialMessage();
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
            setIsGenerating( true );
            updateNotice('Initializing post generation...', 'info' );
            
            // Simulate granular progress updates
            await new Promise( resolve => setTimeout( resolve, 500 ) );
            updateNotice('Preparing AI client...', 'info' );
            
            await new Promise( resolve => setTimeout( resolve, 500 ) );
            updateNotice('Executing ability with your prompt...', 'info' );
            
            const result = await executeAbility( 'wp-ai-client-demo/generate-post', input );
            
            updateNotice('Finalizing post generation...', 'info' );
            await new Promise( resolve => setTimeout( resolve, 300 ) );
            
            updateNotice('Post generation completed successfully!', 'success' );
        } catch ( err ) {
            updateNotice('Error during post generation. Check console for details.', 'error' );
            console.error( err );
        } finally {
            setIsGenerating( false );
        }
    }, [ input ] );

    return (
        <VStack spacing={ 4 }>
            <SettingsTitle/>
            <Notice status={ noticeStatus }>
                { isGenerating && <Spinner /> }
                { noticeMessage }
            </Notice>
            <DataForm
                data={ input }
                fields={ fields }
                form={ form }
                onChange={ onChange }
            />
            <GenerateButton onClick={ generateFromInput } disabled={ isGenerating }/>
        </VStack>
    );
};

export { SettingsPage };