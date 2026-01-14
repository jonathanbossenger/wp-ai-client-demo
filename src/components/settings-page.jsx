import { __ } from '@wordpress/i18n';
import {
    // eslint-disable-next-line @wordpress/no-unsafe-wp-apis
    __experimentalHeading as Heading,
    // eslint-disable-next-line @wordpress/no-unsafe-wp-apis
    __experimentalVStack as VStack,
    Button,
} from '@wordpress/components';
import { DataForm } from '@wordpress/dataviews/wp';
import { getAbility } from '@wordpress/abilities';

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

    const [ input, setInput, saveInput ] = useSettings();

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


    const generatePost = () => {
        const generatePostAbility = getAbility( 'wp-ai-sdk-demo/generate-post' );

    }

    return (
        <VStack spacing={ 4 }>
            <SettingsTitle/>
            <DataForm
                data={data}
                fields={fields}
                form={form}
                onChange={() => {
                }}
            />
            <GenerateButton onClick={generatePost}/>
        </VStack>
    );
};

export { SettingsPage };