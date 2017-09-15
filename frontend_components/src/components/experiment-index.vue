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
            sortOptionsObject: false,
            currentSortObject: false,
            currentSortOptionKey: false,
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
        getPlace: { /* Optional server-side filter. Fetch only experiments that are show in this place. E.g. get-place="index" */
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
        sortExperimentsObject: { /* Optional client-side sorting Object. If set, sorting section is displayed. Supported keys: 'likes', 'alphabetical', 'id'. Set key 'initial to sort on page load. E.g. to sort by likes on page load: :sort-experiments-object="{likes:{name:likes,sort:true}}" */
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
        getExperimentsPreviewNotification: {}
    },
    methods: {
        setup: function() {
            // set up sorting options
            if (this.sortExperimentsObject) {
                this.sortOptionsObject = this.sortExperimentsObject
                if (this.sortOptionsObject.length !== 0) {
                    for (var i = 0; i < this.sortOptionsObject.length; i++) {
                        if (this.sortOptionsObject[i].initial === true) {
                            this.$set(this, 'currentSortObject', this.sortOptionsObject[i])
                            this.$set(this, 'currentSortOptionKey', this.sortOptionsObject[i].key)
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
        titleDescriptionFilter: function(experiments, searchTerm) {
            if (searchTerm !== '' && searchTerm !== ' ') {
                // debug info
                this.debug('serching experiments by keyword: ' + searchTerm)
                return experiments.filter(function(experiments) {
                    var titleText = experiments.title.toLowerCase()
                    var descriptionText = experiments.short_description.toLowerCase()
                    if (titleText.indexOf(searchTerm.toLowerCase()) !== -1 || descriptionText.indexOf(searchTerm.toLowerCase()) !== -1) {
                        return true
                    }
                })
            } else {
                return experiments
            }
        },
        searchExperiments: function() {
            this.titleDescriptionFilter(this.experiments, this.searchTerm)
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
