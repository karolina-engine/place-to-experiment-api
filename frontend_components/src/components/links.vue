<template>

</template>

<script>
import helpers from '../mixins/helpers.js'
export default {
    name: 'links',
    data() {
        return {}
    },
    props: {
        links: {
            required: true,
            type: Array
        },
        facebookIconPath: {
            required: false,
            type: String
        },
        twitterIconPath: {
            required: false,
            type: String
        },
        instagramIconPath: {
            required: false,
            type: String
        },
        linkedinIconPath: {
            required: false,
            type: String
        },
        emailIconPath: {
            required: false,
            type: String
        },
        wwwIconPath: {
            required: false,
            type: String
        }
    },
    mixins: [
        helpers
    ],
    methods: {
        getLinkClass: function(link) {
            var urlName = this.identifyUrl(link.url)
            switch (urlName) {
                case 'facebook':
                    return 'facebook'
                case 'twitter':
                    return 'tweet'
                case 'linkedin':
                    return 'linkedin'
                case 'instagram':
                    return 'instagram'
                case 'email':
                    return 'mail'
                case 'www':
                    return 'www'
                default:
                    return ''
            }
        },
        getBackgroundImage: function(link) {
            var urlName = this.identifyUrl(link.url)
            switch (urlName) {
                case 'facebook':
                    if (this.facebookIconPath) {
                        return this.facebookIconPath
                    }
                    break
                case 'twitter':
                    if (this.twitterIconPath) {
                        return this.twitterIconPath
                    }
                    break
                case 'linkedin':
                    if (this.linkedinIconPath) {
                        return this.linkedinIconPath
                    }
                    break
                case 'instagram':
                    if (this.instagramIconPath) {
                        return this.instagramIconPath
                    }
                    break
                case 'email':
                    if (this.emailIconPath) {
                        return this.emailIconPath
                    }
                    break
                case 'www':
                    if (this.wwwIconPath) {
                        return this.wwwIconPath
                    }
                    break
                default:
                    return ''
            }
        },
        identifyUrl: function(url) {
            var isEmail = url.split('@')
            if (isEmail.length > 1) {
                return 'email'
            }
            var parser = document.createElement('a')
            parser.href = url
            var urlParts = parser.hostname.split('.')
            for (var i = 0; i < urlParts.length; i++) {
                var check = urlParts[i]
                switch (check) {
                    case 'facebook':
                        return 'facebook'
                    case 'twitter':
                        return 'twitter'
                    case 'linkedin':
                        return 'linkedin'
                    case 'instagram':
                        return 'instagram'
                }
            }
            return 'www'
        }
    },
    mounted: function() {
        // debug info
        this.showMountedMessage(this)
    }
}
</script>
