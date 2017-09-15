<template>


</template>

<script>
import helpers from '../mixins/helpers.js'
import jsonp from 'jsonp'
export default {
    name: 'leiki',
    data() {
        return {
            leikiData: {
                tabs: [{
                    items: [{}]
                }, {
                    items: [{}]
                }]
            }
        }
    },
    props: {
        testUrl: { // Optional. If specified, the server and referer elements of the _leikiw object assume this value
            required: false,
            default: false
        }
    },
    mixins: [
        helpers
    ],
    methods: {
        setup: function() {
            this.setupLeiki()
        },
        setupLeiki: function() {
            window._leikiw = window._leikiw || []
            if (this.testUrl) {
                window._leikiw.push({
                    name: 'kokeilunpaikka1c',
                    server: '//tarkkailija.leiki.com/focus',
                    referer: this.testUrl,
                    cid: this.testUrl,
                    isJson: true,
                    jsonCallback: function(data) {
                        var event = new CustomEvent('leiki-data', {
                            'detail': data
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
                            'detail': data
                        })
                        window.dispatchEvent(event)
                    }
                })
            }
            this.getLeikiScript()
        },
        getLeikiScript: function() {
            var t = new Date().getTime()
            jsonp('//tarkkailija.leiki.com/focus/widgets/loader/loader-min.js?t=' + t, null, function(err, data) {
                if (err) {
                    console.error(err.message)
                } else {
                    console.log(data)
                }
            })
        },
        leikiDataEvent: function(event) {
            this.debug('leiki data: ')
            this.debug(event.detail)
            this.leikiData = event.detail
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
        // set this up
        this.setup()
    }
}

</script>
