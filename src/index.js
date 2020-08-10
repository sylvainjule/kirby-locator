import Locator from './field/Locator.vue'
import './assets/svg/icons.js'

panel.plugin('sylvainjule/locator', {
    fields: {
        locator: Locator,
    },
    components: {
        'k-locator-field-preview': {
            props: {
                value: Object,
                column: Object,
                field: Object
            },

            computed: {
                fieldPreview() {
                    let vm = this;
                    let fields = vm.column.fields;
                    let output = [];

                    if (fields === undefined) {
                        fields = ['city', 'country'];
                    }

                    fields.forEach(function(field) {
                        if (vm.value[field] !== undefined) {
                            output.push(vm.value[field]);
                        }
                    });

                    return output.join(', ');
                }
            },

            template: '<p class="k-structure-table-text">{{fieldPreview}}</p>'
        }
    }
});
