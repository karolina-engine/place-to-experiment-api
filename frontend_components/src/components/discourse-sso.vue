<template>


</template>

<script>
import users from '../mixins/users.js'
import auth from '../mixins/auth.js'
import helpers from '../mixins/helpers.js'
export default {
    name: 'discourse-sso',
    data() {
        return {
        }
    },
    props: {
        apiUrl: {
            required: true,
            type: String
        },
        discourseUrl: {
            required: true,
            type: String
        },
        loginRedirect: {
            required: true,
            type: String
        }
    },
    mixins: [
        users,
        auth,
        helpers
    ],
    methods: {
        setup: function() {
            if (this.isUserAuthenticatedMixin()) {
                this.getSSOAuthQueryString().then((queryString) => {
                    this.redirectToDiscourse(queryString)
                })
            } else {
                this.openUrl(this.loginRedirect)
            }
        },
        getParameterByName: function(name, url) {
            if (!url) {
                url = window.location.href
            }
            name = name.replace(/[[\]]/g, '\\$&') // This line breaks Atom beautifier, to beautify, remove first.
            var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)')
            var results = regex.exec(url)
            if (!results) {
                return null
            }
            if (!results[2]) {
                return ''
            }
            return decodeURIComponent(results[2].replace(/\+/g, ' '))
        },
        redirectToDiscourse(queryString) {
            this.debug(this.discourseUrl + '/session/sso_login?' + queryString)
            window.location.href = this.discourseUrl + '/session/sso_login?' + queryString
        },
        getSSOAuthQueryString: function() {
            var data = {
                sso: this.getParameterByName('sso'),
                sig: this.getParameterByName('sig')
            }
            return this.$http.post(this.apiUrl + '/users/sso/discourse/', data, {
                headers: {
                    'Authorization': this.getAuthorizationHeaderMixin()
                }
            }).then((response) => {
                if (response.hasOwnProperty('data')) {
                    if (response.data.hasOwnProperty('sso_string')) {
                        return response.data.sso_string
                    }
                } else {
                    this.debug('sso_string format error')
                }
            }, (error) => {
                this.debug('error')
                this.debug(error.response)
            })
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
