import domReady from '@wordpress/dom-ready';
import { createRoot } from '@wordpress/element';
import { SettingsPage } from './components/settings-page';

domReady( () => {
    const root = createRoot(
        document.getElementById( 'wp-ai-sdk-demo-app' )
    );

    root.render( <SettingsPage /> );
} );