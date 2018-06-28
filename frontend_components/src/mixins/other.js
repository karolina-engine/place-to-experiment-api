export default {
    methods: {
        getDashboardMixin: function (apiUrl) {
            return this.$http.get(apiUrl + '/dashboard/')
        }
    }
}
