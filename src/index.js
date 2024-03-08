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
});
