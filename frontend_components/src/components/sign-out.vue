<template>

</template>

<script>
import helpers from '../mixins/helpers.js'
import auth from '../mixins/auth.js'
export default {
    name: 'sign-out',
    data() {
        return {}
    },
    props: {
        redirectPath: { /* Optional. Path to redirect to if sign out successful */
            required: false,
            default: false
        }
    },
    mixins: [
        auth,
        helpers
    ],
    methods: {
        validateForm: function() {
            // dispatch event so that authorization can process the logout
            this.$root.eventBus.$emit('doLogout', this.redirectPath)
            // close the logout modal
            this.$emit('close')
        }
    },
    mounted: function() {
        // debug info
        this.showMountedMessage(this)
    }
}
</script>
