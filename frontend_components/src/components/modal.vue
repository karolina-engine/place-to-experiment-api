<template>

</template>

<script>
import helpers from '../mixins/helpers.js'
import auth from '../mixins/auth.js'
export default {
    name: 'modal',
    data() {
        return {
            showModal: false,
            redirectPath: false
        }
    },
    props: {
        modalId: {
            required: true,
            type: String
        }
    },
    mixins: [
        helpers,
        auth
    ],
    methods: {
        setShowModal: function(show) {
            this.showModal = show
        },
        setRedirectPath: function(path) {
            this.redirectPath = path
        }
    },
    created: function() {
        this.$root.eventBus.$on('showModal', function(modalId, show, redirectPath = false) {
            if (modalId === this.modalId) {
                this.setShowModal(show)
                if (redirectPath) {
                    this.setRedirectPath(redirectPath)
                }
            }
        }.bind(this))
    },
    mounted: function() {
        // debug info
        this.showMountedMessage(this)
        // ESC key closes the modal
        document.addEventListener('keydown', (e) => {
            if (e.keyCode === 27) {
                if (this.showModal) {
                    this.setShowModal(false)
                }
            }
        })
    }
}
</script>
