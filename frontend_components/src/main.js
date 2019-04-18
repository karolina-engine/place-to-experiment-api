import Vue from 'vue'
import './public-path'

// import components
Vue.component(
    'authorization',
    require('./components/authorization')
)
Vue.component(
    'navbar',
    require('./components/navbar')
)
Vue.component(
    'image-upload',
    require('./components/image-upload')
)
Vue.component(
    'create-experiment',
    require('./components/create-experiment')
)
Vue.component(
    'experiment-index',
    require('./components/experiment-index')
)
Vue.component(
    'experiment-map',
    require('./components/experiment-map')
)
Vue.component(
    'profile-index',
    require('./components/profile-index')
)
Vue.component(
    'reward-index',
    require('./components/reward-index')
)
Vue.component(
    'profile-page',
    require('./components/profile-page')
)
Vue.component(
    'profile-editable',
    () =>
    /* eslint-disable func-call-spacing */
    import ('./components/profile-editable')
)
Vue.component(
    'admin',
    require('./components/admin')
)
Vue.component(
    'experiment-page',
    require('./components/experiment-page')
)
Vue.component(
    'tags',
    require('./components/tags')
)
Vue.component(
    'experiment-language',
    () =>
    /* eslint-disable func-call-spacing */
    import ('./components/experiment-language')
)
Vue.component(
    'experiment-editable',
    () =>
    /* eslint-disable func-call-spacing */
    import ('./components/experiment-editable')
)
Vue.component(
    'discourse-sso',
    require('./components/discourse-sso')
)
Vue.component(
    'leiki',
    require('./components/leiki')
)
Vue.component(
    'leiki-search',
    require('./components/leiki-search')
)
Vue.component(
    'leiki-user',
    require('./components/leiki-user')
)
Vue.component(
    'leiki-testbed',
    require('./components/leiki-testbed')
)
Vue.component(
    'password-reset',
    require('./components/password-reset')
)
Vue.component(
    'notification-message',
    require('./components/notification-message')
)
Vue.component(
    'links',
    require('./components/links')
)
Vue.component(
    'modal',
    require('./components/modal')
)
Vue.component(
    'sign-in',
    require('./components/sign-in')
)
Vue.component(
    'sign-out',
    require('./components/sign-out')
)
Vue.component(
    'sign-up',
    require('./components/sign-up')
)
Vue.component(
    'alert',
    require('./components/alert')
)
Vue.component(
    'date-picker-component',
    () =>
    /* eslint-disable func-call-spacing */
    import ('./components/date-picker-component')
)
/* eslint-disable */
// import plugins
import VeeValidate from 'vee-validate'
import axios from 'axios'
import VueNotifications from 'vue-notifications'
import miniToastr from 'mini-toastr'
import SocialSharing from 'vue-social-sharing'
import * as VueGoogleMaps from 'vue2-google-maps'
/* eslint-enable */
// prepare plugins
miniToastr.init({
    types: {
        success: 'success',
        error: 'error',
        info: 'info',
        warn: 'warn'
    }
})

function toast({
    title,
    message,
    type,
    timeout,
    cb
}) {
    return miniToastr[type](message, title, timeout, cb)
}

// initialize plugins
Vue.use(VueNotifications, {
    success: toast,
    error: toast,
    info: toast,
    warn: toast
})
Vue.use(VeeValidate, {
    // avoid conflict with other plugins
    fieldsBagName: 'formFields'
})
Vue.use(SocialSharing)
Vue.use(VueGoogleMaps)

// set alias to facilitate migration from vue-resource to axios
Vue.prototype.$http = axios
/* eslint-disable */
// import other JS
import placeholders from './placeholders.js'
import auth from './mixins/auth.js'
import helpers from './mixins/helpers.js'
/* eslint-enable */
// define global event bus
var eventBus = new Vue({})

// define global filters
Vue.filter('truncate', function (text, stop, clamp) {
    if (text) {
        if (text.length > stop) {
            var trimmedString = text.substr(0, stop)
            trimmedString = trimmedString.substr(0, Math.min(trimmedString.length, trimmedString.lastIndexOf(' ')))
            return trimmedString + clamp
        } else {
            return text
        }
    } else {
        return text
    }
})

Vue.filter('date', function (value) {
    const date = new Date(value)
    return date.toLocaleDateString(['EN-fi'], { month: 'numeric', year: 'numeric' })
})

// define custom directives
/* eslint-disable */
// autosize directive automatically resizes a textarea when user types
import autosize from 'autosize'
/* eslint-enable */
Vue.directive('autosize', {
    bind: function (el) {
        Vue.nextTick(function () {
            autosize(el)
        })
    },
    update: function (el, binding) {
        Vue.nextTick(function () {
            el.value = binding.value
            autosize.update(el)
        })
    },
    unbind: function (el) {
        autosize.destroy(el)
    }
})

/* eslint-disable no-new */
new Vue({
    el: '#app',
    name: 'root',
    data: {
        eventBus: eventBus,
        placeholders: placeholders
    },
    mixins: [
        auth,
        helpers
    ],
    methods: {
        setAttributes: function() {
            // special hack because $attrs are not populated by Vue in root instance
            let tempArray = Array.from(this.$el.attributes)
            for (let attr in tempArray) {
                let name = tempArray[attr].name
                let value = tempArray[attr].value
                this.attributes = {
                    [name]: value
                }
            }
        }
    }
})
