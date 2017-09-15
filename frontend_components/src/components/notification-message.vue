<template>
<span v-show="false">
    <slot></slot>
  </span>

</template>

<script>
import helpers from '../mixins/helpers.js'
export default {
    name: 'notification-message',
    data() {
        return {}
    },
    props: {
        status: {
            required: true,
            type: String
        }
    },
    mixins: [
        helpers
    ],
    computed: {
        message: function() {
            if (this.$slots.default) {
                return this.$slots.default[0].text
            } else {
                this.debug('notification-message <slot> tag is missing.')
                return ''
            }
        }
    },
    mounted: function() {
        // debug info
        this.showMountedMessage(this, '(' + this.status + ')')
    }
}

</script>
