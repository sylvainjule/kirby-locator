import Locator        from './field/Locator.vue'
import LocatorPreview from './components/LocatorPreview.vue'

import './assets/svg/icons.js'

panel.plugin('sylvainjule/locator', {
    fields: {
        locator: Locator,
    },
    components: {
        'k-locator-field-preview': LocatorPreview,
    },
    icons: {
        locatorMarker: '<g><circle cx="8" cy="6.99" r="2"/><path d="M7.3,15.68c-.1-.1-4.19-3.7-4.19-3.8A6.34,6.34,0,0,1,1,7,7,7,0,0,1,7.9,0H8a7,7,0,0,1,7,6.9V7a6.72,6.72,0,0,1-2.1,5l-4.2,3.8A1.07,1.07,0,0,1,7.3,15.68Zm-2.7-5,3.4,3,3.39-3h0A5,5,0,0,0,13,7.09a5,5,0,0,0-4.89-5H8A4.88,4.88,0,0,0,3,7a5.22,5.22,0,0,0,1.6,3.69Z"/></g>'
    }
});
