export default {
    methods: {
        createExperimentMixin: function (apiUrl, experimentBody, authHeader) {
            return this.$http.post(apiUrl + '/experiments/', experimentBody, {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        getExperimentMixin: function (apiUrl, experimentId, language, authHeader) {
            if (authHeader) {
                return this.$http.get(apiUrl + '/experiments/' + experimentId + '/' + language, {
                    headers: {
                        'Authorization': authHeader
                    }
                })
            } else {
                return this.$http.get(apiUrl + '/experiments/' + experimentId + '/' + language)
            }
        },
        getExperimentsPreviewMixin: function (apiUrl, language, query = false, authHeader) {
            var queryString = ''
            if (query.length > 0) {
                queryString = '?'
                for (var i = 0; i < query.length; i++) {
                    for (var key in query[i]) {
                        queryString += key
                        queryString += '='
                        queryString += query[i][key]
                    }
                    if (i + 1 !== query.length) {
                        queryString += '&'
                    }
                }
            }
            if (authHeader) {
                return this.$http.get(apiUrl + '/experiments/preview/' + language + queryString, {
                    headers: {
                        'Authorization': authHeader
                    }
                })
            } else {
                return this.$http.get(apiUrl + '/experiments/preview/' + language + queryString)
            }
        },
        updateExperimentSettingsMixin: function (apiUrl, experimentId, experimentBody, authHeader) {
            return this.$http.patch(apiUrl + '/experiments/' + experimentId + '/settings/', experimentBody, {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        updateExperimentLanguageMixin: function (apiUrl, experimentId, language, experimentLanguageBody, authHeader) {
            return this.$http.patch(apiUrl + '/experiments/' + experimentId + '/language/' + language + '/', experimentLanguageBody, {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        updateExperimentCustomLanguageMixin: function (apiUrl, experimentId, language, experimentLanguageBody, authHeader) {
            return this.$http.patch(apiUrl + '/experiments/' + experimentId + '/custom_language/' + language + '/', experimentLanguageBody, {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        updateExperimentImageCollectionMixin: function (apiUrl, experimentId, experimentImageCollectionBody, authHeader) {
            return this.$http.patch(apiUrl + '/experiments/' + experimentId + '/image_collection/', experimentImageCollectionBody, {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        getTagsForExperimentsMixin: function (apiUrl, language) {
            return this.$http.get(apiUrl + '/experiments/tags/' + language)
        },
        setTagsForExperimentMixin: function (apiUrl, experimentId, experimentTagsBody, authHeader) {
            return this.$http.post(apiUrl + '/experiments/' + experimentId + '/tags/', experimentTagsBody, {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        updateExperimentFundingMixin: function (apiUrl, experimentId, experimentFundingBody, authHeader) {
            return this.$http.patch(apiUrl + '/experiments/' + experimentId + '/funding/', experimentFundingBody, {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        addExperimentLikeMixin: function (apiUrl, experimentId, authHeader) {
            return this.$http.post(apiUrl + '/experiments/' + experimentId + '/likes/', {}, {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        removeExperimentLikeMixin: function (apiUrl, experimentId, authHeader) {
            return this.$http.delete(apiUrl + '/experiments/' + experimentId + '/likes/', {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        setExperimentLinksMixin: function (apiUrl, experimentId, experimentLinksBody, authHeader) {
            return this.$http.post(apiUrl + '/experiments/' + experimentId + '/links/', experimentLinksBody, {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        moveExperimentToNextStageMixin: function (apiUrl, experimentId, authHeader) {
            return this.$http.post(apiUrl + '/experiments/' + experimentId + '/stagemoves/', {}, {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        getExperimentPicUrl: function (placeholderImage, experimentObject, imageKey, h, w) {
            if (experimentObject.image_collection) {
                if (experimentObject.image_collection[imageKey]) {
                    var picUrl = 'https://d1ncrxda1lmimh.cloudfront.net/karolina-fund/image/fetch/c_thumb,h_' + h + ',w_' + w + '/' + experimentObject.image_collection[imageKey].url
                    return picUrl
                } else {
                    return placeholderImage
                }
            } else {
                return placeholderImage
            }
        },
        getExperimentPreviewPicUrl: function (placeholderImage, imageUrl, h, w) {
            if (imageUrl) {
                var picUrl = 'https://d1ncrxda1lmimh.cloudfront.net/karolina-fund/image/fetch/c_thumb,h_' + h + ',w_' + w + '/' + imageUrl
                return picUrl
            } else {
                return placeholderImage
            }
        },
        getTeamPicUrl: function (placeholderImage, image, h, w) {
            if (image) {
                var picUrl = 'https://d1ncrxda1lmimh.cloudfront.net/karolina-fund/image/fetch/c_thumb,h_' + h + ',w_' + w + '/' + image
                return picUrl
            } else {
                return placeholderImage
            }
        },
        setTagsForExperiment: function (apiUrl, experimentId, experimentTagsBody, callback = false, callbackArg = false) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.setTagsForExperimentMixin(apiUrl, experimentId, experimentTagsBody, authHeader)
                .then((response) => {
                    // debug info
                    this.debug('setTagsForExperimentMixin response: ')
                    this.debug(this.getResponseData(response))
                    // success callback
                    this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'setTagsForExperiment')
                    // Make sure the callback is a function
                    if (typeof callback === 'function') {
                        if (callbackArg) {
                            callback(callbackArg)
                        } else {
                            callback()
                        }
                    }
                }, (error) => {
                    // debug info
                    this.debug('setTagsForExperimentMixin error: ')
                    this.debug(this.getResponseMessage(error.response))
                    // error callback
                    this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'setTagsForExperiment')

                    // show error message

                    var message = this.getErrorMessage(this, error.response)
                    this.setTagsForExperimentNotification({
                        message: message,
                        timeout: 5000,
                        type: 'error'
                    })
                    // Make sure the callback is a function
                    if (typeof callback === 'function') {
                        callback()
                    }
                })
        }
    }
}
