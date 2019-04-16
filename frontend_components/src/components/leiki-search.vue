<template>

</template>

<script>
import helpers from '../mixins/helpers.js'
import jsonp from 'jsonp'
export default {
    name: 'leiki-search',
    data() {
        return {
            searchTermResults: '',
            results: [],
            leikiResultUrl: 'https://kiwi53.leiki.com/focus/api?method=searchc&apikey=eb426404-1a17-4a1f-ac26-d2b2f32d07c3&instancesearch&partialsearchpriority=ontology&sourceallmatch'
        }
    },
    props: {
        language: {
            required: true,
            type: String
        }
    },
    mixins: [
        helpers
    ],
    methods: {
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
        getTtypeQuery: function() {
            return '&t_type=kokeilunpaikka'
        },
        getAdditionalCatQuery: function() {
            // TODO: implement selected categories
            return ''
        },
        leikiDataEvent: function(event) {
            this.debug('leiki data: ')
            this.debug(event.detail)
            if (event.detail.name === 'searchResults') {
                this.processSearchResults(event.detail.data)
            }
        },
        processSearchResults: function(response) {
            if (response.status === 'ok') {
                if (response.hasOwnProperty('data')) {
                    this.results = []
                    if (response.data.hasOwnProperty('items')) {
                        for (var i = 0; i < response.data.items.length; i++) {
                            this.results.push(response.data.items[i])
                        }
                    }
                }
            } else {
                this.debug('error: ' + response.details.errorCode + ':' + response.details.errorMsg)
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
