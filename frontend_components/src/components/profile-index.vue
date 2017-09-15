<template>

</template>

<script>
import users from '../mixins/users.js'
import auth from '../mixins/auth.js'
import helpers from '../mixins/helpers.js'
export default {
    name: 'profile-index',
    data() {
        return {
            profile: this.common.placeholders.profile_preview,
            profiles: [],
            searchTerm: '',
            responseStatus: {}
        }
    },
    props: {
        apiUrl: {
            required: true,
            type: String
        },
        placeholderImageUrl: {
            required: false,
            default: false
        },
        limit: {
            required: false /* Optional. Number between 0 and n, 0 means no limit. E.g. limit="12" */
        },
        userIds: {
            required: false /* Optional. List of user ids or 'all'. E.g. user-ids="5, 22, 155" */
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
        getUsersPreviewNotification: {}
    },
    methods: {
        setup: function() {
            // make API call
            this.getUsersPreview(this.apiUrl)
        },
        getUsersPreview: function(apiUrl, language) {
            this.getUsersPreviewMixin(apiUrl, language).then((response) => {
                // debug info
                this.debug('getUsersPreviewMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'getUsersPreview')
                if (response.hasOwnProperty('data')) {
                    if (response.data.hasOwnProperty('previews')) {
                        this.profiles = response.data.previews
                    }
                }
            }, (error) => {
                // debug info
                this.debug('getUsersPreviewMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'getUsersPreview')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.getUsersPreviewNotification({
                    message: message,
                    timeout: 5000,
                    type: 'error'
                })
            })
        },
        getUserName: function(profile) {
            return profile.first_name + ' ' + profile.last_name
        },
        sliceProfiles: function(profiles, start, end) {
            if (end !== 0) {
                // debug info
                this.debug('limiting profile quantity to: ' + end)
                return profiles.slice(start, end)
            } else {
                return profiles.slice(start)
            }
        },
        filterById: function(profiles, userIds) {
            if (userIds) {
                var userIdsNumbers = userIds.split(',')
                return profiles.filter(function(profiles) {
                    if (userIds === 'all') {
                        return true
                    } else {
                        // debug info
                        this.debug('limiting profile IDs to: ' + userIds)
                        for (var i = 0; i < userIdsNumbers.length; i++) {
                            if (userIdsNumbers[i].trim() === profiles.user_id) {
                                return true
                            }
                        }
                    }
                })
            } else {
                return profiles
            }
        },
        nameDescriptionFilter: function(profiles, searchTerm) {
            if (searchTerm !== '' && searchTerm !== ' ') {
                this.debug('serching profiles by keyword: ' + searchTerm)
                return profiles.filter(function(profiles) {
                    var firstName = profiles.first_name ? profiles.first_name : ''
                    var lastName = profiles.last_name ? profiles.last_name : ''
                    var nameText = firstName.toLowerCase() + ' ' + lastName.toLowerCase()
                    var description = profiles.short_description ? profiles.short_description : ''
                    var descriptionText = description.toLowerCase()
                    if (nameText.indexOf(searchTerm.toLowerCase()) !== -1 || descriptionText.indexOf(searchTerm.toLowerCase()) !== -1) {
                        return true
                    }
                })
            } else {
                return profiles
            }
        },
        searchProfiles: function() {
            this.nameDescriptionFilter(this.profiles, this.searchTerm)
        }
    },
    computed: {
        placeholderImage: function() {
            if (this.placeholderImageUrl) {
                return this.placeholderImageUrl
            } else {
                return this.common.placeholders.defaultPlaceholderImageUrl
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
