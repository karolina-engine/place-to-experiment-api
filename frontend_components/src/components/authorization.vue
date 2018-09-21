<template>

</template>

<script>
import auth from '../mixins/auth.js'
import helpers from '../mixins/helpers.js'
import users from '../mixins/users.js'
export default {
    name: 'authorization',
    data() {
        return {
            profile: this.common.placeholders.profile,
            responseStatus: {}
        }
    },
    props: {
        apiUrl: {
            required: true,
            type: String
        },
        // inherit global data from parent
        common: {
            required: true,
            type: Object
        }
    },
    mixins: [
        users,
        auth,
        helpers
    ],
    notifications: {
        loginNotification: {},
        profileNotification: {},
        registerNotification: {}
    },
    methods: {
        setup: function() {
            if (this.isUserAuthenticatedMixin()) {
                // fetch profile data
                this.getMyProfile(this.apiUrl)
            } else {
                // reset data
                this.profile = this.common.placeholders.profile
            }
        },
        login: function(apiUrl, credentials, redirect = false) {
            this.getUserTokenMixin(apiUrl, credentials).then((response) => {
                // debug info
                this.debug('getUserTokenMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'getUserToken')
                if (response.hasOwnProperty('data')) {
                    if (response.data.hasOwnProperty('token')) {
                        if (response.data.token.hasOwnProperty('string')) {
                            this.processLogin(apiUrl, response.data.token.string, redirect)
                        }
                    }
                }
                // show success message
                var message = this.getErrorMessage(this, {
                    data: {
                        status: 'login_successful',
                        message: 'Login successful.'
                    }
                })
                this.loginNotification({
                    message: message,
                    timeout: 5000,
                    type: 'success'
                })
            }, (error) => {
                // debug info
                this.debug('getUserTokenMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'getUserToken')
                // handle unsucessfull calls - ensure client side data is correct
                this.$root.eventBus.$emit('sign-in-failed')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.loginNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        processLogin: function(apiUrl, data, redirect = false) {
            var tokenObject = {
                'string': data.token.string,
                'timestamp': this.getTimestamp()
            }
            this.setLocalStorageObjectMixin('token', tokenObject)
            this.$root.eventBus.$emit('login')
            if (redirect) {
                // debug info
                this.debug('processLogin redirect: ' + redirect)
                this.openUrl(redirect)
            }
        },
        getMyProfile: function(apiUrl) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.getMyProfileMixin(apiUrl, authHeader).then((response) => {
                // debug info
                this.debug('getMyProfileMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'getMyProfile')
                if (response.hasOwnProperty('data')) {
                    if (response.data.hasOwnProperty('profile')) {
                        this.profile = response.data.profile
                    }
                    if (response.data.hasOwnProperty('acl')) {
                        this.acl = response.data.acl
                    }
                }
                var userDataObject = {
                    'user_id': this.profile.user_id,
                    'first_name': this.profile.first_name,
                    'last_name': this.profile.last_name,
                    'email': this.profile.email,
                    'phone': this.profile.phone,
                    'document_number': this.profile.document_number
                }
                let userAclObject = this.acl
                this.setLocalStorageObjectMixin('userData', userDataObject)
                this.setLocalStorageObjectMixin('userAcl', userAclObject)
                // emit event so that other components can react to login
                this.$root.eventBus.$emit('profile')
            }, (error) => {
                // debug info
                this.debug('getMyProfileMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'getMyProfile')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.profileNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        register: function(apiUrl, credentials, redirect) {
            this.registerUserMixin(apiUrl, credentials).then((response) => {
                // debug info
                this.debug('registerUserMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'registerUser')
                if (response.hasOwnProperty('data')) {
                    if (response.data.hasOwnProperty('token')) {
                        if (response.data.token.hasOwnProperty('string')) {
                            this.processLogin(apiUrl, response.data.token.string, redirect)
                        }
                    }
                }
                // show success message
                var message = this.getErrorMessage(this, {
                    data: {
                        status: 'registration_successful',
                        message: 'Registration successful.'
                    }
                })
                this.registerNotification({
                    message: message,
                    timeout: 5000,
                    type: 'success'
                })
            }, (error) => {
                // debug info
                this.debug('registerUserMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'registerUser')
                this.$root.eventBus.$emit('sign-up-failed')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.registerNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        }
    },
    created: function() {
        this.$root.eventBus.$on('doLogin', function(credentials, redirect) {
            this.login(this.apiUrl, credentials, redirect)
        }.bind(this))
        this.$root.eventBus.$on('doLogout', function(redirect) {
            this.logoutMixin(redirect)
            var message = this.getErrorMessage(this, {
                data: {
                    status: 'logout_successful',
                    message: 'Logout successful.'
                }
            })
            this.registerNotification({
                message: message,
                timeout: 5000,
                type: 'success'
            })
        }.bind(this))
        this.$root.eventBus.$on('login', function() {
            this.setup()
        }.bind(this))
        this.$root.eventBus.$on('logout', function() {
            this.setup()
        }.bind(this))
        this.$root.eventBus.$on('doRegister', function(credentials, redirect) {
            this.register(this.apiUrl, credentials, redirect)
        }.bind(this))
        this.$root.eventBus.$on('doProfile', function() {
            this.getMyProfile(this.apiUrl)
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
