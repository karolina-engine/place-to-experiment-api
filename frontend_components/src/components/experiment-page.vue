<template>

</template>

<script>
import experiments from '../mixins/experiments.js'
import files from '../mixins/files.js'
import date from '../mixins/date.js'
import auth from '../mixins/auth.js'
import helpers from '../mixins/helpers.js'
export default {
    name: 'experiment-page',
    data() {
        return {
            responseStatus: {},
            experiment: this.common.placeholders.experiment,
            myRelationships: [],
            experimentLinks: [],
            showSpindle: false,
            imageLoaded: false,
            selectedExperimentStage: null,
            experimentLocation: '',
            displayedExperimentStage: null,
            imageBody: null,
            uploadImageNotification: null,
            experimentTitle: '',
            experimentShortDescription: '',
            tagsForExperiments: [],
            editExperimentStage: false,
            editExperimentShowIn: false,
            editExperimentDisabled: false,
            editExperimentFunding: false,
            editExperimentLinks: false,
            editExperimentLocation: false,
            selectedExperimentDisabled: null,
            selectedExperimentShowIn: {},
            experimentDisabledAgree: false,
            experimentFundingCrowdRaised: 0,
            experimentFundingCrowdStage: null,
            experimentFunding: {
                goal: null,
                currency: null,
                sources: {
                    crowd: {
                        api: null,
                        campaign_id: null
                    },
                    state: {
                        raised: null
                    },
                    organizations: {
                        raised: null
                    }
                }
            },
            showFundingErrors: false
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
        experimentId: {
            required: true,
            type: String
        },
        placeholderImageUrl: {
            required: false,
            default: false
        },
        cachedData: {
            required: false,
            default: false
        },
        experimentLocations: {
            required: false,
            type: Array
        },
        redirectUrlNotFound: {
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
        files,
        date,
        auth,
        helpers
    ],
    notifications: {
        getExperimentNotification: {},
        updateExperimentNotification: {},
        updateExperimentSettingsNotification: {},
        updateExperimentImageNotification: {},
        getTagsForExperimentsNotification: {},
        updateExperimentFundingNotification: {},
        goToTheNextStageNotification: {},
        setExperimentLinksNotification: {},
        setTagsForExperimentNotification: {},
        publishExperimentNotification: {},
        unpublishExperimentNotification: {}
    },
    methods: {
        setup: function() {
            // load cached data if any
            if (this.cachedData) {
                try {
                    var cachedJSON = JSON.parse(this.cachedData)
                    if (cachedJSON.status) {
                        if (cachedJSON.status === 'not_found') {
                            // debug info
                            this.debug('error in cached data:')
                            if (cachedJSON.message) {
                                this.debug(cachedJSON.message)
                                // show 404
                                this.experiment = null
                            }
                        } else {
                            this.setInitialValues(cachedJSON)
                        }
                    }
                } catch (e) {
                    // debug info
                    this.debug('cachedData not JSON')
                    this.debug('error: ' + e)
                }
            }
            // make the API call
            this.getExperiment(this.apiUrl, this.experimentId, this.language)
        },
        setInitialValues: function(remoteData) {
            var remoteExperimentData = remoteData.experiment
            this.experiment = remoteExperimentData
            this.selectedExperimentStage = parseInt(remoteExperimentData.stage)
            this.displayedExperimentStage = parseInt(remoteExperimentData.stage)
            if (!remoteExperimentData.show_in) {
                this.$set(remoteExperimentData, 'show_in', {})
            }
            this.selectedExperimentShowIn = Object.assign({}, remoteExperimentData.show_in)
            this.selectedExperimentDisabled = remoteExperimentData.disabled
            if (remoteExperimentData.funding) {
                if (remoteExperimentData.funding.goal) {
                    this.experimentFunding.goal = remoteExperimentData.funding.goal
                }
                if (remoteExperimentData.funding.currency) {
                    this.experimentFunding.currency = remoteExperimentData.funding.currency
                }
                if (remoteExperimentData.funding.sources) {
                    if (remoteExperimentData.funding.sources.crowd) {
                        if (remoteExperimentData.funding.sources.crowd.campaign_id) {
                            this.experimentFunding.sources.crowd.campaign_id = remoteExperimentData.funding.sources.crowd.campaign_id
                        }
                        if (remoteExperimentData.funding.sources.crowd.api) {
                            this.experimentFunding.sources.crowd.api = remoteExperimentData.funding.sources.crowd.api
                        }
                    }
                    if (remoteExperimentData.funding.sources.organizations) {
                        if (remoteExperimentData.funding.sources.organizations.raised !== undefined) {
                            this.experimentFunding.sources.organizations.raised = remoteExperimentData.funding.sources.organizations.raised
                        }
                    }
                    if (remoteExperimentData.funding.sources.state) {
                        if (remoteExperimentData.funding.sources.state.raised !== undefined) {
                            this.experimentFunding.sources.state.raised = remoteExperimentData.funding.sources.state.raised
                        }
                    }
                }
            }
            var remoteMyRelationshipsData = remoteData.my_relationships
            this.myRelationships = remoteMyRelationshipsData
            var remoteAclData = remoteData.acl
            this.acl = remoteAclData
            this.setUserAcl()
            this.experimentLinks = JSON.parse(JSON.stringify(remoteExperimentData.links))
            this.experimentLocation = remoteExperimentData.geographic_location
        },
        getExperiment: function(apiUrl, experimentId, language) {
            // send the Auth header if logged in
            if (this.isUserAuthenticatedMixin()) {
                // debug info
                // this.debug('Authorization header: ' + this.getAuthorizationHeaderMixin())
                var authHeader = this.getAuthorizationHeaderMixin()
            }
            this.getExperimentMixin(apiUrl, experimentId, language, authHeader).then((response) => {
                // debug info
                this.debug('getExperimentMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'getExperiment')
                if (response.hasOwnProperty('data')) {
                    if (response.data.hasOwnProperty('status')) {
                        if (response.data.status === 'not_found') {
                            // debug info
                            this.debug('error fetching experiment:')
                            if (response.data.status.hasOwnProperty('message')) {
                                this.debug(response.data.message)
                            }
                            // show 404
                            this.experiment = null
                        } else {
                            this.setInitialValues(response.data)
                        }
                    }
                }
                // get the available tags
                if (this.isUserEditorMixin()) {
                    this.getTagsForExperiments(apiUrl, language)
                }
            }, (error) => {
                // debug info
                this.debug('getExperimentMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'getExperiment')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.getExperimentNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })

                // if not found, redirect
                if (error.response.data.hasOwnProperty('status')) {
                    if (error.response.data.status === 'not_found') {
                        if (this.redirectUrlNotFound) {
                            this.openUrl(this.redirectUrlNotFound)
                        }
                    }
                }
            })
        },
        getTagsForExperiments: function(apiUrl, language) {
            this.getTagsForExperimentsMixin(apiUrl, language).then((response) => {
                // debug info
                this.debug('getTagsForExperimentsMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'getTagsForExperiments')
                if (response.hasOwnProperty('data')) {
                    if (response.data.hasOwnProperty('tags')) {
                        this.tagsForExperiments = response.data.tags
                    }
                } else {
                    this.debug('hasOwnProperty(data.tags) error: ')
                }
            }, (error) => {
                // debug info
                this.debug('getTagsForExperimentsMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'getTagsForExperiments')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.getTagsForExperimentsNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        updateExperimentImage: function(imageName) {
            if (this.imageBody) {
                var message = this.getErrorMessage(this, {
                    data: {
                        status: 'saving_image',
                        message: 'Saving image.'
                    }
                })
                this.updateExperimentImageNotification({
                    message: message,
                    timeout: 5000,
                    type: 'info'
                })
                var authHeader = this.getAuthorizationHeaderMixin()
                this.uploadImageFileMixin(this.apiUrl, this.imageBody, authHeader).then((response) => {
                    // debug info
                    this.debug('uploadImageFileMixin response: ')
                    this.debug(this.getResponseData(response))
                    // success callback
                    this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'uploadImageFile')
                    if (response.hasOwnProperty('data')) {
                        if (response.data.hasOwnProperty('image')) {
                            if (response.data.image.hasOwnProperty('filename')) {
                                var experimentImageCollectionBody = {
                                    image_collection: {
                                        [imageName]: {
                                            filename: response.data.image.filename
                                        }
                                    }
                                }
                            }
                        }
                    }
                    this.imageBody = null
                    this.updateExperimentImageCollection(this.apiUrl, this.experiment.experiment_id, experimentImageCollectionBody)
                    var message = this.getErrorMessage(this, {
                        data: {
                            status: 'image_saved',
                            message: 'Image saved.'
                        }
                    })
                    this.updateExperimentImageNotification({
                        message: message,
                        timeout: 5000,
                        type: 'success'
                    })
                }, (error) => {
                    // debug info
                    this.debug('uploadImageFileMixin error: ')
                    this.debug(this.getResponseMessage(error.response))
                    // error callback
                    this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'uploadImageFile')
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
        updateExperimentSettings: function(apiUrl, experimentId, experimentBody, getExperiment = false) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.updateExperimentSettingsMixin(apiUrl, experimentId, experimentBody, authHeader).then((response) => {
                // debug info
                this.debug('updateExperimentSettingsMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'updateExperimentSettings')
                if (getExperiment) {
                    this.getExperiment(apiUrl, experimentId, this.language)
                }
            }, (error) => {
                // debug info
                this.debug('updateExperimentSettingsMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'updateExperimentSettings')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.updateExperimentSettingsNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        updateExperimentImageCollection: function(apiUrl, experimentId, experimentImageCollectionBody) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.updateExperimentImageCollectionMixin(apiUrl, experimentId, experimentImageCollectionBody, authHeader).then((response) => {
                // debug info
                this.debug('updateExperimentImageCollectionMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'updateExperimentImageCollection')
                // TODO: Maybe update client side object? problem is URL is generated server-side..
                // remove current image
                // TODO: improve this to be dynamic, not all-removing
                this.experiment.image_collection = {}
                this.showSpindle = true
                // get the experiment again to retrieve new image url
                this.getExperiment(apiUrl, experimentId, this.language)
            }, (error) => {
                // debug info
                this.debug('updateExperimentImageCollectionMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'updateExperimentImageCollection')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.updateExperimentImageNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        updateExperimentLanguage: function(apiUrl, experimentId, language, experimentLanguageBody, getExperiment = false) {
            // var infoToast = this.showNotification('Updating experiment', '', 'info', 0)
            var authHeader = this.getAuthorizationHeaderMixin()
            this.updateExperimentLanguageMixin(apiUrl, experimentId, language, experimentLanguageBody, authHeader).then((response) => {
                // debug info
                this.debug('updateExperimentLanguageMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'updateExperimentLanguage')
                if (getExperiment) {
                    this.getExperiment(apiUrl, experimentId, language)
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
        updateExperimentCustomLanguage: function(apiUrl, experimentId, language, experimentLanguageBody, getExperiment = false) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.updateExperimentCustomLanguageMixin(apiUrl, experimentId, language, experimentLanguageBody, authHeader).then((response) => {
                // debug info
                this.debug('updateExperimentCustomLanguageMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'updateExperimentCustomLanguage')
                if (getExperiment) {
                    this.getExperiment(apiUrl, experimentId, language)
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
        },
        updateExperimentFunding: function(apiUrl, experimentId, experimentFundingBody, getExperiment = false) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.updateExperimentFundingMixin(apiUrl, experimentId, experimentFundingBody, authHeader).then((response) => {
                // debug info
                this.debug('updateExperimentFundingMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'updateExperimentFunding')
                if (getExperiment) {
                    this.getExperiment(apiUrl, experimentId, this.language)
                }
            }, (error) => {
                // debug info
                this.debug('updateExperimentFundingMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'updateExperimentFunding')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.updateExperimentFundingNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        addExperimentLike: function(apiUrl, experimentId) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.addExperimentLikeMixin(apiUrl, experimentId, authHeader).then((response) => {
                // debug info
                this.debug('addExperimentLikeMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'addExperimentLike')
                if (this.experiment.hasOwnProperty('like_count')) {
                    this.experiment.like_count += 1
                } else {
                    this.experiment.like_count = 1
                }
                this.myRelationships.push('like')
                // debug info
                this.debug('adding a like')
            }, (error) => {
                // debug info
                this.debug('addExperimentLikeMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'addExperimentLike')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.addExperimentLikeMixinNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        removeExperimentLike: function(apiUrl, experimentId) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.removeExperimentLikeMixin(apiUrl, experimentId, authHeader).then((response) => {
                // debug info
                this.debug('removeExperimentLikeMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'removeExperimentLike')
                // remove a like
                this.experiment.like_count -= 1
                for (var i = 0; i < this.myRelationships.length; i++) {
                    if (this.myRelationships[i] === 'like') {
                        this.debug('Removing relationship: ' + this.myRelationships[i])
                        var index = this.myRelationships.indexOf(this.myRelationships[i])
                        this.myRelationships.splice(index, 1)
                    }
                }
                // debug info
                this.debug('removing a like')
            }, (error) => {
                // debug info
                this.debug('removeExperimentLikeMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'removeExperimentLike')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.addExperimentLikeMixinNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        updateExperimentEditable: function(postBody, type) {
            // debug info
            // this.debug(postBody)
            if (type === 'setting') {
                let settingsBody = {
                    settings: postBody
                }
                this.updateExperimentSettings(this.apiUrl, this.experimentId, settingsBody, true)
            } else if (type === 'funding') {
                let fundingBody = postBody['funding']
                this.updateExperimentFunding(this.apiUrl, this.experimentId, fundingBody, true)
            } else {
                // debug info
                this.debug('experiment editable type "' + type + '" not implemented')
            }
        },
        onImageLoad: function() {
            // debug info
            this.debug('image loaded')
            this.showSpindle = false
            this.imageLoaded = true
        },
        getLanguage: function(key, defaultLanguage = false, html = false) {
            if (!defaultLanguage) {
                if (this.experiment.custom_language) {
                    if (this.experiment.custom_language[key]) {
                        if (html) {
                            return this.experiment.custom_language[key].html
                        } else {
                            return this.experiment.custom_language[key].value
                        }
                    } else {
                        return null
                    }
                } else {
                    return null
                }
            } else {
                if (this.experiment.language) {
                    if (this.experiment.language[key]) {
                        if (html) {
                            return this.experiment.language[key].html
                        } else {
                            return this.experiment.language[key].value
                        }
                    } else {
                        return null
                    }
                } else {
                    return null
                }
            }
        },
        goToTheNextStage: function() {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.moveExperimentToNextStageMixin(this.apiUrl, this.experimentId, authHeader).then((response) => {
                // debug info
                this.debug('moveExperimentToNextStageMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'goToTheNextStage')
                // fetch the experiment to get possible other back-end changes
                this.getExperiment(this.apiUrl, this.experimentId, this.language)
                var message = this.getResponseMessage(response)
                this.goToTheNextStageNotification({
                    message: message,
                    timeout: 5000,
                    type: 'success'
                })
            }, (error) => {
                // debug info
                this.debug('moveExperimentToNextStageMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'goToTheNextStage')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.goToTheNextStageNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        updateExperimentStage: function(apiUrl, experimentId, newExperimentStage) {
            if (newExperimentStage !== parseInt(this.experiment.stage)) {
                var experimentBody = {
                    settings: {
                        stage: newExperimentStage.toString()
                    }
                }
                // update client side object
                this.experiment.stage = experimentBody.settings.stage
                this.displayedExperimentStage = experimentBody.settings.stage
                // collapse editing section
                this.editExperimentStage = false
                // make API call
                this.updateExperimentSettings(apiUrl, experimentId, experimentBody, true)
            } else {
                // debug info
                this.debug('experiment stage not changed')
            }
        },
        updateExperimentShowIn: function(apiUrl, experimentId) {
            if (this.isShowInModified) {
                var experimentBody = {
                    settings: {
                        show_in: this.selectedExperimentShowIn
                    }
                }
                // update client side object
                this.experiment.show_in = Object.assign({}, this.selectedExperimentShowIn)
                // collapse editing section
                this.editExperimentShowIn = false
                // make API call
                this.updateExperimentSettings(apiUrl, experimentId, experimentBody)
            } else {
                // debug info
                this.debug('experiment show in not changed')
            }
        },
        updateExperimentDisabled: function(e, apiUrl, experimentId) {
            if (this.isDisabledAgreed) {
                var experimentBody = {
                    settings: {
                        disabled: true
                    }
                }
                // update client side object
                this.experiment.disabled = true
                this.experimentDisabledAgree = false
                // collapse editing section
                this.editExperimentDisabled = false
                // show alert
                var confirm = window.confirm('Are you sure that you want to DELETE this experiment?')
                if (confirm) {
                    // make API call
                    this.updateExperimentSettings(apiUrl, experimentId, experimentBody, true)
                } else {
                    // debug info
                    this.debug('must confirm to disable the experiment')
                }
            } else {
                // debug info
                this.debug('must agree to disable the experiment')
            }
        },
        updateExperimentGoals: function(apiUrl, experimentId) {
            this.$validator.validateAll().then(success => {
                if (success) {
                    // debug info
                    this.debug('update funding form is valid')
                    if (this.experimentFundingModified) {
                        var experimentFundingBody = {}
                        var crowd = {}
                        if (this.experimentFundingOrganizationsRaisedModified) {
                            if (!experimentFundingBody.hasOwnProperty('sources')) {
                                experimentFundingBody.sources = {}
                            }
                            if (Object.keys(experimentFundingBody.sources).length === -1) {
                                experimentFundingBody.sources = {
                                    organizations: {}
                                }
                            } else if (!experimentFundingBody.sources.hasOwnProperty('organizations')) {
                                experimentFundingBody.sources.organizations = {}
                            }
                            experimentFundingBody.sources.organizations.raised = this.experimentFunding.sources.organizations.raised
                        }
                        if (this.experimentFundingStateRaisedModified) {
                            if (!experimentFundingBody.hasOwnProperty('sources')) {
                                experimentFundingBody.sources = {}
                            }
                            if (Object.keys(experimentFundingBody.sources).length === -1) {
                                experimentFundingBody.sources = {
                                    state: {}
                                }
                            } else if (!experimentFundingBody.sources.hasOwnProperty('state')) {
                                experimentFundingBody.sources.state = {}
                            }
                            experimentFundingBody.sources.state.raised = this.experimentFunding.sources.state.raised
                        }
                        if (this.experimentFundingCrowdCampaignIdModified) {
                            crowd.campaign_id = this.experimentFunding.sources.crowd.campaign_id
                        }
                        if (this.experimentFundingCrowdCampaignIdModified) {
                            crowd.api = this.experimentFunding.sources.crowd.api
                        }
                        if (this.experimentFundingGoalModified) {
                            experimentFundingBody.goal = this.experimentFunding.goal
                        }
                        if (this.experimentFundingCurrencyModified) {
                            experimentFundingBody.currency = this.experimentFunding.currency
                        }
                        // merge objects
                        if (Object.keys(crowd).length > 0) {
                            if (!experimentFundingBody.hasOwnProperty('sources')) {
                                experimentFundingBody.sources = {
                                    crowd: {}
                                }
                            } else {
                                if (!experimentFundingBody.sources.hasOwnProperty('crowd')) {
                                    experimentFundingBody.sources.crowd = {}
                                }
                            }
                            experimentFundingBody.sources.crowd = Object.assign({}, experimentFundingBody.sources.crowd, crowd)
                        }
                        // set client-side values
                        if (experimentFundingBody.hasOwnProperty('sources')) {
                            if (experimentFundingBody.sources.hasOwnProperty('state')) {
                                if (Object.keys(this.experiment.funding.sources).length === -1) {
                                    this.experiment.funding.sources = {}
                                }
                                if (!this.experiment.funding.sources.hasOwnProperty('state')) {
                                    this.experiment.funding.sources.state = {}
                                }
                                this.experiment.funding.sources.state.raised = this.experimentFunding.sources.state.raised
                            }
                            if (experimentFundingBody.sources.hasOwnProperty('organizations')) {
                                if (Object.keys(this.experiment.funding.sources).length === -1) {
                                    this.experiment.funding.sources = {}
                                }
                                if (!this.experiment.funding.sources.hasOwnProperty('organizations')) {
                                    this.experiment.funding.sources.organizations = {}
                                }
                                this.experiment.funding.sources.organizations.raised = this.experimentFunding.sources.organizations.raised
                            }
                            if (experimentFundingBody.sources.hasOwnProperty('crowd')) {
                                if (Object.keys(this.experiment.funding.sources).length === -1) {
                                    this.experiment.funding.sources = {}
                                }
                                this.experiment.funding.sources.crowd = Object.assign({}, this.experiment.funding.sources.crowd, this.experimentFunding.sources.crowd)
                                // emit event so that reward index can react
                                if (experimentFundingBody.sources.crowd.hasOwnProperty('campaign_id')) {
                                    this.$nextTick(() => {
                                        this.$root.eventBus.$emit('campaign-id-updated')
                                    })
                                }
                            }
                        }
                        if (experimentFundingBody.hasOwnProperty('goal')) {
                            this.experiment.funding.goal = this.experimentFunding.goal
                        }
                        if (experimentFundingBody.hasOwnProperty('currency')) {
                            this.experiment.funding.currency = this.experimentFunding.currency
                        }
                        // collapse editing section
                        this.editExperimentFunding = false
                        // make API call
                        this.updateExperimentFunding(apiUrl, experimentId, experimentFundingBody)
                    } else {
                        // debug info
                        this.debug('update funding form not modified')
                    }
                } else {
                    this.showFundingErrors = true
                    // debug info
                    this.debug('update funding form has errors')
                }
            })
        },
        publishExperiment: function() {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.publishExperimentMixin(this.apiUrl, this.experimentId, authHeader).then((response) => {
                // debug info
                this.debug('publishExperimentMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'publishExperiment')
                this.$set(this.selectedExperimentShowIn, 'index', true)
                // update client side object
                this.experiment.show_in = Object.assign({}, this.selectedExperimentShowIn)
            }, (error) => {
                // debug info
                this.debug('publishExperimentMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'publishExperiment')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.publishExperimentNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        unpublishExperiment: function() {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.unpublishExperimentMixin(this.apiUrl, this.experimentId, authHeader).then((response) => {
                // debug info
                this.debug('unpublishExperimentMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'unpublishExperiment')
                this.$set(this.selectedExperimentShowIn, 'index', false)
                // update client side object
                this.experiment.show_in = Object.assign({}, this.selectedExperimentShowIn)
            }, (error) => {
                // debug info
                this.debug('unpublishExperimentMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'unpublishExperiment')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.unpublishExperimentNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        setExperimentLinks: function(apiUrl, experimentId, experimentLinksBody, language) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.setExperimentLinksMixin(apiUrl, experimentId, experimentLinksBody, authHeader).then((response) => {
                // debug info
                this.debug('setExperimentLinksMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'setExperimentLinks')
                // set client side
                this.experimentLinks = JSON.parse(JSON.stringify(experimentLinksBody))
                // get the experiment again to retrieve the links
                this.getExperiment(apiUrl, experimentId, language)
            }, (error) => {
                // debug info
                this.debug('setExperimentLinksMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'setExperimentLinks')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.setExperimentLinksNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        saveExperimentLinks: function(apiUrl, experimentId) {
            this.$validator.validateAll('experiment-links-form').then(success => {
                if (success) {
                    // debug info
                    this.debug('experiment links form is valid')
                    // set client-side values
                    var experimentLinksBody = {}
                    if (this.areExperimentLinksModified) {
                        var links = []
                        for (var i = 0; i < this.experimentLinks.length; i++) {
                            if (this.experimentLinks[i].url !== '' || this.experimentLinks[i].title !== '') {
                                var link = {
                                    url: this.experimentLinks[i].url,
                                    title: this.experimentLinks[i].title
                                }
                                links.push(link)
                            }
                        }
                        experimentLinksBody.links = links
                        // perform api request
                        this.setExperimentLinks(apiUrl, experimentId, experimentLinksBody, this.language)
                        // collapse editing section
                        this.editExperimentLinks = false
                    } else {
                        // debug info
                        this.debug('experiment links form not modified')
                    }
                } else {
                    this.showExperimentLinksErrors = true
                    // debug info
                    this.debug('experiment links form has errors')
                }
            })
        },
        saveExperimentLocation: function(apiUrl, experimentId) {
            if (this.isExperimentLocationModified) {
                var experimentSettingsBody = {
                    settings: {
                        geographic_location: this.experimentLocation
                    }
                }
                // update client side
                this.experiment.geographic_location = this.experimentLocation
                // perform api request
                this.updateExperimentSettings(apiUrl, experimentId, experimentSettingsBody)
                // collapse editing section
                this.editExperimentLocation = false
            } else {
                // debug info
                this.debug('experiment location form not modified')
            }
        },
        experimentLanguageUpdated: function(experimentLanguageBody, customLanguage = false, isSirTrevor = false) {
            // debug info
            this.debug('experimentLanguageUpdated')
            if (customLanguage) {
                // set the values client-side
                this.experiment.custom_language = Object.assign({}, this.experiment.custom_language, experimentLanguageBody.custom_language)
                this.updateExperimentCustomLanguage(this.apiUrl, this.experiment.experiment_id, this.language, experimentLanguageBody, isSirTrevor)
            } else {
                // initialize in case no language exists
                if (!this.experiment.language) {
                    this.experiment.language = {}
                }
                // set the values client-side
                for (var key in experimentLanguageBody.language) {
                    if (experimentLanguageBody.language.hasOwnProperty(key)) {
                        this.$set(this.experiment.language, key, experimentLanguageBody.language[key])
                    }
                }
                this.updateExperimentLanguage(this.apiUrl, this.experiment.experiment_id, this.language, experimentLanguageBody, isSirTrevor)
            }
        },
        experimentTagsAdded: function(tags, tagsIds) {
            // debug info
            this.debug('adding tags to experiment')
            this.debug(tags)
            this.debug(tagsIds)
            // set client-side object
            this.experiment.tags = []
            for (var i = 0; i < tags.length; i++) {
                this.experiment.tags.push(tags[i])
            }
            var experimentTagsBody = {
                tags: tagsIds
            }
            // make API calls
            this.setTagsForExperiment(this.apiUrl, this.experiment.experiment_id, experimentTagsBody)
        },
        setImage: function(imageData) {
            this.imageBody = imageData
        },
        resetStage: function() {
            this.editExperimentStage = false
            this.selectedExperimentStage = this.experiment.stage
        },
        initializeShowInEditor: function() {
            if (this.editExperimentShowIn === false) {
                this.editExperimentShowIn = true
            }
        },
        initializeDisabledEditor: function() {
            if (this.editExperimentDisabled === false) {
                this.editExperimentDisabled = true
            }
        },
        initializeFundingEditor: function() {
            if (this.editExperimentFunding === false) {
                this.editExperimentFunding = true
            }
        },
        initializeLinksEditor: function() {
            if (this.editExperimentLinks === false) {
                this.editExperimentLinks = true
            }
        },
        initializeLocationEditor: function() {
            if (this.editExperimentLocation === false) {
                this.editExperimentLocation = true
            }
        },
        resetShowIn: function() {
            this.editExperimentShowIn = false
            this.selectedExperimentShowIn = Object.assign({}, this.experiment.show_in)
        },
        resetDisabled: function() {
            this.editExperimentDisabled = false
            this.experimentDisabledAgree = false
        },
        resetFunding: function() {
            if (this.editExperimentFunding === true) {
                this.editExperimentFunding = false
                // reset values
                if (this.experiment.hasOwnProperty('funding')) {
                    if (this.experiment.funding.hasOwnProperty('goal')) {
                        this.experimentFunding.goal = this.experiment.funding.goal
                    }
                    if (this.experiment.funding.hasOwnProperty('currency')) {
                        this.experimentFunding.currency = this.experiment.funding.currency
                    }
                    if (this.experiment.funding.hasOwnProperty('sources')) {
                        if (this.experiment.funding.sources.hasOwnProperty('crowd')) {
                            if (this.experiment.funding.sources.crowd.hasOwnProperty('campaign_id')) {
                                this.experimentFunding.sources.crowd.campaign_id = this.experiment.funding.sources.crowd.campaign_id
                            }
                            if (this.experiment.funding.sources.crowd.hasOwnProperty('api')) {
                                this.experimentFunding.sources.crowd.api = this.experiment.funding.sources.crowd.api
                            }
                        }
                        if (this.experiment.funding.sources.hasOwnProperty('organizations')) {
                            if (this.experiment.funding.sources.organizations.hasOwnProperty('raised')) {
                                this.experimentFunding.sources.organizations.raised = this.experiment.funding.sources.organizations.raised
                            }
                        }
                        if (this.experiment.funding.sources.hasOwnProperty('state')) {
                            if (this.experiment.funding.sources.state.hasOwnProperty('raised')) {
                                this.experimentFunding.sources.state.raised = this.experiment.funding.sources.state.raised
                            }
                        }
                    }
                }
                // reset validation errors
                this.showFundingErrors = false
                this.errors.clear()
            }
        },
        resetLocationEditor: function() {
            if (this.editExperimentLocation === true) {
                this.editExperimentLocation = false
                if (this.isExperimentLocationModified) {
                    this.experimentLocation = this.experiment.geographic_location
                }
            }
        },
        resetLinksEditor: function() {
            if (this.editExperimentLinks === true) {
                this.editExperimentLinks = false
                if (this.areExperimentLinksModified) {
                    this.experimentLinks = JSON.parse(JSON.stringify(this.experiment.links))
                }
            }
        },
        sortShowInByKey: function(shownIn) {
            var ordered = {}
            Object.keys(shownIn).sort().forEach(function(key) {
                ordered[key] = shownIn[key]
            })
            return ordered
        },
        toggleShowIn: function(e, key) {
            // TODO: there is probabaly a way to simplify this code. The main issue is that v-model seems to react with delay to foundation switch changes. The code below forces that change. The correct way is probably using nextTick or something like that
            var switchOn = e.target.checked
            // initialize on client-side if key not present
            if (!this.experiment.show_in.hasOwnProperty(key)) {
                this.$set(this.experiment.show_in, key, false)
            }
            this.$set(this.selectedExperimentShowIn, key, switchOn)
        },
        openFundingPage: function(pre, id, post) {
            if (id) {
                this.openUrl(pre + id + post, true)
            } else {
                // debug info
                this.debug('campaign id not defined')
            }
        },
        organizationsRaised: function() {
            if (this.experiment.funding) {
                if (this.experiment.funding.sources) {
                    if (this.experiment.funding.sources.organizations) {
                        if (this.experiment.funding.sources.organizations.raised) {
                            return this.experiment.funding.sources.organizations.raised
                        }
                    }
                }
            }
            return 0
        },
        stateRaised: function() {
            if (this.experiment.funding) {
                if (this.experiment.funding.sources) {
                    if (this.experiment.funding.sources.state) {
                        if (this.experiment.funding.sources.state.raised) {
                            return this.experiment.funding.sources.state.raised
                        }
                    }
                }
            }
            return 0
        },
        crowdRaised: function() {
            return this.experimentFundingCrowdRaised
        },
        likeExperiment: function() {
            if (this.isUserAuthenticatedMixin()) {
                if (!this.userLiked) {
                    this.addExperimentLike(this.apiUrl, this.experiment.experiment_id)
                } else {
                    this.removeExperimentLike(this.apiUrl, this.experiment.experiment_id)
                }
            } else {
                this.debug('not logged in')
                this.$root.eventBus.$emit('showModal', 'sign-in', true)
            }
        },
        addExperimentLinkField: function() {
            var link = {
                url: '',
                title: ''
            }
            this.experimentLinks.push(link)
        },
        removeExperimentLinkField: function(index) {
            this.experimentLinks.splice(index, 1)
        }
    },
    computed: {
        placeholderImage: function() {
            if (this.placeholderImageUrl) {
                return this.placeholderImageUrl
            } else {
                return this.common.placeholders.defaultPlaceholderImageUrl
            }
        },
        isShowInModified: function() {
            this.experiment.show_in = this.sortShowInByKey(this.experiment.show_in)
            this.selectedExperimentShowIn = this.sortShowInByKey(this.selectedExperimentShowIn)
            return JSON.stringify(this.experiment.show_in) !== JSON.stringify(this.selectedExperimentShowIn)
        },
        isDisabledAgreed: function() {
            return this.experimentDisabledAgree
        },
        experimentFundingOrganizationsRaisedModified: function() {
            var currentOrganizationsRaised = null
            if (this.experimentFunding.sources.organizations.raised !== null) {
                if (this.experiment.hasOwnProperty('funding')) {
                    if (this.experiment.funding.hasOwnProperty('sources')) {
                        if (this.experiment.funding.sources.hasOwnProperty('organizations')) {
                            currentOrganizationsRaised = this.experiment.funding.sources.organizations.raised
                        }
                    }
                }
                if (currentOrganizationsRaised !== this.experimentFunding.sources.organizations.raised) {
                    return true
                } else {
                    return false
                }
            } else {
                return false
            }
        },
        experimentFundingStateRaisedModified: function() {
            var currentStateRaised = null
            if (this.experimentFunding.sources.state.raised !== null) {
                if (this.experiment.hasOwnProperty('funding')) {
                    if (this.experiment.funding.hasOwnProperty('sources')) {
                        if (this.experiment.funding.sources.hasOwnProperty('state')) {
                            currentStateRaised = this.experiment.funding.sources.state.raised
                        }
                    }
                }
                if (currentStateRaised !== this.experimentFunding.sources.state.raised) {
                    return true
                } else {
                    return false
                }
            } else {
                return false
            }
        },
        experimentFundingCrowdCampaignIdModified: function() {
            var currentCampaignId = null
            if (this.experimentFunding.sources.crowd.campaign_id !== null) {
                if (this.experiment.hasOwnProperty('funding')) {
                    if (this.experiment.funding.hasOwnProperty('sources')) {
                        if (this.experiment.funding.sources.hasOwnProperty('crowd')) {
                            currentCampaignId = this.experiment.funding.sources.crowd.campaign_id
                        }
                    }
                }
                if (currentCampaignId !== this.experimentFunding.sources.crowd.campaign_id && this.experimentFunding.sources.crowd.campaign_id !== '') {
                    return true
                } else {
                    return false
                }
            } else {
                return false
            }
        },
        experimentFundingCrowdApiUrlModified: function() {
            var currentCampaignApiUrl = null
            if (this.experimentFunding.sources.crowd.api !== null && this.experimentFunding.sources.crowd.api !== undefined) {
                if (this.experiment.hasOwnProperty('funding')) {
                    if (this.experiment.funding.hasOwnProperty('sources')) {
                        if (this.experiment.funding.sources.hasOwnProperty('crowd')) {
                            currentCampaignApiUrl = this.experiment.funding.sources.crowd.api
                        }
                    }
                }
                if (currentCampaignApiUrl !== this.experimentFunding.sources.crowd.api) {
                    return true
                } else {
                    return false
                }
            } else {
                return false
            }
        },
        experimentFundingGoalModified: function() {
            var currentGoal = null
            if (this.experimentFunding.goal !== null) {
                if (this.experiment.hasOwnProperty('funding')) {
                    if (this.experiment.funding.hasOwnProperty('goal')) {
                        currentGoal = this.experiment.funding.goal
                    }
                }
                if (currentGoal !== this.experimentFunding.goal && this.experimentFunding.goal !== '') {
                    return true
                } else {
                    return false
                }
            } else {
                return false
            }
        },
        experimentFundingCurrencyModified: function() {
            var currentCurrency = null
            if (this.experimentFunding.goal !== null) {
                if (this.experiment.hasOwnProperty('funding')) {
                    if (this.experiment.funding.hasOwnProperty('currency')) {
                        currentCurrency = this.experiment.funding.currency
                    }
                }
                if (currentCurrency !== this.experimentFunding.currency) {
                    return true
                } else {
                    return false
                }
            } else {
                return false
            }
        },
        experimentFundingModified: function() {
            if (this.experimentFundingOrganizationsRaisedModified || this.experimentFundingStateRaisedModified || this.experimentFundingCrowdCampaignIdModified || this.experimentFundingCrowdApiUrlModified || this.experimentFundingGoalModified ||
                this.experimentFundingCurrencyModified) {
                return true
            } else {
                return false
            }
        },
        experimentRaised: function() {
            return this.crowdRaised() + parseInt(this.stateRaised()) + parseInt(this.organizationsRaised())
        },
        raisedPercent: function() {
            if (this.experiment.funding.goal) {
                return Math.round((this.experimentRaised / this.experiment.funding.goal) * 100)
            } else {
                return 0
            }
        },
        likeCount: function() {
            if (this.experiment.hasOwnProperty('like_count')) {
                return this.experiment.like_count
            } else {
                return 0
            }
        },
        userLiked: function() {
            for (var i = 0; i < this.myRelationships.length; i++) {
                if (this.myRelationships[i] === 'like') {
                    return true
                }
            }
            return false
        },
        areExperimentLinksModified: function() {
            if (this.experiment.hasOwnProperty('links')) {
                if (this.experiment.links.length !== 0) {
                    return JSON.stringify(this.experiment.links) !== JSON.stringify(this.experimentLinks)
                }
            }
            if (this.experimentLinks.length !== 0) {
                for (var i = 0; i < this.experimentLinks.length; i++) {
                    if (this.experimentLinks[i].url !== '' || this.experimentLinks[i].title !== '') {
                        return true
                    }
                }
            }
            return false
        },
        isExperimentLocationModified: function() {
            return this.experiment.geographic_location !== this.experimentLocation && this.experimentLocation !== undefined
        },
        isExperimentPublished: function() {
            if (this.experiment.show_in !== null) {
                if (this.experiment.show_in.hasOwnProperty('index')) {
                    return this.experiment.show_in.index
                } else {
                    return false
                }
            } else {
                return false
            }
        }
    },
    created: function() {
        this.$root.eventBus.$on('login', function() {
            this.setup()
        }.bind(this))
        this.$root.eventBus.$on('logout', function() {
            this.setup()
        }.bind(this))
        this.$root.eventBus.$on('profile', function() {
            this.setup()
        }.bind(this))
        this.$root.eventBus.$on('crowdfunding:connected', function(stage, raised) {
            if (stage) {
                this.experimentFundingCrowdStage = stage
            }
            if (raised) {
                this.experimentFundingCrowdRaised = raised
            }
        }.bind(this))
    },
    watch: {
        // TODO: this is a workaround because select v-model="selectedExperimentStage" casts the value as String. In theory, the 'number' attribute can fix that, but it doesn't seem to work
        selectedExperimentStage: function(val) {
            if (typeof this.selectedExperimentStage === 'string') {
                this.selectedExperimentStage = parseInt(this.selectedExperimentStage)
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
