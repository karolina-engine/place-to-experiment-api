export default {
    methods: {
        getCampaignMixin: function (apiUrl, campaignId, language) {
            return this.$http.get(apiUrl + '/campaigns/' + campaignId + '/' + language)
        }
    }
}
