<template>

</template>

<script>
import users from '../mixins/users.js'
import experiments from '../mixins/experiments.js'
import auth from '../mixins/auth.js'
import helpers from '../mixins/helpers.js'
export default {
    name: 'profile-index',
    data() {
        return {
            profile: this.common.placeholders.profile_preview,
            profiles: [],
            searchTerm: '',
            filterOptions: [],
            tagsForExperiments: [],
            responseStatus: {},
            currentSortObject: false,
            currentSortOptionKey: false
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
        getTag: { /* Optional server-side filter. Fetch only users that contain this tag. E.g. get-tag="1" */
            required: false,
            default: false
        },
        getLimit: { /* Optional server-side filter. Fetch a limited quantity of experiments. E.g. get-limit="20" */
            required: false,
            default: false
        },
        getOffset: { /* Optional server-side filter. Fetch a experiments starting by a specified offset. E.g. get-offset="5" */
            required: false,
            default: false
        },
        sortProfilesObject: { /* Optional client-side sorting Object. If set, sorting section is displayed. Supported keys: 'oldest', 'newest'. Set key 'initial' to sort on page load. E.g. to sort by oldest on page load: :sort-profiles-object="{oldest:{name:oldest, initial:true}}" */
            required: false,
            type: Array
        },
        usersFilterArray: { /* Optional client-side array of objects. If set, filter section is displayed. Supported keys: 'tag'. Set key 'initial' to show filter on page load. E.g. :users-filter-array="[{tag:{name:tag, initial:true}}]" */
            required: false,
            type: Array
        },
        // inherit global data from parent
        common: {
            required: true,
            type: Object
        }
    },
    mixins: [
        users,
        experiments,
        auth,
        helpers
    ],
    notifications: {
        getUsersPreviewNotification: {}
    },
    methods: {
        setup: function() {
            // set up filter options
            if (this.usersFilterArray) {
                if (this.usersFilterArray.length !== 0) {
                    for (var i = 0; i < this.usersFilterArray.length; i++) {
                        var option = this.usersFilterArray[i]
                        option.selected = false
                        this.filterOptions.push(option)
                    }
                }
            }
            // set up sorting options
            if (this.sortProfilesObject) {
                if (this.sortProfilesObject.length !== 0) {
                    for (var j = 0; j < this.sortProfilesObject.length; j++) {
                        if (this.sortProfilesObject[j].initial === true) {
                            this.$set(this, 'currentSortObject', this.sortProfilesObject[j])
                            this.$set(this, 'currentSortOptionKey', this.sortProfilesObject[j].key)
                        }
                    }
                }
            }
            // build query Array
            var queryArray = []
            if (this.getTag) {
                queryArray.push({
                    tag: this.getTag
                })
            }
            if (this.getLimit) {
                queryArray.push({
                    limit: this.getLimit
                })
            }
            if (this.getOffset) {
                queryArray.push({
                    offset: this.getOffset
                })
            }
            // make API call
            this.getUsersPreview(this.apiUrl, queryArray)
            this.getTagsForExperiments(this.apiUrl, this.language)
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
        getUserName: function(profile) {
            return profile.first_name + ' ' + profile.last_name
        },
        sliceProfiles: function(profiles, start, end) {
            if (parseInt(end) !== 0) {
                // debug info
                this.debug('limiting profile quantity to: ' + end)
                this.debug(profiles)
                this.debug(profiles.slice(start, end))
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
        keywordFilter: function(profiles, searchTerm) {
            if (searchTerm !== '' && searchTerm !== ' ') {
                this.debug('serching profiles by keyword: ' + searchTerm)
                return profiles.filter(function(profiles) {
                    var firstName = profiles.first_name ? profiles.first_name : ''
                    var lastName = profiles.last_name ? profiles.last_name : ''
                    var nameText = firstName.toLowerCase() + ' ' + lastName.toLowerCase()
                    var description = profiles.short_description ? profiles.short_description : ''
                    var descriptionText = description.toLowerCase()
                    var tagText = ''
                    for (var i = 0; i < profiles.tags.length; i++) {
                        tagText += profiles.tags[i].label + ' '
                    }
                    if (nameText.indexOf(searchTerm.toLowerCase()) !== -1 || descriptionText.indexOf(searchTerm.toLowerCase()) !== -1 || tagText.indexOf(searchTerm.toLowerCase()) !== -1) {
                        return true
                    }
                })
            } else {
                return profiles
            }
        },
        searchProfiles: function() {
            this.keywordFilter(this.profiles, this.searchTerm)
        },
        filterOptionsContain: function(filterKey) {
            if (this.filterOptions) {
                if (this.filterOptions.length > 0) {
                    for (var i = 0; i < this.filterOptions.length; i++) {
                        if (this.filterOptions[i].key === filterKey) {
                            return this.filterOptions[i].selected
                        }
                    }
                }
            } else {
                return false
            }
        },
        sortProfiles: function(profiles) {
            if (this.currentSortObject) {
                if (this.currentSortObject.key === 'oldest') {
                    // debug info
                    this.debug('sorting by oldest')
                    return this.sortProfilesById(profiles, 'asc')
                } else if (this.currentSortObject.key === 'newest') {
                    // debug info
                    this.debug('sorting by newest')
                    return this.sortProfilesById(profiles, 'desc')
                } else {
                    this.debug('unsupported sorting option')
                }
            } else {
                return profiles
            }
        },
        sortProfilesById: function(profiles, direction) {
            if (direction === 'desc') {
                return profiles.sort(function(a, b) {
                    var idA = parseInt(a.user_id)
                    var idB = parseInt(b.user_id)
                    return (idA > idB) ? -1 : (idA < idB) ? 1 : 0
                })
            } else if (direction === 'asc') {
                return profiles.sort(function(a, b) {
                    var idA = parseInt(a.user_id)
                    var idB = parseInt(b.user_id)
                    return (idA < idB) ? -1 : (idA > idB) ? 1 : 0
                })
            } else {
                this.debug('unsupported sorting direction')
            }
        },
        triggerSort: function(sortOption) {
            if (this.sortProfilesObject.length !== 0) {
                for (var i = 0; i < this.sortProfilesObject.length; i++) {
                    if (sortOption.key === this.sortProfilesObject[i].key) {
                        this.$set(this, 'currentSortObject', this.sortProfilesObject[i])
                    }
                }
            }
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
        getTagLabelFromId: function() {
            for (var i = 0; i < this.tagsForExperiments.length; i++) {
                // this.debug('checking tag id:' + this.tagsForExperiments[i].id)
                if (this.tagsForExperiments[i].id === parseInt(this.getTag)) {
                    return this.tagsForExperiments[i].label
                }
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
