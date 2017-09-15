export default {
    methods: {
        getUserTokenMixin: function (apiUrl, credentials) {
            return this.$http.post(apiUrl + '/users/tokens/', credentials)
        },
        registerUserMixin: function (apiUrl, credentials) {
            return this.$http.post(apiUrl + '/users/', credentials)
        },
        getUsersPreviewMixin: function (apiUrl) {
            return this.$http.get(apiUrl + '/users/preview/')
        },
        getProfileMixin: function (apiUrl, userId, authHeader) {
            return this.$http.get(apiUrl + '/users/' + userId + '/profile', {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        getMyProfileMixin: function (apiUrl, authHeader) {
            return this.$http.get(apiUrl + '/users/me/profile', {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        updateMyProfileMixin: function (apiUrl, profileBody, authHeader) {
            return this.$http.patch(apiUrl + '/users/me/profile', profileBody, {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        updateMyProfileImageCollectionMixin: function (apiUrl, profileImageCollectionBody, authHeader) {
            return this.$http.patch(apiUrl + '/users/me/profile/image_collection', profileImageCollectionBody, {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        changeMyPasswordMixin: function (apiUrl, profileBody, authHeader) {
            return this.$http.post(apiUrl + '/users/me/password', profileBody, {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        resetPasswordMixin: function (apiUrl, resetPasswordBody, authHeader) {
            return this.$http.post(apiUrl + '/users/password_resets/', resetPasswordBody, {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        changePasswordMixin: function (apiUrl, changePasswordBody, authHeader) {
            return this.$http.post(apiUrl + '/users/password_changes/', changePasswordBody, {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        setUserLinksMixin: function (apiUrl, userLinksBody, authHeader) {
            return this.$http.post(apiUrl + '/users/me/links/', userLinksBody, {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        setSkillsForUserMixin: function (apiUrl, userSkillsBody, authHeader) {
            return this.$http.post(apiUrl + '/users/me/skills/', userSkillsBody, {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        setTagsForUserMixin: function (apiUrl, userTagsBody, authHeader) {
            return this.$http.post(apiUrl + '/users/me/tags/', userTagsBody, {
                headers: {
                    'Authorization': authHeader
                }
            })
        },
        getProfilePicUrl: function (placeholderImage, profileObject, h, w) {
            if (profileObject.image_collection) {
                if (profileObject.image_collection.profile) {
                    var picUrl = 'https://d1ncrxda1lmimh.cloudfront.net/karolina-fund/image/fetch/c_thumb,h_' + h + ',w_' + w + '/' + profileObject.image_collection.profile.url
                    return picUrl
                } else {
                    return placeholderImage
                }
            } else {
                return placeholderImage
            }
        },
        getProfilePreviewPicUrlLegacy: function (placeholderImage, profileObject, h, w) {
            if (profileObject.image_filename) {
                var picUrl = 'https://d1ncrxda1lmimh.cloudfront.net/karolina-fund/image/fetch/c_thumb,h_' + h + ',w_' + w + '/' + 'https://s3-eu-west-1.amazonaws.com/kfunddynamic/profile_pics/' + profileObject.user_id + '/square/' + profileObject.image_filename
                return picUrl
            } else {
                return placeholderImage
            }
        },
        getProfilePreviewPicUrl: function (placeholderImage, profileObject, h, w) {
            if (profileObject.image) {
                var picUrl = 'https://d1ncrxda1lmimh.cloudfront.net/karolina-fund/image/fetch/c_thumb,h_' + h + ',w_' + w + '/' + profileObject.image
                return picUrl
            } else {
                return placeholderImage
            }
        }
    }
}
