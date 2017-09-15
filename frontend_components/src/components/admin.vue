<template>

</template>

<script>
import experiments from '../mixins/experiments.js'
import auth from '../mixins/auth.js'
import helpers from '../mixins/helpers.js'
export default {
    name: 'admin',
    data() {
        return {
            responseStatus: {},
            experiment: this.common.placeholders.experiment_preview,
            experiments: [],
            currentData: [],
            currentPage: [],
            itemsPerPageDefault: 10,
            selectedItemsPerPage: '',
            offset: 0,
            pagination: {
                total: 0,
                current_page: 1,
                last_page: 1,
                from: 0,
                to: 0
            },
            searchTerm: '',
            sortField: {
                name: 'id',
                direction: 'desc',
                sortable: true
            },
            fields: [{
                text: 'ID',
                name: 'id',
                sortable: true
            }, {
                text: 'Title',
                name: 'title',
                sortable: true
            }, {
                text: 'Short description',
                name: 'short_description',
                sortable: true
            }, {
                text: 'Owner name',
                name: 'owner_name',
                sortable: true
            }, {
                text: 'Show in',
                name: 'show_in',
                sortable: true
            }, {
                text: 'Link',
                name: 'link',
                sortable: false
            }]
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
        itemsPerPage: {
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
        auth,
        helpers
    ],
    notifications: {
        getExperimentsPreviewNotification: {}
    },
    methods: {
        setup: function() {
            if (this.isUserAuthenticatedMixin()) {
                // take per page from prop
                if (this.itemsPerPage) {
                    this.itemsPerPageDefault = this.itemsPerPage
                }
                this.selectedItemsPerPage = this.itemsPerPageDefault
                // make API call
                this.getExperimentsPreview(this.apiUrl, this.language)
            }
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
                        this.currentData = response.data.previews
                        // initial sorting
                        this.sortData(this.sortField, true)
                        // pagination
                        this.doPagination(1)
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
        sortData: function(field, refresh = false) {
            if (field.sortable) {
                if (!refresh) {
                    if (field.name === this.sortField.name) {
                        this.sortField.direction = this.sortField.direction === 'asc' ? 'desc' : 'asc'
                    } else {
                        this.sortField.direction = 'asc'
                    }
                    this.sortField.name = field.name
                }
                if (field.name === 'id') {
                    this.sortExperimentsById(this.currentData, this.sortField.direction)
                } else if (field.name === 'title' || field.name === 'short_description' || field.name === 'owner_name') {
                    this.sortExperimentsByText(field, this.currentData, this.sortField.direction)
                } else if (field.name === 'show_in') {
                    this.sortExperimentsByObjectLength(this.currentData, this.sortField.direction)
                }
                this.doPagination(this.pagination.current_page)
            } else {
                // debug info
                this.debug('non sortable field')
            }
        },
        sortExperimentsById: function(experiments, order) {
            return experiments.sort(function(a, b) {
                var idA = parseInt(a.experiment_id)
                var idB = parseInt(b.experiment_id)
                if (order === 'desc') {
                    return (idA > idB) ? -1 : (idA < idB) ? 1 : 0
                } else if (order === 'asc') {
                    return (idA < idB) ? -1 : (idA > idB) ? 1 : 0
                } else {
                    // debug info
                    this.debug('invalid sort order')
                }
            })
        },
        sortExperimentsByText: function(field, experiments, order) {
            return experiments.sort(function(a, b) {
                var textA = ''
                var textB = ''
                if (field.name === 'title') {
                    textA = a.title
                    textB = b.title
                } else if (field.name === 'short_description') {
                    textA = a.short_description
                    textB = b.short_description
                } else if (field.name === 'owner_name') {
                    textA = a.owner_name
                    textB = b.owner_name
                }
                if (order === 'desc') {
                    return (textA > textB) ? -1 : (textA < textB) ? 1 : 0
                } else if (order === 'asc') {
                    return (textA < textB) ? -1 : (textA > textB) ? 1 : 0
                } else {
                    // debug info
                    this.debug('invalid sort order')
                }
            })
        },
        sortExperimentsByObjectLength: function(experiments, order) {
            return experiments.sort(function(a, b) {
                var lengthA = Object.keys(a.show_in).length
                var lengthB = Object.keys(b.show_in).length
                if (order === 'desc') {
                    return (lengthA > lengthB) ? -1 : (lengthA < lengthB) ? 1 : 0
                } else if (order === 'asc') {
                    return (lengthA < lengthB) ? -1 : (lengthA > lengthB) ? 1 : 0
                } else {
                    // debug info
                    this.debug('invalid sort order')
                }
            })
        },
        titleDescriptionOwnerFilter: function(experiments, searchTerm) {
            if (searchTerm !== '' && searchTerm !== ' ') {
                this.debug('serching experiments by keyword: ' + searchTerm)
                return experiments.filter(function(experiments) {
                    var titleText = experiments.title.toLowerCase()
                    var descriptionText = experiments.short_description.toLowerCase()
                    var ownerText = experiments.owner_name.toLowerCase()
                    if (titleText.indexOf(searchTerm.toLowerCase()) !== -1 || descriptionText.indexOf(searchTerm.toLowerCase()) !== -1 || ownerText.indexOf(searchTerm.toLowerCase()) !== -1) {
                        return true
                    }
                })
            } else {
                return experiments
            }
        },
        searchExperiments: function() {
            this.currentData = this.titleDescriptionOwnerFilter(this.experiments, this.searchTerm)
            // sort data in case the order breaks when showing/hiding items
            this.sortData(this.sortField, true)
            // pagination
            this.doPagination(1)
        },
        doPagination: function(page) {
            if (page > 0 && page < this.pagination.last_page + 1) {
                this.offset = (page - 1) * this.perPage
                this.pagination.total = this.currentData.length
                this.pagination.current_page = page
                this.pagination.last_page = this.getNumberOfPages(this.currentData.length, this.perPage)
                this.pagination.from = this.offset + 1
                if (this.offset + this.perPage < this.currentData.length) {
                    this.pagination.to = this.offset + this.perPage
                } else {
                    this.pagination.to = this.currentData.length
                }
                this.currentPage = this.sliceExperiments(this.currentData, this.offset, this.pagination.to)
            } else {
                // debug info
                this.debug('page out of range')
            }
        },
        getNumberOfPages: function(total, perPage) {
            if (total > 0) {
                return Math.ceil(total / perPage)
            } else {
                return 1
            }
        },
        sliceExperiments: function(experiments, start, end) {
            if (end !== 0) {
                return experiments.slice(start, end)
            } else {
                return experiments.slice(start)
            }
        },
        getObjectKeys: function(obj) {
            if (obj) {
                return Object.keys(obj)
            }
        }
    },
    computed: {
        perPage: function() {
            return this.itemsPerPageDefault
        }
    },
    created: function() {
        this.$root.eventBus.$on('login', function() {
            this.setup()
        }.bind(this))
        this.$root.eventBus.$on('logout', function() {
            this.setup()
        }.bind(this))
    },
    watch: {
        selectedItemsPerPage: function(val) {
            if (val === 'all') {
                this.itemsPerPageDefault = this.pagination.total
            } else {
                this.itemsPerPageDefault = parseInt(val)
            }
            this.doPagination(1)
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
