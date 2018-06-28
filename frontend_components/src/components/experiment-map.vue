<template>

</template>

<script>
import helpers from '../mixins/helpers.js'
import experiments from '../mixins/experiments.js'
import {
    load
} from 'vue2-google-maps'
export default {
    name: 'experiment-map',
    data() {
        return {
            center: {},
            cluster: false,
            markers: [],
            infoName: '',
            infoContent: [],
            infoWindowPos: {
                lat: 0,
                lng: 0
            },
            infoWinOpen: false,
            currentMidx: null,
            // optional: offset infowindow so it visually sits nicely on top of our marker
            infoOptions: {
                pixelOffset: {
                    width: 0,
                    height: -40
                }
            }
        }
    },
    props: {
        experiments: {
            required: true,
            type: Array
        },
        googleMapsApiKey: {
            required: true,
            type: String
        },
        experimentGeocodedLocations: {
            required: true,
            type: Array
        },
        mapCenter: {
            required: true,
            type: Object
        }
    },
    mixins: [
        experiments,
        helpers
    ],
    methods: {
        setup: function() {
            this.center = this.mapCenter
            load({
                key: this.googleMapsApiKey
            })
        },
        setMarkers: function() {
            this.markers = []
            this.infoWinOpen = false
            for (var i = 0; i < this.experiments.length; i++) {
                if (this.experiments[i].geographic_location !== null) {
                    var existing = false
                    if (this.markers.length > 0) {
                        for (var k = 0; k < this.markers.length; k++) {
                            if (this.markers[k].name === this.experiments[i].geographic_location) {
                                // this.debug('found an existing marker: ' + this.markers[k].name)
                                existing = true
                                this.setMarkerContent(this.markers[k].content, this.experiments[i].experiment_id, this.experiments[i].title, this.experiments[i].short_description)
                            }
                        }
                    }
                    if (!existing) {
                        for (var j = 0; j < this.experimentGeocodedLocations.length; j++) {
                            if (this.experimentGeocodedLocations[j][0] === this.experiments[i].geographic_location) {
                                // this.debug('found a location: ' + this.experimentGeocodedLocations[j][0])
                                var marker = {
                                    name: this.experimentGeocodedLocations[j][0],
                                    content: [{
                                        id: this.experiments[i].experiment_id,
                                        title: this.experiments[i].title,
                                        description: this.experiments[i].short_description
                                    }],
                                    position: {
                                        lat: parseFloat(this.experimentGeocodedLocations[j][1]),
                                        lng: parseFloat(this.experimentGeocodedLocations[j][2])
                                    }
                                }
                                // this.debug('pushing a new marker: ' + marker.name)
                                this.markers.push(marker)
                            }
                        }
                    }
                }
            }
        },
        toggleInfoWindow: function(marker, idx) {
            this.infoWindowPos = marker.position
            this.infoName = marker.name
            this.infoContent = marker.content
            // check if its the same marker that was selected if yes toggle
            if (this.currentMidx === idx) {
                this.infoWinOpen = !this.infoWinOpen
            } else {
                // if different marker set infowindow to open and reset current marker index
                this.infoWinOpen = true
                this.currentMidx = idx
            }
        },
        setMarkerContent: function(content, id, title, description) {
            content.push({
                id: id,
                title: title,
                description: description
            })
        }
    },
    computed: {
        placeholderImage: function() {
            if (this.placeholderImageUrl) {
                return this.placeholderImageUrl
            } else {
                return null
            }
        }
    },
    watch: {
        experiments: function(oldVal, newVal) {
            this.setMarkers()
        }
    },
    mounted: function() {
        // debug info
        this.showMountedMessage(this)
        // set this up
        this.setup()
    }
}
</script>
