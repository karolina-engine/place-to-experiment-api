<template>

</template>

<script>
import auth from '../mixins/auth.js'
import helpers from '../mixins/helpers.js'
import jsonp from 'jsonp'
export default {
    name: 'leiki-testbed',
    data() {
        return {
            searchTermCategories: '',
            searchTermResults: '',
            categories: [],
            results: [],
            tags: [],
            leikiCategoryUrl: 'https://kiwi53.leiki.com/focus/api?method=searchcategories&apikey=eb426404-1a17-4a1f-ac26-d2b2f32d07c3&autocomplete=occurred&categoryCounterID=2',
            leikiResultUrl: 'https://kiwi53.leiki.com/focus/api?method=searchc&apikey=eb426404-1a17-4a1f-ac26-d2b2f32d07c3&instancesearch&partialsearchpriority=ontology&sourceallmatch',
            leikiTagUrl: 'https://kiwi53.leiki.com/focus/api?method=analyse&apikey=eb426404-1a17-4a1f-ac26-d2b2f32d07c3',
            targetUrl: '',
            testUrl: '',
            testEnv: false,
            personalTabs: [],
            contextualTabs: []
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
        // inherit global data from parent
        common: {
            required: true,
            type: Object
        }
    },
    mixins: [
        auth,
        helpers
    ],
    methods: {
        searchCategories: function() {
            jsonp(this.leikiCategoryUrl + '&text=' + this.searchTermCategories + '&lang=' + this.language + '&format=jsonp', {
                param: 'jsonp'
            }, function(err, data) {
                if (err) {
                    // debug info
                    console.log('searchCategories error: ')
                    console.log(err.message)
                } else {
                    var event = new CustomEvent('leiki-data', {
                        'detail': {
                            data: data,
                            name: 'searchCategories'
                        }
                    })
                    window.dispatchEvent(event)
                }
            })
        },
        searchResults: function() {
            var tType = this.getTtypeQuery()
            var additionalCat = this.getAdditionalCatQuery()
            jsonp(this.leikiResultUrl + '&lang=' + this.language + tType + additionalCat + '&format=jsonp' + '&query=' + this.searchTermResults, {
                param: 'jsonp'
            }, function(err, data) {
                if (err) {
                    // debug info
                    console.log('searchResults error: ')
                    console.log(err.message)
                } else {
                    var event = new CustomEvent('leiki-data', {
                        'detail': {
                            data: data,
                            name: 'searchResults'
                        }
                    })
                    window.dispatchEvent(event)
                }
            })
        },
        searchTags: function() {
            if (this.targetUrl !== '') {
                jsonp(this.leikiTagUrl + '&target=' + this.targetUrl + '&lang=' + this.language + '&format=jsonp', {
                    param: 'jsonp'
                }, function(err, data) {
                    if (err) {
                        // debug info
                        console.log('searchTags error: ')
                        console.log(err.message)
                    } else {
                        var event = new CustomEvent('leiki-data', {
                            'detail': {
                                data: data,
                                name: 'searchTags'
                            }
                        })
                        window.dispatchEvent(event)
                    }
                })
            } else {
                // debug info
                this.debug('targetUrl cannot be empty')
            }
        },
        getPersonalWidget: function() {
            window._leikiw = window._leikiw || []
            if (this.testEnv) {
                window._leikiw.push({
                    name: 'kokeilunpaikka1p',
                    server: '//tarkkailija.leiki.com/focus',
                    referer: this.testUrl,
                    cid: this.testUrl,
                    isJson: true,
                    jsonCallback: function(data) {
                        var event = new CustomEvent('leiki-data', {
                            'detail': {
                                data: data,
                                name: 'getPersonalWidget'
                            }
                        })
                        window.dispatchEvent(event)
                    }
                })
            } else {
                window._leikiw.push({
                    name: 'kokeilunpaikka1p',
                    server: '//tarkkailija.leiki.com/focus',
                    isJson: true,
                    jsonCallback: function(data) {
                        var event = new CustomEvent('leiki-data', {
                            'detail': {
                                data: data,
                                name: 'getPersonalWidget'
                            }
                        })
                        window.dispatchEvent(event)
                    }
                })
            }
            var t = new Date().getTime()
            jsonp('//tarkkailija.leiki.com/focus/widgets/loader/loader-min.js?t=' + t, {
                param: 'jsonp'
            }, function(err, data) {
                if (err) {
                    console.error(err.message)
                } else {
                    console.log(data)
                }
            })
        },
        getContextualWidget: function() {
            window._leikiw = window._leikiw || []
            if (this.testEnv) {
                window._leikiw.push({
                    name: 'kokeilunpaikka1c',
                    server: '//tarkkailija.leiki.com/focus',
                    referer: this.testUrl,
                    cid: this.testUrl,
                    isJson: true,
                    jsonCallback: function(data) {
                        var event = new CustomEvent('leiki-data', {
                            'detail': {
                                data: data,
                                name: 'getContextualWidget'
                            }
                        })
                        window.dispatchEvent(event)
                    }
                })
            } else {
                window._leikiw.push({
                    name: 'kokeilunpaikka1c',
                    server: '//tarkkailija.leiki.com/focus',
                    isJson: true,
                    jsonCallback: function(data) {
                        var event = new CustomEvent('leiki-data', {
                            'detail': {
                                data: data,
                                name: 'getContextualWidget'
                            }
                        })
                        window.dispatchEvent(event)
                    }
                })
            }
            var t = new Date().getTime()
            jsonp('//tarkkailija.leiki.com/focus/widgets/loader/loader-min.js?t=' + t, {
                param: 'jsonp'
            }, function(err, data) {
                if (err) {
                    console.error(err.message)
                } else {
                    console.log(data)
                }
            })
        },
        getTtypeQuery: function() {
            return '&t_type=kokeilunpaikka&t_type=innokyla&t_type=ratkaisu100'
        },
        getAdditionalCatQuery: function() {
            // TODO: implement selected categories
            return ''
        },
        leikiDataEvent: function(event) {
            this.debug('leiki data: ')
            this.debug(event.detail)
            if (event.detail.name === 'searchCategories') {
                this.processSearchCategories(event.detail.data)
            }
            if (event.detail.name === 'searchResults') {
                this.processSearchResults(event.detail.data)
            }
            if (event.detail.name === 'searchTags') {
                this.processSearchTags(event.detail.data)
            }
            if (event.detail.name === 'getContextualWidget') {
                this.processGetContextualWidget(event.detail.data)
            }
            if (event.detail.name === 'getPersonalWidget') {
                this.processGetPersonalWidget(event.detail.data)
            }
        },
        processSearchCategories: function(response) {
            if (response.status === 'ok') {
                if (response.hasOwnProperty('data')) {
                    this.categories = []
                    if (response.data.hasOwnProperty('matches')) {
                        for (var i = 0; i < response.data.matches.length; i++) {
                            if (response.data.matches[i].hasOwnProperty('match')) {
                                if (response.data.matches[i].match[0]) {
                                    this.categories.push(response.data.matches[i].match[0])
                                }
                            }
                        }
                    }
                }
            } else {
                this.debug('error: ' + response.details.errorCode + ':' + response.details.errorMsg)
            }
        },
        processSearchResults: function(response) {
            if (response.status === 'ok') {
                if (response.hasOwnProperty('data')) {
                    if (response.data.hasOwnProperty('items')) {
                        this.results = []
                        for (var i = 0; i < response.data.items.length; i++) {
                            this.results.push(response.data.items[i])
                        }
                    }
                }
            } else {
                this.debug('error: ' + response.details.errorCode + ':' + response.details.errorMsg)
            }
        },
        processSearchTags: function(response) {
            if (response.status === 'ok') {
                if (response.hasOwnProperty('data')) {
                    if (response.data.hasOwnProperty('items')) {
                        this.tags = []
                        for (var i = 0; i < response.data.items.length; i++) {
                            this.tags.push(response.data.items[i])
                        }
                    }
                }
            } else {
                this.debug('error: ' + response.details.errorCode + ':' + response.details.errorMsg)
            }
        },
        processGetContextualWidget: function(response) {
            if (response.hasOwnProperty('tabs')) {
                this.contextualTabs = response.tabs
            }
        },
        processGetPersonalWidget: function(response) {
            if (response.hasOwnProperty('tabs')) {
                this.personalTabs = response.tabs
            }
        },
        leikiOnClick: function(url) {
            window.leikiComLoader.Widget.onClick(this, url)
        }
    },
    created: function() {
        window.addEventListener('leiki-data', this.leikiDataEvent)
    },
    mounted: function() {
        // debug info
        this.showMountedMessage(this)
    }
}
</script>
