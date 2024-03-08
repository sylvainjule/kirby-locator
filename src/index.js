import Locator from './field/Locator.vue';
import LocatorPreview from './components/LocatorPreview.vue';

import './assets/svg/icons.js';

panel.plugin('sylvainjule/locator', {
    fields: {
        locator: Locator,
    },
    components: {
        'k-locator-field-preview': LocatorPreview,
    },
    icons: {
        locatorMarker:
            '<circle cx="12" cy="10.48" r="3"/><path d="M10.95,23.51c-.15-.15-6.28-5.55-6.28-5.7-2.08-1.86-3.23-4.53-3.16-7.32C1.5,4.76,6.11,.08,11.85,0h.15c5.74,0,10.41,4.61,10.5,10.35v.15c.05,2.83-1.09,5.55-3.15,7.5l-6.3,5.7c-.65,.47-1.54,.4-2.1-.18Zm-4.05-7.5l5.1,4.5,5.08-4.5h0c1.51-1.39,2.38-3.33,2.41-5.38,0-4.08-3.26-7.41-7.33-7.5h-.16c-4.04-.1-7.4,3.09-7.5,7.13,0,.08,0,.15,0,.23,.03,2.09,.89,4.08,2.4,5.53h0Z"/>',
    },
});
