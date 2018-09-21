<template>

</template>

<script>
import helpers from '../mixins/helpers.js'
export default {
    name: 'profile-editable',
    data() {
        return {
            value: null
        }
    },
    props: {
        sourceObject: {
            required: true,
            type: Object
        },
        objectKeyOrPath: {
            required: true,
            type: String
        },
        type: { /* Has to be "language" or "password" */
            required: true,
            type: String
        }
    },
    mixins: [
        helpers
    ],
    methods: {
        setup: function() {
            // set initial value
            this.setInitialValue()
        },
        setInitialValue: function() {
            if (this.objectKeyOrPath.indexOf('.') !== -1) {
                // it is a path
                var keys = this.objectKeyOrPath.split('.')
                if (this.checkNestedKey(this.sourceObject, keys)) {
                    this.value = this.cloneArrayOfJsonObjects(this.getNestedKey(this.sourceObject, keys))
                }
            } else {
                if (this.sourceObject.hasOwnProperty(this.objectKeyOrPath)) {
                    if (this.isObject(this.sourceObject[this.objectKeyOrPath])) {
                        this.value = this.cloneArrayOfJsonObjects(this.sourceObject[this.objectKeyOrPath])
                    } else {
                        this.value = this.sourceObject[this.objectKeyOrPath]
                    }
                }
            }
        },
        reset: function() {
            this.setInitialValue()
        },
        validate: function(resetOnSuccess = false) {
            if (this.isModified) {
                this.$validator.validateAll().then(success => {
                    if (success) {
                        // debug info
                        this.debug('profile editable form is valid')
                        if (this.objectKeyOrPath.indexOf('.') !== -1) {
                            // it is a path
                            var keys = this.objectKeyOrPath.split('.')
                            var postBody = {
                                [keys[keys.length - 1]]: this.value
                            }
                        } else {
                            postBody = {
                                [this.objectKeyOrPath]: this.value
                            }
                        }
                        // emit event so that parent can react
                        this.$emit('profile-editable-updated', postBody, this.type)
                        if (resetOnSuccess) {
                            this.reset()
                        }
                    } else {
                        // debug info
                        this.debug('profile editable form has errors')
                    }
                })
            } else {
                // debug info
                this.debug('profile editable not modified')
            }
        }
    },
    computed: {
        isModified: function() {
            if (this.value !== null) {
                if (this.objectKeyOrPath.indexOf('.') !== -1) {
                    // it is a path
                    var keys = this.objectKeyOrPath.split('.')
                    if (this.checkNestedKey(this.sourceObject, keys)) {
                        return JSON.stringify(this.value) !== JSON.stringify(this.getNestedKey(this.sourceObject, keys))
                    }
                } else {
                    if (this.sourceObject.hasOwnProperty(this.objectKeyOrPath)) {
                        if (this.isObject(this.sourceObject[this.objectKeyOrPath])) {
                            return JSON.stringify(this.value) !== JSON.stringify(this.sourceObject[this.objectKeyOrPath])
                        } else {
                            return this.value !== this.sourceObject[this.objectKeyOrPath]
                        }
                    } else {
                        return true
                    }
                }
            }
            return false
        }
    },
    watch: {
        sourceObject: function(val, oldVal) {
            this.setup()
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
