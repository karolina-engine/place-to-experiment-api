export default {
    methods: {
        getDaysFromNowFromTimestamp: function (timestamp) {
            if (timestamp) {
                return this.getDifferenceInDaysFromTimestamp(timestamp, this.getCurrentTimestamp())
            } else {
                return false
            }
        },
        getDifferenceInDaysFromTimestamp: function (then, now) {
            let difference = now - then
            let differenceInDays = this.convertSeconds(difference, 'd')
            return differenceInDays
        },
        getCurrentTimestamp: function () {
            return Math.floor(Date.now() / 1000)
        },
        convertSeconds: function (totalSeconds, format) {
            let days, hours, minutes, seconds, totalHours, totalMinutes

            totalMinutes = parseInt(Math.floor(totalSeconds / 60))
            totalHours = parseInt(Math.floor(totalMinutes / 60))
            days = parseInt(Math.floor(totalHours / 24))

            seconds = parseInt(totalSeconds % 60)
            minutes = parseInt(totalMinutes % 60)
            hours = parseInt(totalHours % 24)

            switch (format) {
                case 'm':
                    return totalMinutes
                case 'h':
                    return totalHours
                case 'd':
                    return days
                default:
                    return {
                        d: days,
                        h: hours,
                        m: minutes,
                        s: seconds
                    }
            }
        }
    }
}
