export default {
    methods: {
        getRewardPicUrl: function (placeholderImage, rewardPic, h, w) {
            if (rewardPic !== null) {
                var picUrl = 'https://d1ncrxda1lmimh.cloudfront.net/karolina-fund/image/fetch/c_thumb,h_' + h + ',w_' + w + '/' + rewardPic
                return picUrl
            } else {
                return placeholderImage
            }
        }
    }
}
