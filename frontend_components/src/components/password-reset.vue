<template>

</template>

<script>
import users from '../mixins/users.js'
import auth from '../mixins/auth.js'
import helpers from '../mixins/helpers.js'
export default {
    name: 'password-reset',
    data() {
        return {
            email: '',
            newPassword: '',
            retypePassword: '',
            showErrors: false,
            responseStatus: {}
        }
    },
    props: {
        apiUrl: {
            required: true,
            type: String
        },
        resetToken: {
            required: false,
            default: false
        },
        redirectUrl: { /* TODO: implement */
            required: false,
            default: false
        }
    },
    mixins: [
        users,
        auth,
        helpers
    ],
    notifications: {
        resetPasswordNotification: {},
        changePasswordNotification: {}
    },
    methods: {
        setup: function() {
            if (this.resetToken) {
                this.debug('got reset token: ' + this.resetToken)
            } else {
                this.debug('reset token not present')
            }
        },
        validateForm(apiUrl) {
            if (!this.resetToken) {
                this.sendResetEmail(apiUrl)
            } else {
                this.setNewPassword(apiUrl)
            }
        },
        sendResetEmail: function(apiUrl) {
            this.$validator.validateAll().then(success => {
                if (success) {
                    // debug info
                    this.debug('reset email form is valid')
                    var resetPasswordBody = {
                        email: this.email
                    }
                    this.resetPassword(apiUrl, resetPasswordBody)
                } else {
                    this.showErrors = true
                    // debug info
                    this.debug('profile form has errors')
                }
            })
        },
        setNewPassword: function(apiUrl) {
            this.$validator.validateAll().then(success => {
                if (success) {
                    // debug info
                    this.debug('change password form is valid')
                    var changePasswordBody = {
                        email: this.email,
                        password_reset_token: this.resetToken,
                        new_password: this.newPassword
                    }
                    this.changePassword(apiUrl, changePasswordBody)
                } else {
                    this.showErrors = true
                    // debug info
                    this.debug('change password form has errors')
                }
            })
        },
        resetPassword: function(apiUrl, resetPasswordBody) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.resetPasswordMixin(apiUrl, resetPasswordBody, authHeader).then((response) => {
                // debug info
                this.debug('resetPasswordMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'resetPassword')
            }, (error) => {
                // debug info
                this.debug('resetPasswordMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'resetPassword')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.resetPasswordNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        changePassword: function(apiUrl, changePasswordBody) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.changePasswordMixin(apiUrl, changePasswordBody, authHeader).then((response) => {
                // debug info
                this.debug('changePasswordMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'changePassword')
            }, (error) => {
                // debug info
                this.debug('changePasswordMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'changePassword')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.changePasswordNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        }
    },
    computed: {
        isEmailModified: function() {
            if (this.email !== '') {
                return true
            } else {
                return false
            }
        },
        isChangePasswordFormModified: function() {
            if (this.email !== '') {
                return true
            } else if (this.newPassword !== '') {
                return true
            } else {
                return false
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
