<template>
    <div class="k-bubbles-field-preview k-locator-field-preview">
        <ul class="k-bubbles">
            <li class="k-bubble" data-has-text="true">
                <figure class="k-frame k-image-frame k-image" style="--fit: contain; --back: var(--bubble-back);">
                     <k-icon type="pin" />
                </figure>
                <span class="k-bubble-text" v-html="location"></span>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    props: {
        value: [Object, String],
    },
    computed: {
        locationString() {
            let number = this.value.number ? this.value.number + " " : "";
            let address = this.value.address ? this.value.address + ", " : "";
            let postcode = this.value.postcode ? this.value.postcode + " " : "";
            let city = this.value.city ? this.value.city + ", " : "";
            let country = this.value.country ? this.value.country : "";

            return number + address + postcode + city + country;
        },
        latLonString() {
            if (this.value.lat && this.value.lon) {
                let lat =
                    typeof this.value.lat === "string"
                        ? parseFloat(this.value.lat)
                        : this.value.lat;
                lat = lat.toFixed(5);
                let lon =
                    typeof this.value.lon === "string"
                        ? parseFloat(this.value.lon)
                        : this.value.lon;
                lon = lon.toFixed(5);

                return lat + ", " + lon;
            } else {
                return false;
            }
        },
        location() {
            if (this.locationString != "") return this.locationString;
            else if (this.latLonString) {
                return (
                    '<span class="locator-latlon-preview">' +
                    this.latLonString +
                    "</span>"
                );
            } else {
                return "…";
            }
        },
    },
};
</script>
