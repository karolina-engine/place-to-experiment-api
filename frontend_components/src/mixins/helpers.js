export default {
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
        }
    },
    computed: {
        rootUrl: function () {
            var url = window.location.protocol + '//' + window.location.hostname + '/'
            return url
        }
    }
}
