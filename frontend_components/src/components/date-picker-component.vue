<template>
    <flat-pickr :value="value" :config="config" v-on:input="onChange"></flat-pickr>
</template>

<script>
import helpers from '../mixins/helpers.js'
import date from '../mixins/date.js'
import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
export default {
    name: 'datepicker',
    data() {
        return {
            date: null,
            config: {
                dateFormat: 'U',
                altInput: true,
                altFormat: this.displayFormat,
                static: true,
                disableMobile: true
            }
        }
    },
    props: {
        value: {
            required: true
            // type: String // can also be null, so either use a custom validator or do not enforce type
        },
        displayFormat: {
            required: true,
            type: String
        }
    },
    mixins: [
        helpers,
        date
    ],
    components: {
        flatPickr
    },
    methods: {
        onChange: function (event) {
            this.$emit('input', event)
        }
    },
    computed: {
    },
    mounted: function() {
        // debug info
        this.showMountedMessage(this)
    }
}
</script>
