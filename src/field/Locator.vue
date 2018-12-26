<template>
    <k-field :input="_uid" v-bind="$props" class="k-locator-field">
        <div class="k-input k-locator-input" data-theme="field">
            <input ref="input" v-model="location" class="k-text-input" :placeholder="$t('locator.placeholder')" @input="onLocationInput">
            <button :class="[{disabled: !location.length}]" @click="getCoordinates"><svg><use xlink:href="#icon-locator-locate" /></svg> {{ $t('locator.locate') }}</button>
            <k-dropdown-content v-if="autocomplete" ref="dropdown">
                <k-dropdown-item v-for="(option, index) in dropdownOptions" 
                                 :key="index"
                                 @click="select(option)"
                                 @keydown.native.enter.prevent="select(option)"
                                 @keydown.native.space.prevent="select(option)">
                    <span v-html="option.name" />
                    <span class="k-location-type" v-html="option.type" />
                </k-dropdown-item>
            </k-dropdown-content>
        </div>
        <k-dialog ref="dialog" @close="error = ''">
            <k-text>{{ error }}</k-text>
            <k-button-group slot="footer">
                <k-button icon="check" @click="$refs.dialog.close()">
                    {{ $t("confirm") }}
                </k-button>
            </k-button-group>
        </k-dialog>

        <div class="map-container">
            <div :id="mapId" class="map"></div>
        </div>

        <div v-if="valueExists" :class="['content', liststyle]">
            <div v-for="key in display" v-if="value[key]" class="content-block">
                <div class="title">{{ translatedTitle(key) }}</div>
                <div class="value">{{ value[key] }}</div>
            </div>
        </div>
        <k-empty v-else icon="search" class="k-locator-empty" @click="$refs.input.focus()"> 
            {{ $t('locator.empty') }}
        </k-empty>
    </k-field>
</template>

<script>
import L from "leaflet"

export default {
    data() {
        return {
            map: null,
            marker: null,
            tileLayer: null,
            location: '',
            error: '',
            limit: 1,
            dropdownOptions: [],
        }
    },
    props: {
        markerUrl: String,
        tiles:     String,
        center:    Object,
        zoom:      Object,
        mapbox:    Object,
        display:   Array,
        geocoding: String,
        liststyle: String,
        draggable: Boolean,
        autocomplete: Boolean,

        // general options
        label:     String,
        disabled:  Boolean,
        help:      String,
        parent:    String,
        value:     Object,
        name:      [String, Number],
        required:  Boolean,
        type:      String
    },
    computed: {
        mapId() {
            return 'map-'+ (Math.random() + Math.random()).toString(36).substr(2, 8)
        },
        icon() {
            return L.icon({
                iconUrl: this.markerUrl,
                iconSize: [40, 47],
                iconAnchor: [20, 47],
            })
        },
        valueExists() {
            return this.value ? Object.keys(this.value).length : false
        },
        defaultCoords() {
            return this.valueExists ? [this.value.lat, this.value.lon] : [this.center.lat, this.center.lon]
        },
        coords() {
            return this.valueExists ? [this.value.lat, this.value.lon] : []
        },
        tileUrl() {
            if(this.tiles == 'mapbox') {
                return 'https://api.tiles.mapbox.com/v4/'+ this.mapbox.id +'/{z}/{x}/{y}'+ (L.Browser.retina ? '@2x.png' : '.png') +'?access_token='+ this.mapbox.token
            }
            else if(this.tiles == 'wikimedia') {
                return 'https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}' + (L.Browser.retina ? '@2x.png' : '.png')
            }
            else if(this.tiles == 'openstreetmap') {
                return 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
            }
            else if(this.tiles == 'light_all' || this.tiles == 'voyager') {
                return 'https://cartodb-basemaps-{s}.global.ssl.fastly.net/rastertiles/'+ this.tiles +'/{z}/{x}/{y}' + (L.Browser.retina ? '@2x.png' : '.png')
            }
            else return ''
        },
        attribution() {
            if(this.tiles == 'mapbox') {
                return '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>, &copy; <a href="https://www.mapbox.com/">Mapbox</a>'
            }
            else if(this.tiles == 'wikimedia') {
                return '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>, &copy; <a href="https://maps.wikimedia.org" target="_blank" rel="noreferrer">Wikimedia</a>'
            }
            else if(this.tiles == 'openstreetmap') {
                return '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>'
            }
            else if(this.tiles == 'light_all' || this.tiles == 'voyager') {
                return '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>, &copy; <a href="https://carto.com/attribution" target="_blank" rel="noreferrer">CARTO</a>'
            }
            else return '&copy; <a href="http://www.openstreetmap.org/copyright" target="_blank" rel="noreferrer">OpenStreetMap</a>'
        },
        searchQuery() {
            if(this.geocoding == 'nominatim') {
                return 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&addressdetails=1&q=' + this.location
            }
            else if(this.geocoding == 'mapbox') {
                return 'https://api.mapbox.com/geocoding/v5/mapbox.places/'+ this.location +'.json?types=address,country,postcode,place,locality&limit='+ this.limit +'&access_token=' + this.mapbox.token
            }
            else return ''
        },
    },
    watch: {
        value() {
            this.updateMap()
        }
    },
    mounted() {
        this.initMap()
    },
    methods: {
        onLocationInput() {
            if(!this.autocomplete) return false

            if(this.geocoding && this.location.length) {
                if(this.geocoding != 'mapbox') return false

                this.limit = 5
                fetch(this.searchQuery)
                    .then(response => response.json())
                    .then(response => {
                        // if places are found
                        if(response.features.length) {
                            // keep the relevant ones
                            let suggestions = response.features.filter(el => el.relevance == 1)
                            // make them the dropdown options
                            this.dropdownOptions = suggestions.map(el => {
                                return {
                                    name: el.place_name, 
                                    type: this.capitalize(el.place_type[0]),
                                }
                            })
                            this.$refs.dropdown.open()
                        }
                        else {
                            this.$refs.dropdown.close()
                        }
                    })
                    .catch(error => {
                        this.error = this.$t('locator.error')
                        this.$refs.dialog.open()
                        this.$refs.dropdown.close()
                    })
            }
            else {
                this.$refs.dropdown.close()
            }
        },
        select(option) {
            this.location = option.name
            this.getCoordinates()
        },
        translatedTitle(key) {
            key = key.replace('lon', 'longitude')
            key = key.replace('lat', 'latitude')
            return this.$t('locator.'+ key)
        },
        initMap() {
            // init map
            this.map = L.map(this.mapId, {minZoom: this.zoom.min, maxZoom: this.zoom.max}).setView(this.defaultCoords, this.zoom.default)

            // set the tile layer
            this.tileLayer = L.tileLayer(this.tileUrl, {attribution: this.attribution})
            this.map.addLayer(this.tileLayer)

            // create a marker
            if(this.coords.length) this.setMarker()
        },
        updateMap() {
            if(this.map) {
                if(this.valueExists) this.map.panTo(this.coords)
                
                if(this.marker) {
                    if(this.valueExists) this.marker.setLatLng(this.coords)
                    else                 this.map.removeLayer(this.marker)
                }
                else if(!this.marker && this.valueExists) {
                    this.setMarker()
                }
            }
        },
        setMarker() {
            if(this.marker) this.map.removeLayer(this.marker)
            this.marker = L.marker(this.coords, {
                icon: this.icon, 
                draggable: this.draggable,
                autoPan: this.draggable,
            })
            this.map.addLayer(this.marker)

            this.marker.on('dragend', e => {
                let position = this.marker.getLatLng()
                this.value = {
                    'lat': position.lat,
                    'lon': position.lng,
                    'number': null,
                    'city': null,
                    'country': null,
                    'postcode': null,
                    'address': null,
                }
                this.$emit("input", this.value)
            })
        },
        getCoordinates(e) {
            if(e) {
                e.preventDefault()
                e.stopPropagation()
            }

            if(this.$refs.dropdown) this.$refs.dropdown.close()
            this.limit = 1

            if(this.geocoding && this.location.length) {
                fetch(this.searchQuery)
                    .then(response => response.json())
                    .then(response => {
                        if(response.length || Object.keys(response).length) {
                            if(this.geocoding == 'nominatim') {
                                this.setNominatimResponse(response)
                            }
                            else if(this.geocoding == 'mapbox')  {
                                this.setMapboxResponse(response)
                            }
                            this.location = ''
                        }
                        else {
                            this.error = this.$t('locator.empty_response')
                            this.$refs.dialog.open()
                            this.value = {}
                        }

                        this.$emit("input", this.value)
                    })
                    .catch(error => {
                        this.error = this.$t('locator.error')
                        this.$refs.dialog.open()
                    })
            }
        },
        setNominatimResponse(response) {
            response = response[0]
            this.value = {
                'lat': response.lat,
                'lon': response.lon,
                'number': response.address.house_number,
                'city': response.address.city || response.address.town || response.address.village || response.address.county,
                'country': response.address.country,
                'postcode': response.address.postcode,
                'address': response.address.road,
            }
        },
        setMapboxResponse(response) {
            response = response.features[0]
            this.value = {
                'lat':      response.center[1],
                'lon':      response.center[0],
                'number':   response.address || '',
                'city':     response.context.find(el => el.id.startsWith('place'))    ? response.context.find(el => el.id.startsWith('place')).text    : '',
                'country':  response.context.find(el => el.id.startsWith('country'))  ? response.context.find(el => el.id.startsWith('country')).text  : '',
                'postcode': response.context.find(el => el.id.startsWith('postcode')) ? response.context.find(el => el.id.startsWith('postcode')).text : '',
                'address':  response.text || '',
            }
        },
        capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
    },
}
</script>

<style lang="scss">
    @import '../assets/css/styles.scss'
</style>