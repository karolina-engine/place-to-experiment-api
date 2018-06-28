<template>

</template>

<script>
import experiments from '../mixins/experiments.js'
import auth from '../mixins/auth.js'
import helpers from '../mixins/helpers.js'
export default {
    name: 'experiment-index',
    data() {
        return {
            experiment: this.common.placeholders.experiment_preview,
            experiments: [],
            searchTerm: '',
            filterOptions: [],
            sortOptionsObject: false,
            currentSortObject: false,
            currentSortOptionKey: false,
            tagsForExperiments: [],
            responseStatus: {}
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
            default: false,
            type: String
        },
        getTeamMemberId: { /* Optional server-side filter. Fetch only experiments that have a team member with this user ID. E.g. get-team-member-id="15" */
            required: false,
            default: false
        },
        getPlace: { /* Optional server-side filter. Fetch only experiments that are set to show in this place. E.g. get-place="index" */
            required: false,
            default: false
        },
        getTag: { /* Optional server-side filter. Fetch only experiments that contain this tag. E.g. get-tag="1" */
            required: false,
            default: false
        },
        getStage: { /* Optional server-side filter. Fetch only experiments that are in this stage. E.g. get-stage="3" */
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
        showLimit: { /* Optional client-side filter. Number between 0 and n, 0 means no limit. E.g. showLimit="12" */
            required: false,
            default: false
        },
        showStages: { /* Optional client-side filter. List of numbers between 0 and 5 or 'all'. E.g. showStages="1, 4, 5" */
            required: false,
            default: false
        },
        showExperimentIds: { /* Optional client-side filter. List of experiment ids or 'all'. E.g. show-experiment-ids="5, 22, 155" */
            required: false,
            default: false
        },
        sortExperimentsObject: { /* Optional client-side sorting Object. If set, sorting section is displayed. Supported keys: 'likes', 'alphabetical', 'id', 'tag'. Set key 'initial' to sort on page load. E.g. to sort by likes on page load: :sort-experiments-object="{likes:{name:likes, initial:true}}" */
            required: false,
            type: Array
        },
        experimentsFilterArray: { /* Optional client-side array of objects. If set, filter section is displayed. Supported keys: 'tag'. Set key 'initial' to show filter on page load. E.g. :experiments-filter-array="[{tag:{name:tag, initial:true}}]" */
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
        experiments,
        auth,
        helpers
    ],
    notifications: {
        getExperimentsPreviewNotification: {},
        getTagsForExperimentsNotification: {}
    },
    methods: {
        setup: function() {
            // set up filter options
            if (this.experimentsFilterArray) {
                if (this.experimentsFilterArray.length !== 0) {
                    for (var i = 0; i < this.experimentsFilterArray.length; i++) {
                        var option = this.experimentsFilterArray[i]
                        option.selected = false
                        this.filterOptions.push(option)
                    }
                }
            }
            // set up sorting options
            if (this.sortExperimentsObject) {
                this.sortOptionsObject = this.sortExperimentsObject
                if (this.sortOptionsObject.length !== 0) {
                    for (var j = 0; j < this.sortOptionsObject.length; j++) {
                        if (this.sortOptionsObject[j].initial === true) {
                            this.$set(this, 'currentSortObject', this.sortOptionsObject[j])
                            this.$set(this, 'currentSortOptionKey', this.sortOptionsObject[j].key)
                        }
                    }
                }
            }
            // build query Array
            var queryArray = []
            if (this.getTeamMemberId) {
                queryArray.push({
                    team_member_id: this.getTeamMemberId
                })
            }
            if (this.getPlace) {
                queryArray.push({
                    place: this.getPlace
                })
            }
            if (this.getTag) {
                queryArray.push({
                    tag: this.getTag
                })
            }
            if (this.getStage) {
                queryArray.push({
                    stage: this.getStage
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
            this.getExperimentsPreview(this.apiUrl, this.language, queryArray)
            this.getTagsForExperiments(this.apiUrl, this.language)
        },
        getExperimentsPreview: function(apiUrl, language, query) {
            this.getExperimentsPreviewMixin(apiUrl, language, query).then((response) => {
                // debug info
                this.debug('getExperimentsPreviewMixin response: ')
                this.debug(this.getResponseData(response))
                // success callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, response, 'getExperimentsPreview')
                if (response.hasOwnProperty('data')) {
                    if (response.data.hasOwnProperty('previews')) {
                        this.experiments = response.data.previews
                    }
                }
            }, (error) => {
                // debug info
                this.debug('getExperimentsPreviewMixin error: ')
                this.debug(this.getResponseMessage(error.response))
                // error callback
                this.responseStatus = this.setResponseStatus(this.responseStatus, error.response, 'getExperimentsPreview')
                // show error message
                var message = this.getErrorMessage(this, error.response)
                this.getExperimentsPreviewNotification({
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
        sliceExperiments: function(experiments, start, end) {
            if (parseInt(end) !== 0) {
                // debug info
                this.debug('filtering experiment quantity to: ' + end)
                return experiments.slice(start, end)
            } else {
                return experiments.slice(start)
            }
        },
        filterByStage: function(experiments, stages) {
            if (stages) {
                var stageNumbers = stages.split(',')
                // debug info
                this.debug('filtering experiment stages to: ' + stages)
                return experiments.filter(function(experiments) {
                    if (stages === 'all') {
                        return true
                    } else {
                        for (var i = 0; i < stageNumbers.length; i++) {
                            if (parseInt(stageNumbers[i].trim()) === experiments.stage) {
                                return true
                            }
                        }
                    }
                })
            } else {
                return experiments
            }
        },
        filterById: function(experiments, experimentIds) {
            if (experimentIds) {
                var experimentIdsNumbers = experimentIds.split(',')
                // debug info
                this.debug('filtering experiment IDs to: ' + experimentIds)
                return experiments.filter(function(experiments) {
                    if (experimentIds === 'all') {
                        return true
                    } else {
                        for (var i = 0; i < experimentIdsNumbers.length; i++) {
                            if (experimentIdsNumbers[i].trim() === experiments.experiment_id) {
                                return true
                            }
                        }
                    }
                })
            } else {
                return experiments
            }
        },
        keywordFilter: function(experiments, searchTerm) {
            if (searchTerm !== '' && searchTerm !== ' ') {
                // debug info
                this.debug('serching experiments by keyword: ' + searchTerm)
                return experiments.filter(function(experiments) {
                    var titleText = experiments.title.toLowerCase()
                    var descriptionText = experiments.short_description.toLowerCase()
                    var tagText = ''
                    for (var i = 0; i < experiments.tags.length; i++) {
                        tagText += experiments.tags[i].label + ' '
                    }
                    if (titleText.indexOf(searchTerm.toLowerCase()) !== -1 || descriptionText.indexOf(searchTerm.toLowerCase()) !== -1 || tagText.indexOf(searchTerm.toLowerCase()) !== -1) {
                        return true
                    }
                })
            } else {
                return experiments
            }
        },
        searchExperiments: function() {
            this.keywordFilter(this.experiments, this.searchTerm)
        },
        sortExperiments: function(experiments) {
            if (this.currentSortObject) {
                if (this.currentSortObject.key === 'likes') {
                    // debug info
                    this.debug('sorting by likes')
                    return this.sortExperimentsByLikes(experiments)
                } else if (this.currentSortObject.key === 'alphabetical') {
                    // debug info
                    this.debug('sorting by title')
                    return this.sortExperimentsByTitle(experiments)
                } else if (this.currentSortObject.key === 'id') {
                    // debug info
                    this.debug('sorting by ID')
                    return this.sortExperimentsById(experiments)
                } else {
                    this.debug('unsupported sorting option')
                }
            } else {
                return experiments
            }
        },
        sortExperimentsByLikes: function(experiments) {
            return experiments.sort(function(a, b) {
                var likeCountA = a.like_count
                var likeCountB = b.like_count
                return (likeCountA > likeCountB) ? -1 : (likeCountA < likeCountB) ? 1 : 0
            })
        },
        sortExperimentsByTitle: function(experiments) {
            return experiments.sort(function(a, b) {
                var titleA = a.title
                var titleB = b.title
                return (titleA < titleB) ? -1 : (titleA > titleB) ? 1 : 0
            })
        },
        sortExperimentsById: function(experiments) {
            return experiments.sort(function(a, b) {
                var idA = parseInt(a.experiment_id)
                var idB = parseInt(b.experiment_id)
                return (idA > idB) ? -1 : (idA < idB) ? 1 : 0
            })
        },
        triggerSort: function(sortOption) {
            if (this.sortOptionsObject.length !== 0) {
                for (var i = 0; i < this.sortOptionsObject.length; i++) {
                    if (sortOption.key === this.sortOptionsObject[i].key) {
                        this.$set(this, 'currentSortObject', this.sortOptionsObject[i])
                    }
                }
            }
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
        sortOptions: function() {
            return this.sortOptionsObject
        },
        getTagLabelFromId: function() {
            for (var i = 0; i < this.tagsForExperiments.length; i++) {
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
