<template>

</template>

<script>
import helpers from '../mixins/helpers.js'
export default {
    name: 'alert',
    data() {
        return {
        }
    },
    props: {
        displayText: {
            required: true,
            type: String
        }
    },
    mixins: [
        helpers
    ],
    methods: {
        setup: function() {

        },
        showAlert: function() {
            var confirm = window.confirm(this.displayText)
            if (confirm) {
                this.$emit('confirm')
            } else {
                this.$emit('cancel')
                // debug info
                this.debug('user cancelled')
            }
        }
    },
    mounted: function() {
        // debug info
        this.showMountedMessage(this)
        // set this up
        this.setup()
    }
}
</script>
