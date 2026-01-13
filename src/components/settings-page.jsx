import { __ } from '@wordpress/i18n';
import {
    // eslint-disable-next-line @wordpress/no-unsafe-wp-apis
    __experimentalHeading as Heading,
    // eslint-disable-next-line @wordpress/no-unsafe-wp-apis
    __experimentalVStack as VStack,
    Button,
} from '@wordpress/components';
import { DataForm } from '@wordpress/dataviews/wp';

const SettingsTitle = () => {
    return (
        <Heading level={ 1 }>
            { __( 'WP AI SDK Demo', 'wp-ai-sdk-demo' ) }
        </Heading>
    );
};

const SaveButton = ( { onClick } ) => {
    return (
        <div>
            <Button variant="primary" onClick={ onClick } __next40pxDefaultSize>
                { __( 'Save', 'wp-ai-sdk-demo' ) }
            </Button>
        </div>
    );
};

const SettingsPage = () => {

    const data = {};
    const fields = [];
    const form = {};

    const saveSettings = () => {
        // Implement save functionality here
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
            <SaveButton onClick={saveSettings}/>
        </VStack>
    );
};

export { SettingsPage };