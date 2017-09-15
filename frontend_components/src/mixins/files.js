export default {
    methods: {
        uploadImageFileMixin: function (apiUrl, imageBody, authHeader) {
            return this.$http.post(apiUrl + '/files/images/', imageBody, {
                headers: {
                    'Authorization': authHeader
                }
            })
        }
    }
}
