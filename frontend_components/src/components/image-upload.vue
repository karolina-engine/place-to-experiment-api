<template>


</template>

<script>
import helpers from '../mixins/helpers.js'
export default {
    data() {
        return {
            showEditor: false,
            imageData: null
        }
    },
    props: {
        identifier: { // if provided, it is passed as a parameter of the 'set-image' event
            required: false,
            default: false
        }
    },
    mixins: [
        helpers
    ],
    notifications: {
        setImageNotification: {}
    },
    methods: {
        verifyImage: function(event) {
            this.$validator.validateAll().then(success => {
                if (success && event.target.files[0]) {
                    var fileBody = event.target.files[0]
                    // debug info
                    this.debug('image is valid')
                    this.imageData = new window.FormData()
                    this.imageData.append('file', fileBody)
                    // dispatch event so that the parent can react
                    if (this.identifier) {
                        this.$emit('set-image', this.imageData, this.identifier)
                    } else {
                        this.$emit('set-image', this.imageData)
                    }
                } else {
                    // debug info
                    this.debug('image has errors')
                    // reset input
                    event.target.value = null
                    this.imageData = null
                    // dispatch event so that the parent can react
                    if (this.identifier) {
                        this.$emit('set-image', this.imageData, this.identifier)
                    } else {
                        this.$emit('set-image', this.imageData)
                    }
                    // show error message
                    this.setImageNotification({
                        message: this.errors.first('image'),
                        timeout: 5000,
                        type: 'error'
                    })
                }
            })
        },
        uploadImage: function(imageName) {
            if (this.imageData) {
                this.showEditor = false
                this.imageData = null
                this.$emit('upload-image', imageName)
            } else {
                // debug info
                this.debug('no image selected')
            }
        },
        resetImage: function() {
            this.imageData = null
            this.showEditor = false
            this.$refs.imageFile.value = null
        }
    },
    mounted: function() {
        // debug info
        this.showMountedMessage(this)
    }
}

</script>
