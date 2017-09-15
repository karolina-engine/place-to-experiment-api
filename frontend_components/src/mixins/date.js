import moment from 'moment-timezone'
export default {
    methods: {
        getTimeLeft: function (deadline) {
            var daysLeft = this.getDaysLeft(deadline)
            if (daysLeft === 0 || daysLeft === 1) {
                var hrsLeft = this.getHrsLeft(deadline)
                if (hrsLeft < 0) {
                    return false
                } else {
                    return (hrsLeft + 1)
                }
            } else if (daysLeft > 1) {
                return daysLeft + 1
            } else {
                return false
            }
        },
        getHrsLeft: function (deadline) {
            var futureTime = moment.unix(deadline)
            var todaysDate = moment()
            var hrsLeft = futureTime.diff(todaysDate, 'hours')
            return hrsLeft
        },
        getDaysLeft: function (deadline) {
            var futureTime = moment.unix(deadline)
            var todaysDate = moment()
            var daysLeft = futureTime.diff(todaysDate, 'days')
            return daysLeft
        },
        getLocalTimeFromUnix: function (timestamp) {
            // TODO: May need to change the timezone thing, if it needs to be taken from the platform config
            return moment.unix(timestamp)
                .format('YYYY-MM-DD HH:mm') + ' ' + moment.tz.guess()
        },
        getNow: function (timestamp) {
            return moment.now()
        },
        formatDate: function (date, format) {
            return moment(date)
                .format(format)
        }
    }
}
