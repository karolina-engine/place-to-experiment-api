<template>

</template>

<script>
import experiments from '../mixins/experiments.js'
import auth from '../mixins/auth.js'
import helpers from '../mixins/helpers.js'
export default {
    name: 'create-experiment',
    data() {
        return {
            responseStatus: {},
            experimentTitle: '',
            experimentShortDescription: '',
            experimentOwnerName: '',
            showErrors: false,
            experimentId: null
        }
    },
    props: {
        apiUrl: {
            required: true,
            type: String
        },
        language: {
            required: true,
            type: String
        },
        experimentStage: {
            required: true,
            type: String
        },
        experimentTag: { // Optional. If you specify it, the experiment is assigned this tag on creation
            required: false,
            type: String
        },
        languageKeyPrefix: { // Optional. If you specify it, the experiment text is saved as custom language (e.g. <prefix>title)
            required: false,
            type: String
        },
        redirectPath: { // Optional. If you specify it, the user is redirected after experiment is created
            required: false,
            type: String
        },
        // inherit global data from parent
        common: {
            required: true,
            type: Object
        }
    },
    mixins: [
        experiments,
        auth,
        helpers
    ],
    notifications: {
        createExperimentNotification: {},
        setTagsForExperimentNotification: {}
    },
    methods: {
        createExperiment: function(apiUrl, language) {
            this.$validator.validateAll().then(success => {
                if (success) {
                    // debug info
                    this.debug('create experiment form is valid')
                    var authHeader = this.getAuthorizationHeaderMixin()
                    var experimentBody = {
                        stage: this.experimentStage
                    }
                    var message = this.getErrorMessage(this, {
                        data: {
                            status: 'creating_experiment',
                            message: 'Creating experiment'
                        }
                    })
                    this.createExperimentNotification({
                        message: message,
                        timeout: 5000,
                        type: 'info'
                    })
                    this.createExperimentMixin(apiUrl, experimentBody, authHeader).then((response) => {
                        // debug info
                        this.debug('createExperimentMixin response: ')
                        this.debug(this.getResponseData(response))
                        // success callback
                        this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'createExperiment')
                        var experimentLanguageBody = {}
                        if (this.languageKeyPrefix) {
                            experimentLanguageBody.custom_language = {}
                            var titleKey = this.languageKeyPrefix + 'title'
                            var shortDescriptionKey = this.languageKeyPrefix + 'short_description'
                            experimentLanguageBody.custom_language = {
                                [titleKey]: {
                                    value: this.experimentTitle,
                                    format: 'plaintext'
                                },
                                [shortDescriptionKey]: {
                                    value: this.experimentShortDescription,
                                    format: 'plaintext'
                                }
                            }
                        } else {
                            experimentLanguageBody.language = {
                                title: {
                                    value: this.experimentTitle
                                },
                                short_description: {
                                    value: this.experimentShortDescription
                                },
                                owner_name: {
                                    value: this.experimentOwnerName
                                }
                            }
                        }
                        if (response.hasOwnProperty('data')) {
                            if (response.data.hasOwnProperty('experiment')) {
                                if (response.data.experiment.hasOwnProperty('experiment_id')) {
                                    this.experimentId = response.data.experiment.experiment_id
                                    // update created experiment
                                    if (this.experimentTag) {
                                        var experimentTagsBody = {
                                            tags: [this.experimentTag]
                                        }
                                        this.setTagsForExperiment(apiUrl, this.experimentId, experimentTagsBody, this.updateExperiment, experimentLanguageBody)
                                    } else {
                                        this.updateExperiment(experimentLanguageBody)
                                    }
                                }
                            }
                        }
                    }, (error) => {
                        // debug info
                        this.debug('createExperimentMixin error: ')
                        this.debug(this.getResponseMessage(error.response))
                        // error callback
                        this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'createExperiment')
                        // show error message
                        var message = this.getErrorMessage(this, error.response)
                        this.createExperimentNotification({
                            message: message,
                            timeout: 5000,
                            type: 'error'
                        })
                    })
                } else {
                    this.showErrors = true
                    // debug info
                    this.debug('create experiment form has errors')
                }
            })
        },
        updateExperiment: function(experimentLanguageBody) {
            if (this.languageKeyPrefix) {
                this.updateExperimentCustomLanguage(this.apiUrl, this.experimentId, this.language, experimentLanguageBody)
            } else {
                this.updateExperimentLanguage(this.apiUrl, this.experimentId, this.language, experimentLanguageBody)
            }
        },
        updateExperimentLanguage: function(apiUrl, experimentId, language, experimentLanguageBody) {
            // var infoToast = this.showNotification('Updating experiment', '', 'info', 0)
            var authHeader = this.getAuthorizationHeaderMixin()
            this.updateExperimentLanguageMixin(apiUrl, experimentId, language, experimentLanguageBody, authHeader).then((response) => {
                // debug info
                this.debug('updateExperimentLanguageMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'updateExperimentLanguage')
                // redirect to new page
                if (this.redirectPath !== '') {
                    this.openUrl(this.redirectPath + '/' + experimentId)
                }
            }, (error) => {
                // debug info
                this.debug('updateExperimentLanguageMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'updateExperimentLanguage')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.updateExperimentImageNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        updateExperimentCustomLanguage: function(apiUrl, experimentId, language, experimentLanguageBody) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.updateExperimentCustomLanguageMixin(apiUrl, experimentId, language, experimentLanguageBody, authHeader).then((response) => {
                // debug info
                this.debug('updateExperimentCustomLanguageMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'updateExperimentCustomLanguage')
                // redirect to new page
                if (this.redirectPath !== '') {
                    this.openUrl(this.redirectPath + '/' + experimentId)
                }
            }, (error) => {
                // debug info
                this.debug('updateExperimentCustomLanguageMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'updateExperimentCustomLanguage')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.updateExperimentImageNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        }
    },
    mounted: function() {
        // debug info
        this.showMountedMessage(this)
    }
}
</script>
