import config from '../config.js'
export default {
    data() {
        return {
            isUserAuthenticated: false,
            isUserAdmin: false,
            isUserEditor: false,
            acl: [],
            userFirstName: '',
            userLastName: '',
            userEmail: '',
            credentials: {
                email: '',
                password: '',
                first_name: '',
                last_name: ''
            },
            retype_password: ''
        }
    },
    methods: {
        logoutMixin: function (redirect = false) {
            window.localStorage.removeItem('token')
            window.localStorage.removeItem('userData')
            if (redirect) {
                window.location.href = redirect
            }
            this.$root.eventBus.$emit('logout')
        },
        getAuthorizationHeaderMixin: function () {
            if (this.isUserAuthenticatedMixin()) {
                try {
                    var tokenObject = this.getLocalStorageObjectMixin('token')
                    return 'Bearer ' + tokenObject.string
                } catch (e) {
                    this.debug('Exception getting token: ' + e)
                    return ''
                }
            } else {
                return ''
            }
        },
        setUserAcl: function () {
            this.isUserEditorMixin()
            this.isUserAdminMixin()
        },
        isUserAuthenticatedMixin: function () {
            var authenticated = false
            if (this.phpLogin) {
                // debug info
                this.debug('checking PHP login')
                for (var i = 0; i < this.$root.$children.length; i++) {
                    // debug info
                    // this.debug('children name: ' + this.$root.$children[i].$options.name)
                    if (this.$root.$children[i].$options.name === 'php-login') {
                        authenticated = this.$root.$children[i].loggedInBoolean
                    }
                }
            } else {
                if (this.getLocalStorageObjectMixin('token')) {
                    var tokenObject = this.getLocalStorageObjectMixin('token')
                    if (this.getTimeDifference(tokenObject.timestamp) < config.tokenTimeout) {
                        // debug info
                        // this.debug('this.getTimeDifference(tokenObject.timestamp): ' + this.getTimeDifference(tokenObject.timestamp))
                        authenticated = true
                    } else {
                        // debug info
                        this.debug('token expired')
                        // remove the expired token
                        this.logoutMixin(false)
                    }
                }
            }
            this.isUserAuthenticated = authenticated
            return authenticated
        },
        isUserEditorMixin: function () {
            var editor = false
            if (this.isUserAuthenticatedMixin()) {
                for (var i = 0; i < this.acl.length; i++) {
                    if (this.acl[i] === 'edit') {
                        editor = true
                    }
                }
            }
            this.isUserEditor = editor
            return editor
        },
        isUserAdminMixin: function () {
            var admin = false
            if (this.isUserAuthenticatedMixin()) {
                for (var i = 0; i < this.acl.length; i++) {
                    if (this.acl[i] === 'admin') {
                        admin = true
                    }
                }
            }
            this.isUserAdmin = admin
            return admin
        },
        setLocalStorageObjectMixin: function (key, value) {
            window.localStorage.setItem(key, JSON.stringify(value))
        },
        getLocalStorageObjectMixin: function (key) {
            var value = window.localStorage.getItem(key)
            return value && JSON.parse(value)
        },
        getTimeDifference: function (timestamp) {
            var now = this.getTimestamp()
            return (now - timestamp)
        },
        getTimestamp: function () {
            return Date.now()
        },
        getUserData: function () {
            if (this.isUserAuthenticatedMixin()) {
                var userObject = this.getLocalStorageObjectMixin('userData')
                if (userObject) {
                    if (userObject.hasOwnProperty('first_name')) {
                        this.userFirstName = userObject.first_name
                    }
                    if (userObject.hasOwnProperty('last_name')) {
                        this.userLastName = userObject.last_name
                    }
                    if (userObject.hasOwnProperty('email')) {
                        this.userEmail = userObject.email
                    }
                }
            } else {
                // reset values
                this.userFirstName = ''
                this.userLastName = ''
                this.userEmail = ''
            }
        },
        userName: function () {
            return this.userFirstName + ' ' + this.userLastName
        }
    },
    created: function () {
        this.$root.eventBus.$on('login', function () {
            this.setUserAcl()
        }.bind(this))
        this.$root.eventBus.$on('logout', function () {
            this.setUserAcl()
        }.bind(this))
    },
    mounted: function () {
        // check if user is logged in
        this.setUserAcl()
    }
}
