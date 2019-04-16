<template>

</template>

<script>
import auth from '../mixins/auth.js'
import helpers from '../mixins/helpers.js'
import jsonp from 'jsonp'
export default {
    name: 'leiki-user',
    data() {
        return {
            personalTabs: []
        }
    },
    mixins: [
        auth,
        helpers
    ],
    methods: {
        setup: function() {
            if (this.isUserAuthenticated) {
                // get Leiki data
                this.getPersonalWidget()
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
        leikiDataEvent: function(event) {
            this.debug('leiki data: ')
            this.debug(event.detail)
            if (event.detail.name === 'getPersonalWidget') {
                this.processGetPersonalWidget(event.detail.data)
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
        // setup
        this.setup()
    }
}
</script>
