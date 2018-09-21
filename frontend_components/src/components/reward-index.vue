<template>

</template>

<script>
import campaigns from '../mixins/campaigns.js'
import rewards from '../mixins/rewards.js'
import date from '../mixins/date.js'
import auth from '../mixins/auth.js'
import helpers from '../mixins/helpers.js'
export default {
    name: 'reward-index',
    data() {
        return {
            reward: this.common.placeholders.reward,
            rewards: [],
            campaign: null,
            responseStatus: {},
            showAddRewardEditor: false,
            rewardAmount: '',
            rewardTitle: '',
            rewardDescription: '',
            rewardStock: '',
            customQuestion: '',
            customQuestions: [],
            stockLimited: false,
            imageBody: null,
            showAddRewardErrors: false,
            editRewardStock: false,
            editRewardQuestions: false,
            editRewardImage: false,
            askForAddress: false,
            editRewardAddress: false,
            selectedWithoutReward: false
        }
    },
    props: {
        apiUrl: {
            required: false,
            type: String
        },
        language: {
            required: true,
            type: String
        },
        campaignId: {
            required: false,
            type: String
        },
        placeholderImageUrl: {
            required: false,
            default: false
        },
        selectedRewardId: {
            required: false,
            type: String
        },
        initialCustomRewardAmount: {
            required: false,
            type: String
        },
        // inherit global data from parent
        common: {
            required: true,
            type: Object
        },
        // enable legacy login system
        phpLogin: {
            required: false,
            type: Boolean,
            default: false
        }
    },
    mixins: [
        campaigns,
        rewards,
        date,
        auth,
        helpers
    ],
    notifications: {
        getCampaignNotification: {},
        createRewardNotification: {},
        updateRewardNotification: {},
        updateRewardLanguageNotification: {},
        uploadCampaignFileNotification: {}
    },
    methods: {
        setup: function() {
            if (this.campaignId) {
                // make API call
                this.getCampaign(this.apiUrl, this.campaignId, this.language)
            } else {
                // debug info
                this.debug('campaign ID not defined')
            }
        },
        getCampaign: function(apiUrl, campaignId, language) {
            this.getCampaignMixin(apiUrl, campaignId, language).then((response) => {
                // debug info
                this.debug('getCampaignMixin response:')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'getCampaign')
                if (response.hasOwnProperty('data')) {
                    if (response.data.hasOwnProperty('campaign')) {
                        this.campaign = response.data.campaign
                        // Get funding data end emit vie event
                        var raised = null
                        var stage = null
                        if (response.data.campaign.hasOwnProperty('funding_reached')) {
                            raised = this.campaign.funding_reached
                        }
                        if (response.data.campaign.hasOwnProperty('stage')) {
                            stage = this.campaign.stage
                        }
                        this.$root.eventBus.$emit('crowdfunding:connected', stage, raised)
                        // set rewards
                        if (response.data.campaign.hasOwnProperty('rewards')) {
                            this.$set(this, 'rewards', response.data.campaign.rewards)
                            // preselected reward
                            if (this.selectedRewardId) {
                                for (var i = 0; i < this.rewards.length; i++) {
                                    if (this.rewards[i].reward_id === parseInt(this.selectedRewardId)) {
                                        // emit event so that parent can react
                                        this.$emit('reward-selected', this.rewards[i])
                                    }
                                }
                            }
                        }
                    }
                    if (response.data.hasOwnProperty('acl')) {
                        this.$set(this, 'acl', response.data.acl)
                        this.setUserAcl()
                    }
                }
            }, (error) => {
                // debug info
                this.debug('getCampaignMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'getCampaign')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.getCampaignNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        addReward(apiUrl, campaignId, language) {
            this.$validator.validateAll('add-reward-form').then(success => {
                if (success) {
                    // debug info
                    this.debug('add reward form is valid')
                    this.showAddRewardErrors = false
                    var authHeader = this.getAuthorizationHeaderMixin()
                    this.createRewardMixin(apiUrl, campaignId, authHeader).then((response) => {
                        // debug info
                        this.debug('createRewardMixin response:')
                        this.debug(this.getResponseData(response))
                        // success callback
                        this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'createReward')
                        var rewardObject = {
                            reward_id: response.data.reward.reward_id,
                            amount: parseInt(this.rewardAmount),
                            description: this.rewardDescription,
                            title: this.rewardTitle,
                            stock: null,
                            stock_reserved: 0,
                            image: null,
                            custom_questions: []
                        }
                        var rewardBody = {
                            amount: parseInt(this.rewardAmount)
                        }
                        if (this.isRewardStockModified) {
                            rewardBody.stock = parseInt(this.rewardStock)
                            rewardObject.stock = parseInt(this.rewardStock)
                        }
                        var rewardLanguageBody = {}
                        if (this.isRewardDescriptionModified) {
                            rewardLanguageBody.description = {
                                [language]: this.rewardDescription
                            }
                        }
                        if (this.isRewardTitleModified) {
                            rewardLanguageBody.title = {
                                [language]: this.rewardTitle
                            }
                        }
                        this.updateReward(apiUrl, response.data.reward.reward_id, rewardBody)
                        if (this.isRewardDescriptionModified || this.isRewardTitleModified) {
                            this.updateRewardLanguage(apiUrl, response.data.reward.reward_id, rewardLanguageBody, language)
                        }
                        if (this.isCustomQuestionsModified || this.askForAddress) {
                            var questions = this.customQuestions
                            if (this.askForAddress) {
                                var askForAddress = {
                                    key: 'custom_question_' + questions.length + 1,
                                    text: 'ask_for_address'
                                }
                                questions.push(askForAddress)
                            }
                            var rewardCustomQuestionsBody = {
                                custom_questions: {
                                    [language]: questions
                                }
                            }
                            this.updateRewardLanguage(apiUrl, response.data.reward.reward_id, rewardCustomQuestionsBody, language)
                            for (var i = 0; i < rewardCustomQuestionsBody.custom_questions[language].length; i++) {
                                rewardObject.custom_questions.push(rewardCustomQuestionsBody.custom_questions[language][i])
                            }
                        }
                        if (this.isRewardImageModified) {
                            rewardObject.image = ''
                            this.uploadRewardImage(apiUrl, this.campaign.campaign_id, response.data.reward.reward_id, this.imageBody)
                        }
                        if (!this.rewards) {
                            this.rewards = []
                        }
                        // insert reward object into rewards array
                        this.rewards.push(rewardObject)
                        // collapse editing part
                        this.showAddRewardEditor = false
                        this.editRewardStock = false
                        this.editRewardQuestions = false
                        this.editRewardImage = false
                        this.editRewardAddress = false
                    }, (error) => {
                        // debug info
                        this.debug('createRewardMixin error: ')
                        this.debug(this.getResponseMessage(error.response))
                        // error callback
                        this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'createRewardMixin')
                        // show error message
                        var message = this.getErrorMessage(this, error.response)
                        this.createRewardNotification({
                            message: message,
                            timeout: 5000,
                            type: 'error'
                        })
                    })
                } else {
                    // debug info
                    this.debug('add reward form has errors')
                    this.showAddRewardErrors = true
                }
            })
        },
        updateReward(apiUrl, rewardId, rewardBody) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.updateRewardMixin(apiUrl, rewardId, rewardBody, authHeader).then((response) => {
                // debug info
                this.debug('updateRewardMixin response:')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'updateRewardMixin')
                if (rewardBody.hasOwnProperty('image')) {
                    this.setRewardImage(rewardId, rewardBody.image)
                }
            }, (error) => {
                // debug info
                this.debug('updateRewardMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'updateRewardMixin')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.updateRewardNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        updateRewardLanguage(apiUrl, rewardId, rewardLanguageBody, language) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.updateRewardLanguageMixin(apiUrl, rewardId, rewardLanguageBody, authHeader).then((response) => {
                // debug info
                this.debug('updateRewardLanguageMixin response:')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'updateRewardLanguageMixin')
            }, (error) => {
                // debug info
                this.debug('updateRewardLanguageMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'updateRewardLanguageMixin')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.updateRewardLanguageNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        uploadRewardImage(apiUrl, campaignId, rewardId, rewardImageBody) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.uploadCampaignFileMixin(apiUrl, campaignId, rewardImageBody, authHeader).then((response) => {
                // debug info
                this.debug('uploadCampaignFileMixin response:')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'uploadCampaignFileMixin')
                if (response.data.hasOwnProperty('file')) {
                    if (response.data.file.hasOwnProperty('name')) {
                        var rewardBody = {
                            'image': response.data.file.name
                        }
                        this.updateReward(apiUrl, rewardId, rewardBody)
                    }
                }
            }, (error) => {
                // debug info
                this.debug('uploadCampaignFileMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'uploadCampaignFileMixin')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.uploadCampaignFileNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        sortRewards: function(rewardsArray) {
            return rewardsArray.slice().sort(function(a, b) {
                if (a.amount - b.amount === 0) {
                    return a.reward_id - b.reward_id
                } else {
                    return a.amount - b.amount
                }
            })
        },
        setImage: function(imageData) {
            this.imageBody = imageData
        },
        setRewardImage: function(rewardId, rewardImage) {
            for (var i = 0; i < this.rewards.length; i++) {
                if (this.rewards[i].reward_id === rewardId) {
                    this.rewards[i].image = 'https://s3-eu-west-1.amazonaws.com/kfunddynamic/project_content_pics/' + this.campaign.campaign_id + '/medium/' + rewardImage
                    break
                }
            }
        },
        removeReward: function(rewardId) {
            for (var i = 0; i < this.rewards.length; i++) {
                if (this.rewards[i].reward_id === rewardId) {
                    var index = this.rewards.indexOf(this.rewards[i])
                    this.rewards.splice(index, 1)
                    this.debug('Removing reward: ' + rewardId)
                    break
                }
            }
        },
        initializeAddRewardEditor: function() {
            if (this.showAddRewardEditor === false) {
                this.showAddRewardEditor = true
            }
        },
        resetAddRewardEditor: function() {
            if (this.showAddRewardEditor === true) {
                this.showAddRewardEditor = false
                this.rewardAmount = ''
                this.rewardDescription = ''
                this.rewardTitle = ''
                this.rewardStock = ''
                this.customQuestion = ''
                this.customQuestions = []
                this.askForAddress = false
                this.stockLimited = false
                this.imageBody = null
                if (this.$refs.imageUploadRef) {
                    if (this.$refs.imageUploadRef.$refs.imageInputRef) {
                        this.$refs.imageUploadRef.$refs.imageInputRef.value = null
                    }
                }
                this.editRewardStock = false
                this.editRewardQuestions = false
                this.editRewardImage = false
                this.editRewardAddress = false
            }
        },
        addCustomQuestion: function() {
            var index = 1
            if (this.reward.custom_questions) {
                if (this.reward.custom_questions.length > 0) {
                    index = this.reward.custom_questions.length + 1
                }
            } else if (this.customQuestions) {
                if (this.customQuestions.length > 0) {
                    index = this.customQuestions.length + 1
                }
            }
            var question = {
                key: 'custom_question_' + index,
                text: this.customQuestion
            }
            this.customQuestions.push(question)
            this.customQuestion = ''
        },
        removeCustomQuestion: function(key) {
            // find and remove the item
            for (var i = 0; i < this.customQuestions.length; i++) {
                if (this.customQuestions[i].key === key) {
                    var index = this.customQuestions.indexOf(this.customQuestions[i])
                    this.customQuestions.splice(index, 1)
                    this.debug('Removing custom question: ' + key)
                    break
                }
            }
            // loop through the items and fix the keys
            for (var j = 0; j < this.customQuestions.length; j++) {
                this.customQuestions[j].key = 'custom_question_' + (j + 1)
            }
        },
        openRewardUrl: function(url, newWindow = false) {
            if (this.campaign.stage === 2) {
                this.openUrl(url, newWindow)
            } else {
                // debug info
                this.debug('this campaign is not currently seeking funds')
            }
        },
        selectReward: function(reward, url = false) {
            if (this.isFunding) {
                if (url) {
                    this.openUrl(url)
                } else {
                    // emit event so that parent can react
                    this.$emit('reward-selected', reward)
                    // emit event so that rewards set selected
                    this.$root.eventBus.$emit('set-reward-selected', reward)
                }
            }
        },
        rewardSelected: function(reward) {
            this.$emit('reward-selected', reward)
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
        isFunding: function() {
            if (this.campaign) {
                return this.campaign.stage === 2 || this.campaign.stage === 6
            } else {
                return false
            }
        },
        isRecurring: function() {
            if (this.campaign) {
                return this.campaign.stage === 6
            } else {
                return false
            }
        },
        isRewardAmountModified: function() {
            return this.rewardAmount !== ''
        },
        isRewardImageModified: function() {
            return this.imageBody !== null
        },
        isRewardDescriptionModified: function() {
            return this.rewardDescription !== ''
        },
        isRewardTitleModified: function() {
            return this.rewardTitle !== ''
        },
        isRewardStockModified: function() {
            return this.stockLimited && this.isRewardStockNumberModified
        },
        isRewardStockNumberModified: function() {
            return this.rewardStock !== ''
        },
        isCustomQuestionModified: function() {
            return this.customQuestion !== ''
        },
        isCustomQuestionsModified: function() {
            return JSON.stringify(this.customQuestions) !== JSON.stringify([])
        },
        isCustomRewardModified: function() {
            return this.customReward !== ''
        },
        customQuestionsCount: function() {
            if (!this.askForAddress) {
                return this.customQuestions.length
            } else {
                return this.customQuestions.length - 1
            }
        }
    },
    watch: {
        campaignId: function() {
            this.reward = this.common.placeholders.reward
            this.rewards = []
            this.campaign = null
            this.acl = []
            this.setup()
        }
    },
    created: function() {
        this.$root.eventBus.$on('set-reward-selected', function(reward) {
            if (reward.reward_id === '') {
                this.selectedWithoutReward = true
            } else {
                this.selectedWithoutReward = false
            }
        }.bind(this))
        this.$root.eventBus.$on('select-reward-by-id', function(rewardId) {
            // pledge without reward
            if (rewardId === '') {
                this.selectReward({
                    amount: this.initialCustomRewardAmount,
                    reward_id: '',
                    image: null
                })
            }
        }.bind(this))
        this.$root.eventBus.$on('select-reward-by-amount', function(amount) {
            // pledge without reward
            if (amount === '') {
                this.selectReward({
                    amount: this.initialCustomRewardAmount,
                    reward_id: '',
                    image: null
                })
            }
        }.bind(this))
        this.$root.eventBus.$on('campaign-id-updated', function() {
            this.reward = this.common.placeholders.reward
            this.rewards = []
            this.campaign = null
            this.acl = []
            this.setup()
        }.bind(this))
        this.$root.eventBus.$on('login', function() {
            this.setup()
        }.bind(this))
        this.$root.eventBus.$on('logout', function() {
            this.setup()
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
