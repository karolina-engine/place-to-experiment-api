export default {
    data () {
        return {
            attributes: null
        }
    },
    methods: {
        debug: function (message) {
            console.log(message)
        },
        showMountedMessage: function (componentInstance, appendtext = '') {
            this.debug(componentInstance.$options.name + ' mounted ' + appendtext)
        },
        reload: function () {
            window.location.reload()
        },
        openUrl: function (url, newWindow = false) {
            if (!newWindow) {
                window.location.href = url
            } else {
                window.open(url, '_blank')
            }
        },
        scrollToTop: function () {
            window.scrollTo(0, 0)
        },
        getErrorMessage: function (componentInstance, response, fallbackToGlobal = true) {
            if (response) {
                if (response.hasOwnProperty('data')) {
                    if (response.data.hasOwnProperty('status')) {
                        var status = response.data.status
                    } else {
                        status = ''
                    }
                }
                for (var i = 0; i < componentInstance.$children.length; i++) {
                    if (componentInstance.$children[i].$options.name === 'notification-message') {
                        if (componentInstance.$children[i].status === status) {
                            return componentInstance.$children[i].message
                        }
                    }
                }
                if (fallbackToGlobal) {
                    for (var j = 0; j < componentInstance.$root.$children.length; j++) {
                        if (componentInstance.$root.$children[j].$options.name === 'notification-message') {
                            if (componentInstance.$root.$children[j].status === status) {
                                return componentInstance.$root.$children[j].message
                            }
                        }
                    }
                }
                if (response.hasOwnProperty('data')) {
                    if (response.data.hasOwnProperty('message')) {
                        return response.data.message + ' (' + componentInstance.$options.name + ':' + response.data.status + ')'
                    }
                }
            }
            return ''
        },
        getResponseStatus: function (responseStatus, requestName) {
            if (responseStatus.hasOwnProperty(requestName)) {
                return responseStatus[requestName]
            } else {
                return false
            }
        },
        setResponseStatus: function (responseStatus, response, requestName) {
            if (response) {
                if (response.hasOwnProperty('data')) {
                    this.debug('setting response status: ' + requestName + ' = ' + response.data.status)
                    this.$set(responseStatus, requestName, response.data.status)
                }
                return responseStatus
            }
            return false
        },
        getResponseMessage: function (response) {
            var message = false
            if (response) {
                if (response.hasOwnProperty('data')) {
                    if (response.data.hasOwnProperty('message')) {
                        message = response.data.message
                    }
                }
            }
            if (message) {
                return message
            } else {
                return 'response not JSON format or message key missing.'
            }
        },
        getResponseData: function (response) {
            var data = false
            if (response.hasOwnProperty('data')) {
                data = response.data
            }
            if (data) {
                return data
            } else {
                return 'response not JSON format.'
            }
        },
        setValidatorLocale: function (validator, language) {
            validator.setLocale(language)
        },
        currentPath: function () {
            return window.location.pathname
        },
        currentUrl: function () {
            return window.location.href
        },
        isEqual: function (first, second) {
            // this.debug(first)
            // this.debug(second)
            return first === second
        },
        getEmbedCode: function (url, width, height) {
            var embedCode = '<iframe width="' + width + '" height="' + height + '" src="' + url + '" frameborder="0" allowfullscreen></iframe>'
            return embedCode
        },
        getAttributeValue: function (key) {
            if (this.attributes && this.attributes.hasOwnProperty(key)) {
                return this.attributes[key]
            } else {
                this.debug('attribute key ' + key + ' not found')
            }
        },
        setAttributeValue: function(key, value, propagate = false) {
            if (this.attributes && this.attributes.hasOwnProperty(key)) {
                this.attributes[key] = value
                if (propagate) {
                    this.$root.eventBus.$emit('set-attribute-value', key, value)
                }
            } else {
                this.debug('attribute key ' + key + ' not found')
            }
        },
        setAttributes: function() {
            this.attributes = this.$attrs
        },
        isObject: function (obj) {
            return obj === Object(obj)
        },
        cloneArrayOfJsonObjects: function(array) {
            return JSON.parse(JSON.stringify(array))
        },
        cookieGet: function(name) {
            var c = '; ' + document.cookie
            var x = c.split('; ' + name + '=')
            if (x.length === 2) {
                return x.pop().split(';').shift()
            }
        },
        cookieSet: function(name, value) {
            var expires = ''
            var date = new Date()
            date.setTime(date.getTime() + (365 * 24 * 60 * 60 * 1000))
            expires = '; expires=' + date.toUTCString()
            document.cookie = name + '=' + value + expires + '; path=/'
        }
    },
    computed: {
        rootUrl: function () {
            var url = window.location.protocol + '//' + window.location.hostname + '/'
            return url
        }
    },
    created: function() {
        this.$root.eventBus.$on('set-attribute-value', function(key, value) {
            // this.debug('event received in: ' + this.$options.name)
            this.setAttributeValue(key, value)
        }.bind(this))
    },
    mounted: function() {
        // set up non-defined props
        this.setAttributes()
    }
}
