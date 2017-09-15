<template>

</template>

<script>
import helpers from '../mixins/helpers.js'
import auth from '../mixins/auth.js'
export default {
    name: 'sign-in',
    data() {
        return {
            showErrors: false
        }
    },
    props: {
        redirectPath: { /* Optional. Path to redirect to if sign in successful */
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
            this.$validator.validateAll().then(success => {
                if (success) {
                    // debug info
                    this.debug('sign in form is valid')
                    // dispatch event so that authorization can process the login
                    this.$root.eventBus.$emit('doLogin', this.credentials, this.redirectPath)
                    // close the sign in modal
                    this.$emit('close')
                } else {
                    this.showErrors = true
                    // debug info
                    this.debug('sign in form has errors')
                }
            })
        }
    },
    mounted: function() {
        // debug info
        this.showMountedMessage(this)
    }
}
</script>
