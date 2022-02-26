<template>
    <k-field :input="_uid" v-bind="$props" :class="['k-locator-field', {filled: valueExists}, status]">
        <!-- Edit button -->
        <template slot="options">
            <k-button v-if="valueExists && filledStatus == 'closed'" :id="_uid" icon="edit" @click="toggle('open')">
                {{ $t('edit') }}
            </k-button>
            <k-button v-if="valueExists && filledStatus == 'open'" :id="_uid" icon="trash" @click="resetValue">
                {{ $t('locator.reset') }}
            </k-button>
            <k-button v-if="valueExists && filledStatus == 'open'" :id="_uid" icon="collapse" @click="toggle('closed')">
                {{ $t('locator.collapse') }}
            </k-button>
        </template>
        <div class="k-input k-locator-input" data-theme="field">
            <input ref="input" v-model="location" class="k-text-input" :placeholder="$t('locator.placeholder')" @input="onLocationInput">
            <button :class="[{disabled: !location.length}]" @click="getCoordinates"><svg><use href="#icon-locator-locate" /></svg> {{ $t('locator.locate') }}</button>
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

        <div class="k-locator-container">
            <div :class="['map-container', {'map-only': !display}]">
                <div :id="mapId" class="map"></div>
            </div>

            <div v-if="valueExists && display" :class="['content', liststyle]">
                <div v-for="key in display" v-if="value[key]" class="content-block">
                    <div class="title">{{ translatedTitle(key) }}</div>
                    <div class="value">{{ value[key] }}</div>
                </div>
            </div>
            <k-empty v-else-if="!valueExists" icon="search" class="k-locator-empty" @click="$refs.input.focus()">
                {{ $t('locator.empty') }}
            </k-empty>
        </div>
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
            filledStatus: 'closed',
            dragged: false
        }
    },
    props: {
        tiles:        String,
        center:       Object,
        zoom:         Object,
        saveZoom:     Boolean,
        autoSaveZoom: Boolean,
        mapbox:       Object,
        display:      [Array, Boolean],
        geocoding:    String,
        liststyle:    String,
        draggable:    Boolean,
        autocomplete: Boolean,
        language:     [String, Boolean],
        dblclick:     String,
        markerColor:  String,

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
            return 'map-'+ this._uid
        },
        icon() {
            let color = this.markerColor
                color = color == 'light' ? '#efefef' : color
                color = color == 'dark'  ? '#161719' : color

            let icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 142"><path fill="'+ color +'" d="M60,0A59.68,59.68,0,0,0,27.37,9.62c-.31.19-.61.39-.92.6A55.74,55.74,0,0,0,7.57,30.71,59.75,59.75,0,0,0,2.63,77.35q.45,1.53,1,3a83.85,83.85,0,0,0,20.53,32.08c9.72,9.51,20.75,17.68,31.2,26.45l3.7,3.09h2c4.7-3.67,9.69-7,14-11.1,9.2-8.69,18.47-17.37,26.92-26.77.49-.54,1-1.09,1.44-1.64l.3-.36c.43-.54.86-1.06,1.28-1.6s.9-1.16,1.34-1.76,1-1.31,1.4-2,.8-1.21,1.19-1.82c.06-.09.12-.18.17-.27.36-.56.72-1.14,1.06-1.72l.08-.13c.29-.5.58-1,.86-1.51.46-.82.91-1.64,1.34-2.48a58.77,58.77,0,0,0,5.35-13s0,0,0,0A59.85,59.85,0,0,0,60,0Zm0,37.55a22.23,22.23,0,1,1-22.2,22.33A22.15,22.15,0,0,1,60,37.55Z"/></svg>'
            let iconUrl = 'data:image/svg+xml;base64,' + btoa(icon);

            return L.icon({
                iconUrl: iconUrl,
                iconSize: [40, 47],
                iconAnchor: [20, 47]
            })
        },
        valueExists() {
            return this.value && (Object.keys(this.value).length > 1 || Object.keys(this.value).length == 1 && !this.value.zoom) ? Object.keys(this.value).length : false
        },
        status() {
            return this.valueExists ? this.filledStatus : ''
        },
        defaultCoords() {
            return this.valueExists ? [this.value.lat, this.value.lon] : [this.center.lat, this.center.lon]
        },
        defaultZoom() {
            return this.valueExists && this.value.zoom ? this.value.zoom : this.zoom.default
        },
        coords() {
            return this.valueExists ? [this.value.lat, this.value.lon] : []
        },
        tileUrl() {
            if(this.tiles == 'mapbox' || this.tiles == 'mapbox.custom') {
                return 'https://api.mapbox.com/styles/v1/'+ this.mapbox.id +'/tiles/256/{z}/{x}/{y}'+ (L.Browser.retina ? '@2x' : '') +'?access_token='+ this.mapbox.token
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
            if(this.tiles == 'mapbox' || this.tiles == 'mapbox.custom') {
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
                let languageParam = this.language ? '&accept-language=' + this.language : ''
                return 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&addressdetails=1&q=' + this.location + languageParam
            }
            else if(this.geocoding == 'mapbox') {
                let languageParam = this.language ? '&language=' + this.language : ''
                return 'https://api.mapbox.com/geocoding/v5/mapbox.places/'+ this.location +'.json?types=address,country,postcode,place,locality&limit='+ this.limit +'&access_token=' + this.mapbox.token + languageParam
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

                const fetchInit = { referrerPolicy: 'strict-origin-when-cross-origin' }

                this.limit = 5
                fetch(this.searchQuery, fetchInit)
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
        toggle(arg) {
            this.filledStatus = arg
            this.$nextTick(() => {
                this.map.invalidateSize()
                this.map.setView(this.coords, this.defaultZoom)
                if(arg == 'closed' && this.marker) this.disableMapEvents()
                else if(arg == 'open' && this.marker) this.enableMapEvents()
            })
        },
        initMap() {
            // init map
            let zoom = this.value ? this.value.zoom || this.defaultZoom : this.defaultZoom

            this.map = L.map(this.mapId, {
                minZoom: this.zoom.min,
                maxZoom: this.zoom.max,
            }).setView(this.defaultCoords, zoom)

            // set the tile layer
            this.tileLayer = L.tileLayer(this.tileUrl, {attribution: this.attribution})

            
            // add event listeners to override the panel's referrerpolicy while loading tiles through Mapbox API
            if(this.tiles == 'mapbox' || this.tiles == 'mapbox.custom') {
                this.tileLayer.on('loading', () => document.querySelector("meta[name=referrer]").content = "strict-origin-when-cross-origin")
                this.tileLayer.on('load', () => document.querySelector("meta[name=referrer]").content = "same-origin")
            }

            // add the tile layer to the map
            this.map.addLayer(this.tileLayer)

            // create a marker
            if(this.coords.length) this.setMarker()

            if (this.saveZoom && this.autoSaveZoom) {
                this.map.on('zoomend', () => {
                    this.value = {
                        ...this.value,
                        'zoom': this.map.getZoom()
                    }

                    this.$emit("input", this.value)
                    this.dragged = true
                    setTimeout(() => {
                        this.dragged = false
                    }, 500)
                });
            }

            if(this.dblclick == 'marker') {
                this.map.doubleClickZoom.disable()
                this.map.on('dblclick', (e) => {
                    this.setCoordinates(e.latlng.lat + ',' + e.latlng.lng)
                })
            }
        },
        updateMap() {
            if(this.map) {
                // If a marker already exists
                if(this.marker) {
                    if(this.valueExists) {
                        this.marker.setLatLng(this.coords)
                        if(!this.dragged) this.toggle('closed')
                    }
                    else {
                        this.map.removeLayer(this.marker)
                        this.marker = null
                    }
                }

                // If a marker should be created
                else if(!this.marker && this.valueExists) {
                    this.setMarker()
                    if(!this.dragged) this.toggle('closed')
                }

                // If there is a filled value
                if(this.valueExists) {
                    this.map.panTo(this.coords)
                }

                // If there is no filled value, reset default view
                else {
                    this.$nextTick(() => {
                        this.map.invalidateSize()
                        let zoom = this.value ? this.value.zoom || this.defaultZoom : this.defaultZoom
                        this.map.setView(this.defaultCoords, zoom)
                    })
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
            if(this.filledStatus == 'closed') this.disableMapEvents()

            this.marker.on('dragend', e => {
                let position = this.marker.getLatLng()
                let _this    = this

                this.value = {
                    'lat': parseFloat(position.lat),
                    'lon': parseFloat(position.lng),
                    'number': null,
                    'city': null,
                    'region': null,
                    'country': null,
                    'postcode': null,
                    'address': null,
                    'osm': null,
                }

                if(this.saveZoom) {
                    this.value = {
                        ...this.value,
                        'zoom': this.map.getZoom()
                    }
                }

                this.$emit("input", this.value)
                this.dragged = true
                setTimeout(() => {
                    _this.dragged = false
                }, 500)
            })
        },
        getCoordinates(e) {
            if(e) {
                e.preventDefault()
                e.stopPropagation()
            }

            if(this.$refs.dropdown) this.$refs.dropdown.close()
            this.limit = 1

            if(this.isLatLon(this.location)) {
                this.setCoordinates(this.location)
                return true
            }

            if(this.geocoding && this.location.length) {
                const fetchInit = this.geocoding == 'mapbox' ? { referrerPolicy: 'strict-origin-when-cross-origin' } : {}
                
                fetch(this.searchQuery, fetchInit)
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
        isLatLon(str) {
            const regexExp = /^((\-?|\+?)?\d+(\.\d+)?),\s*((\-?|\+?)?\d+(\.\d+)?)$/gi;
            return regexExp.test(str);
        },
        setCoordinates(str) {
            let arr   = str.split(',')
            let lat   = arr[0].replace(' ', '')
            let lon   = arr[1].replace(' ', '')
            let _this = this

            this.value = {
                'lat': parseFloat(lat),
                'lon': parseFloat(lon),
                'number': null,
                'city': null,
                'region': null,
                'country': null,
                'postcode': null,
                'address': null,
                'osm': null,
            }

            if(this.saveZoom) {
                this.value = {
                    ...this.value,
                    'zoom': this.map.getZoom()
                }
            }

            this.location = ''
            this.$emit("input", this.value)
            this.dragged = true
            setTimeout(() => {
                _this.dragged = false
            }, 500)
        },
        setNominatimResponse(response) {
            response = response[0]

            this.value = {
                'lat': parseFloat(response.lat),
                'lon': parseFloat(response.lon),
                'number': response.address.house_number,
                'city': response.address.city || response.address.town || response.address.village || response.address.county || response.address.state,
                'region': response.address.state,
                'country': response.address.country,
                'postcode': response.address.postcode,
                'address': response.address.road,
                'osm': response.osm_id
            }
            if(this.saveZoom) {
                this.value = {
                    ...this.value,
                    'zoom': this.map.getZoom()
                }
            }
        },
        setMapboxResponse(response) {
            response = response.features[0]

            this.value = {
                'lat':      parseFloat(response.center[1]),
                'lon':      parseFloat(response.center[0]),
                'number':   response.address || '',
                'city':     response.context.find(el => el.id.startsWith('place'))    ? response.context.find(el => el.id.startsWith('place')).text    : '',
                'region':   response.context.find(el => el.id.startsWith('region'))    ? response.context.find(el => el.id.startsWith('region')).text    : '',
                'country':  response.context.find(el => el.id.startsWith('country'))  ? response.context.find(el => el.id.startsWith('country')).text  : '',
                'postcode': response.context.find(el => el.id.startsWith('postcode')) ? response.context.find(el => el.id.startsWith('postcode')).text : '',
                'address':  response.text || ''
            }
            if(this.saveZoom) {
                this.value = {
                    ...this.value,
                    'zoom': this.map.getZoom()
                }
            }
        },
        capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        },
        disableMapEvents() {
            if(this.map) {
                this.map.scrollWheelZoom.disable()
                this.map.dragging.disable()
                this.map.touchZoom.disable()
                this.map.doubleClickZoom.disable()
                this.map.boxZoom.disable()
                this.map.keyboard.disable()
                if (this.map.tap) this.map.tap.disable()
            }
            if(this.marker) this.marker.dragging.disable()
        },
        enableMapEvents() {
            if(this.map) {
                this.map.scrollWheelZoom.enable()
                this.map.dragging.enable()
                this.map.touchZoom.enable()
                if(this.dblclick != 'marker') this.map.doubleClickZoom.enable()
                this.map.boxZoom.enable()
                this.map.keyboard.enable()
                if (this.map.tap) this.map.tap.enable()
            }
            if(this.marker && this.draggable) this.marker.dragging.enable()
        },
        resetValue() {
            this.value = {}
            this.$emit("input", this.value)
        }
    },
};
</script>

<style lang="scss">
    @import '../assets/css/styles.scss'
</style>
