<template>

</template>

<script>
import auth from '../mixins/auth.js'
import helpers from '../mixins/helpers.js'
export default {
    name: 'navbar',
    data() {
        return {}
    },
    mixins: [
        auth,
        helpers
    ],
    methods: {
        setup: function() {
            this.getUserData()
        }
    },
    created: function() {
        this.$root.eventBus.$on('login', function() {
            this.setup()
        }.bind(this))
        this.$root.eventBus.$on('logout', function() {
            this.setup()
        }.bind(this))
        this.$root.eventBus.$on('profile', function() {
            this.setup()
        }.bind(this))
    },
    mounted: function() {
        // debug info
        this.showMountedMessage(this)
        // set this up
        this.setup()
    }
}
</script>
