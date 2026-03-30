import { __ } from '@wordpress/i18n';
import {
    // eslint-disable-next-line @wordpress/no-unsafe-wp-apis
    __experimentalHeading as Heading,
    // eslint-disable-next-line @wordpress/no-unsafe-wp-apis
    __experimentalVStack as VStack,
    Button,
    CheckboxControl,
    Notice,
    TabPanel
} from '@wordpress/components';
import { useState, useEffect, useCallback } from "@wordpress/element";
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { DataForm } from '@wordpress/dataviews/wp';
import { decodeEntities } from '@wordpress/html-entities';
// Uses /* webpackIgnore: true */ to tell webpack to skip bundling this import and leave it as a runtime ES module import.
// Needed until https://github.com/WordPress/gutenberg/issues/75196 is fixed
const { getAbility, executeAbility } = await import( /* webpackIgnore: true */ '@wordpress/abilities' );

const SettingsTitle = () => {
    return (
        <Heading level={ 1 }>
            { __( 'WP AI Client Demo', 'wp-ai-client-demo' ) }
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

const GenerateWritingStyleButton = ( { onClick } ) => {
    return (
        <div>
            <Button variant="primary" onClick={ onClick } __next40pxDefaultSize>
                { __( 'Generate Writing Style', 'wp-ai-client-demo' ) }
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
        writingStyle: "",
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

    const writingStyleAbility = useSelect(
        ( select ) => select( 'core/abilities' ).getAbility( 'wp-ai-client-demo/get-writing-style' ),
        []
    );

    const postElements = ( posts || [] ).map( ( post ) => ( {
        value: post.id,
        label: decodeEntities( post.title.rendered ),
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
            id: 'writingStyle',
            label: __( 'Writing Style', 'wp-ai-client-demo' ),
            type: 'text',
            Edit: 'textarea',
        },
        {
            id: "context",
            label: __( 'Context: Select Posts to generate writing style.', 'wp-ai-client-demo' ),
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
                            />
                        ) ) }
                    </fieldset>
                );
            },
        }
    ];

    const generateForm = {
        fields: [ 'title', 'prompt' ],
    };

    const writingStyleForm = {
        fields: [ 'writingStyle', 'context' ],
    };

    const tabs = [
        { name: 'generate', title: __( 'Generate Post', 'wp-ai-client-demo' ) },
        { name: 'writing-style', title: __( 'Writing Style', 'wp-ai-client-demo' ) },
    ];

    useEffect( () => {
        async function loadInstructionsMessage() {
            let prompt = '';
            prompt += 'A simple sentence encouraging the user to create a WordPress Post using AI. ';
            prompt += 'Only return the actual sentence. Do not include any additional text or formatting.';
            const text = await wp.aiClient.prompt(prompt).generateText();
            setNoticeMessage( text );
        }
        loadInstructionsMessage();

    }, [] );

    useEffect( () => {
        if ( ! writingStyleAbility ) {
            return;
        }
        async function loadWritingStyle() {
            try {
                const result = await executeAbility( 'wp-ai-client-demo/get-writing-style');
                if ( result?.instructions ) {
                    setInput( ( current ) => ( {
                        ...current,
                        writingStyle: result.instructions,
                    } ) );
                }
            } catch ( err ) {
                console.error( 'Failed to load writing style:', err );
            }
        }
        loadWritingStyle();
    }, [ writingStyleAbility ] );

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
            const result = await executeAbility( 'wp-ai-client-demo/generate-post', {
                title: input.title,
                prompt: input.prompt,
            } );
            console.log(result);
        } catch ( err ) {
            updateNotice('Error during post generation. Check console for details.', 'error' );
            console.error( err );
        } finally {
            updateNotice('Post generation completed!.', 'success' );
        }
    }, [ input ] );

    const generateWritingStyle = useCallback( async () => {
        const writingStyleAbility = getAbility( 'wp-ai-client-demo/generate-writing-style' );
        if ( ! writingStyleAbility ) {
            updateNotice('Whoops, writing style generation Ability not found.', 'error' );
            return;
        }
        if ( ! input.context || input.context.length === 0 ) {
            updateNotice('Please select at least one post to analyze.', 'error' );
            return;
        }
        try {
            updateNotice('Generating writing style from selected posts, please hold for updates...', 'info' );
            const result = await executeAbility( 'wp-ai-client-demo/generate-writing-style', {
                post_ids: input.context,
            } );
            console.log(result);
            if ( result?.instructions ) {
                onChange( { writingStyle: result.instructions } );
            }
        } catch ( err ) {
            updateNotice('Error during writing style generation. Check console for details.', 'error' );
            console.error( err );
        } finally {
            updateNotice('Writing style generated and saved!', 'success' );
        }
    }, [ input ] );

    return (
        <VStack spacing={ 4 }>
            <SettingsTitle/>
            <Notice status={ noticeStatus }>
                { noticeMessage }
            </Notice>
            <TabPanel tabs={ tabs }>
                { ( tab ) => {
                    if ( tab.name === 'generate' ) {
                        return (
                            <VStack spacing={ 4 }>
                                <DataForm
                                    data={ input }
                                    fields={ fields }
                                    form={ generateForm }
                                    onChange={ onChange }
                                />
                                <GenerateButton onClick={ generateFromInput }/>
                            </VStack>
                        );
                    }
                    if ( tab.name === 'writing-style' ) {
                        return (
                            <VStack spacing={ 4 }>
                                <DataForm
                                    data={ input }
                                    fields={ fields }
                                    form={ writingStyleForm }
                                    onChange={ onChange }
                                />
                                <GenerateWritingStyleButton onClick={ generateWritingStyle }/>
                            </VStack>
                        );
                    }
                } }
            </TabPanel>
        </VStack>
    );
};

export { SettingsPage };
