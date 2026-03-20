import { __ } from '@wordpress/i18n';
import {
    // eslint-disable-next-line @wordpress/no-unsafe-wp-apis
    __experimentalHeading as Heading,
    // eslint-disable-next-line @wordpress/no-unsafe-wp-apis
    __experimentalVStack as VStack,
    Button,
    CheckboxControl,
    Notice
} from '@wordpress/components';
import { useState, useEffect, useCallback } from "@wordpress/element";
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { DataForm } from '@wordpress/dataviews/wp';

// Uses /* webpackIgnore: true */ to tell webpack to skip bundling this import and leave it as a runtime ES module import.
// Needed until https://github.com/WordPress/gutenberg/issues/75196 is fixed
const { getAbility, executeAbility } = await import( /* webpackIgnore: true */ '@wordpress/abilities' );

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

    const [ noticeStatus, setNoticeStatus ] = useState( 'info' );
    const [ noticeMessage, setNoticeMessage ] = useState( 'Ready...' );

    const [input, setInput] = useState({
        title: "",
        prompt: "",
        context: [],
    });

    const posts = useSelect( ( select ) => {
        return select( coreStore ).getEntityRecords( 'postType', 'post', {
            per_page: 25,
            orderby: 'date',
            order: 'desc',
            status: 'publish',
        } );
    }, [] );

    const postElements = ( posts || [] ).map( ( post ) => ( {
        value: post.id,
        label: post.title.rendered,
    } ) );

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
        },
        {
            id: "context",
            label: __( 'Context (optional), select posts to include', 'wp-ai-client-demo' ),
            Edit: ( { data, field, onChange } ) => {
                const selected = data.context || [];
                return (
                    <fieldset>
                        <legend>{ field.label }</legend>
                        { postElements.map( ( element ) => (
                            <CheckboxControl
                                key={ element.value }
                                label={ element.label }
                                checked={ selected.includes( element.value ) }
                                onChange={ ( isChecked ) => {
                                    const updated = isChecked
                                        ? [ ...selected, element.value ]
                                        : selected.filter( ( v ) => v !== element.value );
                                    onChange( { context: updated } );
                                } }
                                __nextHasNoMarginBottom
                            />
                        ) ) }
                    </fieldset>
                );
            },
        }
    ];

    const form = {
        fields: [ 'title', 'prompt', 'context' ],
    };

    useEffect( () => {
        async function updateMessage() {
            const text = await wp.aiClient.prompt('A simple sentence encouraging the user to create a WordPress Post using AI.').generateText();
            setNoticeMessage( text );
        }
        updateMessage();
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
        const generatePostAbility = getAbility( 'wp-ai-client-demo/generate-post' );
        if ( ! generatePostAbility ) {
            updateNotice('Whoops, post generation Ability not found.', 'error' );
            return;
        }
        try {
            updateNotice('Attempting to execute post generation Ability, please hold for updates...', 'info' );
            const result = await executeAbility( 'wp-ai-client-demo/generate-post', input );
            console.log(result);
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