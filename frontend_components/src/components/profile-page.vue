<template>

</template>

<script>
import experiments from '../mixins/experiments.js'
import files from '../mixins/files.js'
import users from '../mixins/users.js'
import auth from '../mixins/auth.js'
import helpers from '../mixins/helpers.js'
export default {
    name: 'profile-page',
    data() {
        return {
            profile: this.common.placeholders.profile,
            profileFirstName: '',
            profileLastName: '',
            profileShortDescription: '',
            profileLinks: [],
            showPasswordEditor: false,
            showProfileEditor: false,
            showLinksEditor: false,
            showSkillsEditor: false,
            imageBody: null,
            showSpindle: false,
            imageLoaded: false,
            credentials: {
                newPassword: '',
                oldPassword: ''
            },
            retypePassword: '',
            showProfileErrors: false,
            showLinksErrors: false,
            showPasswordErrors: false,
            responseStatus: {},
            tagsForExperiments: [],
            newSkill: '',
            selectedSkills: []
        }
    },
    props: {
        apiUrl: {
            required: true,
            type: String
        },
        userId: {
            required: true,
            type: String
        },
        language: {
            required: true,
            type: String
        },
        profileProp: {
            required: false,
            default: false
        },
        placeholderImageUrl: {
            required: false,
            default: false
        },
        logoutRedirectPath: {
            required: false,
            default: false
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
        users,
        auth,
        helpers
    ],
    notifications: {
        getProfileNotification: {},
        updateProfileNotification: {},
        updateProfileImageNotification: {},
        updatePasswordNotification: {},
        setUserLinksNotification: {},
        setTagsForUserNotification: {},
        setSkillsForUserNotification: {}
    },
    methods: {
        setup: function() {
            // set validator locale
            // this.setValidatorLocale(this.$validator, this.language)
            if (this.profileProp) {
                // debug info
                this.debug('profile-page using the profile prop')
                // TODO: figure out why this is not working/reactive. in the meantime, do not use prop option
                this.profile = this.profileProp
                // set values to populate form
                this.profileFirstName = this.profileProp.first_name
                this.profileLastName = this.profileProp.last_name
                this.profileShortDescription = this.profileProp.short_description
                this.profileLinks = JSON.parse(JSON.stringify(this.profile.links))
            } else if (this.userId) {
                // debug info
                this.debug('profile-page fetching its own profile')
                // make API call
                this.getProfile(this.apiUrl, this.userId)
            } else {
                // debug info
                this.debug('need to provide user id or profile prop')
            }
        },
        getProfile: function(apiUrl, userId) {
            if (userId === 'me') {
                this.getMyProfile(apiUrl)
            } else {
                var authHeader = this.getAuthorizationHeaderMixin()
                this.getProfileMixin(apiUrl, userId, authHeader).then((response) => {
                    // debug info
                    this.debug('getProfileMixin response: ')
                    this.debug(this.getResponseData(response))
                    // success callback
                    this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'getProfile')
                    if (response.hasOwnProperty('data')) {
                        if (response.data.hasOwnProperty('profile')) {
                            this.$set(this, 'profile', response.data.profile)
                            this.selectedSkills = this.profile.skills.slice()
                            if (response.data.profile.hasOwnProperty('experiments')) {
                                this.sortExperiments()
                            }
                        }
                        if (response.data.hasOwnProperty('acl')) {
                            this.$set(this, 'acl', response.data.acl)
                        }
                    }
                    // set values to populate form
                    this.profileFirstName = this.profile.first_name
                    this.profileLastName = this.profile.last_name
                    this.profileShortDescription = this.profile.short_description
                    this.profileLinks = JSON.parse(JSON.stringify(this.profile.links))
                    // get the available tags
                    if (this.isEditable) {
                        this.getTagsForExperiments(apiUrl, this.language)
                    }
                    // this.setUserAcl()
                }, (error) => {
                    // debug info
                    this.debug('getProfileMixin error: ')
                    this.debug(this.getResponseMessage(error.response))
                    // error callback
                    this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'getProfile')
                    // show error message
                    var message = this.getErrorMessage(this, error.response)
                    this.getProfileNotification({
                        message: message,
                        timeout: 5000,
                        type: 'error'
                    })
                })
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
                        this.$set(this, 'profile', response.data.profile)
                        this.selectedSkills = this.profile.skills.slice()
                        if (response.data.profile.hasOwnProperty('experiments')) {
                            this.sortExperiments()
                        }
                    }
                    if (response.data.hasOwnProperty('acl')) {
                        this.$set(this, 'acl', response.data.acl)
                    }
                }
                // set values to populate form
                this.profileFirstName = this.profile.first_name
                this.profileLastName = this.profile.last_name
                this.profileShortDescription = this.profile.short_description
                this.profileLinks = JSON.parse(JSON.stringify(this.profile.links))
                // get the available tags
                if (this.isEditable) {
                    this.getTagsForExperiments(apiUrl, this.language)
                }
                // this.setUserAcl()
            }, (error) => {
                // debug info
                this.debug('getMyProfileMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'getMyProfile')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.getProfileNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        updateProfile: function(apiUrl) {
            this.$validator.validateAll('update-profile-form').then(success => {
                if (success) {
                    // debug info
                    this.debug('profile form is valid')
                    // set client-side values
                    var profileBody = {}
                    if (this.isProfileModified) {
                        if (this.profileFirstName !== this.profile.first_name) {
                            profileBody.first_name = this.profileFirstName
                            this.profile.first_name = this.profileFirstName
                        }
                        if (this.profileLastName !== this.profile.last_name) {
                            profileBody.last_name = this.profileLastName
                            this.profile.last_name = this.profileLastName
                        }
                        if (this.profileShortDescription !== this.profile.short_description) {
                            profileBody.short_description = this.profileShortDescription
                            this.profile.short_description = this.profileShortDescription
                        }
                        // make API calls
                        if (Object.keys(profileBody).length > 0) {
                            this.updateMyProfile(apiUrl, profileBody)
                        }
                        if (this.imageBody) {
                            this.updateProfileImage()
                        }
                        // collapse editing section
                        this.showProfileEditor = false
                    } else {
                        // debug info
                        this.debug('profile form not modified')
                    }
                } else {
                    this.showProfileErrors = true
                    // debug info
                    this.debug('profile form has errors')
                }
            })
        },
        updateMyProfile: function(apiUrl, profileBody) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.updateMyProfileMixin(apiUrl, profileBody, authHeader).then((response) => {
                // debug info
                this.debug('updateMyProfileMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'updateMyProfile')
                // dispatch event so that authorization can process the change
                this.$root.eventBus.$emit('doProfile')
                // update client-side
                for (var key in profileBody) {
                    this.$set(this.profile, key, profileBody[key])
                }
            }, (error) => {
                // debug info
                this.debug('updateMyProfileMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'updateMyProfile')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.updateProfileNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        updateProfileImage: function() {
            var message = this.getErrorMessage(this, {
                data: {
                    status: 'saving_image',
                    message: 'Saving image.'
                }
            })
            this.updateProfileImageNotification({
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
                            var profileImageCollectionBody = {
                                image_collection: {
                                    profile: {
                                        filename: response.data.image.filename
                                    }
                                }
                            }
                        }
                    }
                }
                this.imageBody = null
                this.updateMyProfileImageCollection(this.apiUrl, profileImageCollectionBody)
                // show success message
                var message = this.getErrorMessage(this, {
                    data: {
                        status: 'image_saved',
                        message: 'Image saved.'
                    }
                })
                this.updateProfileImageNotification({
                    message: message,
                    timeout: 5000,
                    type: 'success'
                })
                // dispatch event so that authorization can process the change
                this.$root.eventBus.$emit('doProfile')
            }, (error) => {
                // debug info
                this.debug('uploadImageFileMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'uploadImageFile')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.updateProfileImageNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        updateMyProfileImageCollection: function(apiUrl, profileImageCollectionBody) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.updateMyProfileImageCollectionMixin(apiUrl, profileImageCollectionBody, authHeader).then((response) => {
                // debug info
                this.debug('updateMyProfileImageCollectionMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'updateMyProfileImageCollection')
                // TODO: Maybe update client side object? problem is URL is generated server-side..
                // remove current image
                // TODO: improve this to be dynamic, not all-removing
                this.profile.image_collection = {
                    profile: {
                        url: ''
                    }
                }
                this.showSpindle = true
                // get the profile again to retrieve new image url
                this.getMyProfile(apiUrl)
            }, (error) => {
                // debug info
                this.debug('updateMyProfileImageCollectionMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'updateMyProfileImageCollection')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.updateProfileImageNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        updateMyPassword: function(apiUrl) {
            this.$validator.validateAll('update-password-form').then(success => {
                if (success) {
                    if (this.credentials.newPassword !== this.credentials.oldPassword) {
                        // debug info
                        this.debug('update password form is valid')
                        // collapse editor
                        this.showPasswordEditor = false
                        var profileBody = {
                            new_password: this.credentials.newPassword,
                            old_password: this.credentials.oldPassword
                        }
                        this.changeMyPassword(profileBody)
                    } else {
                        // debug info
                        this.debug('old password is the same as new password')
                    }
                } else {
                    this.showPasswordErrors = true
                    // debug info
                    this.debug('update password form has errors')
                }
            })
        },
        changeMyPassword: function(postBody) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.changeMyPasswordMixin(this.apiUrl, postBody, authHeader).then((response) => {
                // debug info
                this.debug('changeMyPasswordMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'changeMyPassword')
                // show success message
                var message = this.getErrorMessage(this, {
                    data: {
                        status: 'password_updated',
                        message: 'Password updated.'
                    }
                })
                this.updateProfileImageNotification({
                    message: message,
                    timeout: 5000,
                    type: 'success'
                })
            }, (error) => {
                // debug info
                this.debug('changeMyPasswordMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'changeMyPassword')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.updateProfileImageNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        setUserLinks: function(apiUrl, userLinksBody) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.setUserLinksMixin(apiUrl, userLinksBody, authHeader).then((response) => {
                // debug info
                this.debug('setUserLinksMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'setUserLinks')
                // set client side
                this.profileLinks = JSON.parse(JSON.stringify(userLinksBody))
                // get the profile again to retrieve the links
                this.getMyProfile(apiUrl)
            }, (error) => {
                // debug info
                this.debug('setUserLinksMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'setUserLinks')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.setUserLinksNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        updateProfileLinks: function(apiUrl) {
            this.$validator.validateAll('update-profile-links-form').then(success => {
                if (success) {
                    // debug info
                    this.debug('profile links form is valid')
                    // set client-side values
                    var userLinksBody = {}
                    if (this.areLinksModified) {
                        var links = []
                        for (var i = 0; i < this.profileLinks.length; i++) {
                            if (this.profileLinks[i].url !== '' || this.profileLinks[i].title !== '') {
                                var link = {
                                    url: this.profileLinks[i].url,
                                    title: this.profileLinks[i].title
                                }
                                links.push(link)
                            }
                        }
                        userLinksBody.links = links
                        // perform api request
                        this.setUserLinks(apiUrl, userLinksBody)
                        // collapse editing section
                        this.showLinksEditor = false
                    } else {
                        // debug info
                        this.debug('profile links form not modified')
                    }
                } else {
                    this.showLinksErrors = true
                    // debug info
                    this.debug('profile links form has errors')
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
        setTagsForUser: function(apiUrl, userTagsBody) {
            var authHeader = this.getAuthorizationHeaderMixin()
            this.setTagsForUserMixin(apiUrl, userTagsBody, authHeader).then((response) => {
                // debug info
                this.debug('setTagsForUserMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'setTagsForUser')
            }, (error) => {
                // debug info
                this.debug('setTagsForUserMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'setTagsForUser')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.setTagsForUserNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        setSkillsForUser: function(apiUrl) {
            var authHeader = this.getAuthorizationHeaderMixin()
            // set client side
            this.profile.skills = this.selectedSkills.slice()
            // send api request
            var userSkillsBody = {
                skills: this.selectedSkills
            }
            this.setSkillsForUserMixin(apiUrl, userSkillsBody, authHeader).then((response) => {
                // debug info
                this.debug('setSkillsForUserMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'setSkillsForUser')
                this.showSkillsEditor = false
            }, (error) => {
                // debug info
                this.debug('setSkillsForUserMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'setSkillsForUser')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.setSkillsForUserNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        userTagsAdded: function(tags, tagsIds) {
            // debug info
            this.debug('adding tags to user')
            this.debug(tags)
            this.debug(tagsIds)
            // set client-side object
            this.profile.tags = []
            for (var i = 0; i < tags.length; i++) {
                this.profile.tags.push(tags[i])
            }
            var userTagsBody = {
                tags: tagsIds
            }
            // make API calls
            this.setTagsForUser(this.apiUrl, userTagsBody)
        },
        onImageLoad: function() {
            // debug info
            this.debug('image loaded')
            this.showSpindle = false
            this.imageLoaded = true
        },
        setImage: function(imageData) {
            this.imageBody = imageData
        },
        sortExperiments: function() {
            this.profile.experiments.sort(function(a, b) {
                if (a.stage - b.stage === 0) {
                    return a.experiment_id - b.experiment_id
                } else {
                    return a.stage - b.stage
                }
            })
        },
        initializeProfileEditor: function() {
            if (this.showProfileEditor === false) {
                this.showProfileEditor = true
                this.$nextTick(() => {
                    if (this.$refs.profileInputRef) {
                        this.$refs.profileInputRef.focus()
                    }
                })
            }
        },
        resetProfileEditor: function() {
            if (this.showProfileEditor === true) {
                this.showProfileEditor = false
                if (this.isProfileModified) {
                    // set to original value
                    this.profileFirstName = this.profile.first_name
                    this.profileLastName = this.profile.last_name
                    this.profileShortDescription = this.profile.short_description
                    this.imageBody = null
                    if (this.$refs.imageUploadRef) {
                        if (this.$refs.imageUploadRef.$refs.imageInputRef) {
                            this.$refs.imageUploadRef.$refs.imageInputRef.value = null
                        }
                    }
                }
            }
        },
        initializeLinksEditor: function() {
            if (this.showLinksEditor === false) {
                this.showLinksEditor = true
            }
        },
        resetLinksEditor: function() {
            if (this.showLinksEditor === true) {
                this.showLinksEditor = false
                if (this.areLinksModified) {
                    this.profileLinks = JSON.parse(JSON.stringify(this.profile.links))
                }
            }
        },
        initializeSkillsEditor: function() {
            if (this.showSkillsEditor === false) {
                this.showSkillsEditor = true
            }
        },
        resetSkillsEditor: function() {
            if (this.showSkillsEditor === true) {
                this.showSkillsEditor = false
                if (this.areSkillsModified) {
                    this.selectedSkills = this.profile.skills.slice()
                }
            }
        },
        initializePasswordEditor: function() {
            if (this.showPasswordEditor === false) {
                this.showPasswordEditor = true
                this.$nextTick(() => {
                    if (this.$refs.passwordInputRef) {
                        this.$refs.passwordInputRef.focus()
                    }
                })
            }
        },
        resetPasswordEditor: function() {
            if (this.showPasswordEditor === true) {
                this.showPasswordEditor = false
                if (this.isPasswordModified) {
                    // set to original value
                    this.credentials.oldPassword = ''
                    this.credentials.newPassword = ''
                    this.retypePassword = ''
                }
                this.errors.clear()
            }
        },
        getLanguage: function(key) {
            if (this.profile.hasOwnProperty(key)) {
                return this.profile[key]
            } else {
                return null
            }
        },
        profileLanguageUpdated: function(profileLanguageBody, customLanguage = false, isSirTrevor = false) {
            // debug info
            this.debug('profileLanguageUpdated')
            // set the values client-side
            for (var key in profileLanguageBody) {
                this.$set(this.profile, key, profileLanguageBody[key])
            }
            this.updateMyProfile(this.apiUrl, profileLanguageBody)
        },
        addLinkField: function() {
            var link = {
                url: '',
                title: ''
            }
            this.profileLinks.push(link)
        },
        removeLinkField: function(index) {
            this.profileLinks.splice(index, 1)
        },
        addNewSkill: function() {
            if (this.isNewSkillModified) {
                this.selectedSkills.push(this.newSkill)
                this.newSkill = ''
            }
        },
        removeSkill: function(index) {
            this.selectedSkills.splice(index, 1)
        },
        updateProfileEditable: function(postBody, type) {
            // debug info
            // this.debug(postBody)
            if (type === 'language') {
                this.updateMyProfile(this.apiUrl, postBody)
            } else if (type === 'password') {
                let passwordBody = {
                    new_password: postBody.credentials.newPassword
                }
                this.debug(passwordBody)
                this.changeMyPassword(passwordBody)
            } else {
                // debug info
                this.debug('profile editable type "' + type + '" not implemented')
            }
        }
    },
    computed: {
        userFullName: function() {
            return this.profile.first_name + ' ' + this.profile.last_name
        },
        placeholderImage: function() {
            if (this.placeholderImageUrl) {
                return this.placeholderImageUrl
            } else {
                return this.common.placeholders.defaultPlaceholderImageUrl
            }
        },
        isEditable: function() {
            var currentUserObject = this.getLocalStorageObjectMixin('userData')
            if (currentUserObject) {
                if (currentUserObject.hasOwnProperty('user_id')) {
                    return (this.isUserAuthenticated && this.userId === 'me') || (this.isUserAuthenticated && parseInt(this.userId) === currentUserObject.user_id)
                }
            }
        },
        isProfileModified: function() {
            if (this.profileFirstName !== this.profile.first_name || this.profileLastName !== this.profile.last_name || this.profileShortDescription !== this.profile.short_description || this.imageBody) {
                return true
            } else {
                return false
            }
        },
        areLinksModified: function() {
            if (this.profile.hasOwnProperty('links') && this.profile.links) {
                if (this.profile.links.length !== 0) {
                    return JSON.stringify(this.profile.links) !== JSON.stringify(this.profileLinks)
                }
            }
            if (this.profileLinks.length !== 0) {
                for (var i = 0; i < this.profileLinks.length; i++) {
                    if (this.profileLinks[i].url !== '' || this.profileLinks[i].title !== '') {
                        return true
                    }
                }
            }
            return false
        },
        isPasswordModified: function() {
            if (this.credentials.oldPassword !== '' && this.credentials.newPassword !== '' && this.retypePassword !== '') {
                return true
            } else {
                return false
            }
        },
        isNewSkillModified: function() {
            return this.newSkill !== ''
        },
        areSkillsModified: function() {
            return JSON.stringify(this.selectedSkills) !== JSON.stringify(this.profile.skills)
        }
    },
    created: function() {
        this.$root.eventBus.$on('login', function() {
            this.setup()
        }.bind(this))
        this.$root.eventBus.$on('logout', function() {
            if (this.logoutRedirectPath && this.userId === 'me') {
                this.openUrl(this.logoutRedirectPath + this.profile.user_id + '/')
            }
            this.setup()
        }.bind(this))
        this.$root.eventBus.$on('profile', function() {
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
