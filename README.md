# Kirby Locator

A simple map & geolocation field, built on top of open-source services and Mapbox.

![screenshot](https://user-images.githubusercontent.com/14079751/48486342-89e44680-e81b-11e8-93fb-c3eadd7fbb61.jpg)

<br/>

## Overview

> This plugin is completely free and published under the MIT license. However, if you are using it in a commercial project and want to help me keep up with maintenance, please consider [making a donation of your choice](https://www.paypal.me/sylvainjl) or purchasing your license(s) through [my affiliate link](https://a.paddle.com/v2/click/1129/36369?link=1170).

- [1. Installation](#1-installation)
- [2. Setup](#2-setup)
- [3. Tile-servers](#3-tile-servers)
  * [3.1. Open-source / free tiles](#31-open-source-free-tiles)
  * [3.2. Mapbox tiles](#32-mapbox-tiles)
- [4. Geocoding service](#4-geocoding-service)
  * [4.1. Open-source API (Nominatim)](#41-open-source-nominatim)
  * [4.2. Mapbox API](#42-mapbox-api)
- [5. Per-field options](#5-per-field-options)
- [6. Global options](#6-global-options)
- [7. Front-end usage](#6-front-end-usage)
- [8. Credits](#7-credits)
- [9. License](#8-license)

<br/>

## 1. Installation

Download and copy this repository to ```/site/plugins/locator```

Alternatively, you can install it with composer: ```composer require sylvainjule/locator```

<br/>

## 2. Setup

Out of the box, the field is set to use open-source services both for geocoding (Nominatim) and tiles-rendering (Positron), without any API-key requirements.

Keep in mind that **these services are bound by strict usage policies**, always double-check if your usage is compatible. Otherwise, please set-up the field to use Mapbox, see details below.

You can also directly enter latitude / longitude coordinates and bypass the geolocation (in a format such as: `15.23456, -30.67890`).

```yaml
mymap:
  label: Location
  type: locator
```

<br/>

## 3. Tile-servers

#### 3.1. Open-source / free tiles

![tiles-opensource-2](https://user-images.githubusercontent.com/14079751/48648038-2c542380-e9ee-11e8-8668-4170ef107195.jpg)

You can pick one of the 4 free tile servers included:

1. ~~`wikimedia` ([Terms of Use](https://foundation.wikimedia.org/wiki/Maps_Terms_of_Use))~~ → Public usage is now forbidden
2. `openstreetmap` ([Terms of Use](https://wiki.openstreetmap.org/wiki/Tile_usage_policy))
3. `positron` (default, [Terms of Use](https://carto.com/legal/) [Under *Free Basemaps Terms of Service*])
4. `voyager` ([Terms of Use](https://carto.com/legal/) [Under *Free Basemaps Terms of Service*])

```yaml
mymap:
  type: locator
  tiles: positron
```

You can also set this globally in your installation's main `config.php`, then you won't have to configure it in every blueprint:

```php
return array(
    'sylvainjule.locator.tiles' => 'positron',
);
```

#### 3.2. Mapbox tiles

![tiles-mapbox-2](https://user-images.githubusercontent.com/14079751/48648037-2c542380-e9ee-11e8-916d-ca240a40bc20.jpg)

1. ~~mapbox.outdoors~~ → `mapbox/outdoors-v11` (default mapbox theme)
2. ~~mapbox.streets~~ → `mapbox/streets-v11`
3. ~~mapbox.light~~ → `mapbox/light-v10`
4. ~~mapbox.dark~~ → `mapbox/dark-v10`

In case your usage doesn't fall into the above policies (or if you don't want to rely on those services), you can set-up the field to use Mapbox' tiles.

Leaflet doesn't render vector-maps, therefore you will not be able to use custom-styles edited with Mapbox Studio, only the public Mapbox tile-layers (listed above).

You will have to set both the `id` of the tiles you want to use and your mapbox `public key` in your installation's main `config.php`:

```php
return array(
    'sylvainjule.locator.mapbox.id'    => 'mapbox/outdoors-v11',
    'sylvainjule.locator.mapbox.token' => 'pk.vdf561vf8...',
);
```

You can now explicitely state in your blueprint that you want to use Mapbox tiles:

```yaml
mymap:
  type: locator
  tiles: mapbox
```

You can also set this globally in your installation's main `config.php`, then you won't have to configure it in every blueprint:

```php
return array(
    'sylvainjule.locator.tiles' => 'mapbox',
);
```

<br/>

## 4. Geocoding services

#### 4.1. Open-source API (Nominatim)

This is the default geocoding service. It doesn't require any additional configuration, but please double-check if your needs fit the [Nominatim Usage Policy](https://operations.osmfoundation.org/policies/nominatim/).

```yaml
mymap:
  type: locator
  geocoding: nominatim
```

#### 4.2. Mapbox API

In case your usage doesn't fall into the above policy (or if you don't want to use Nominatim), you can set-up the field to use Mapbox API.

If you haven't already, you will have to set your mapbox `public key` in your installation's main `config.php`:

```php
return array(
    'sylvainjule.locator.mapbox.token' => 'pk.vdf561vf8...',
);
```

You can now explicitely state in your blueprint that you want to use Mapbox as a geocoding service:

```yaml
mymap:
  type: locator
  geocoding: mapbox
```

You can also set this globally in your installation's main `config.php`, then you won't have to configure it in every blueprint:

```php
return array(
    'sylvainjule.locator.geocoding' => 'mapbox',
);
```

<br/>

## 5. Per-field options

#### 5.1. `center`

The coordinates of the center of the map, if the field has no stored value. Default is `{lat: 48.864716, lon: 2.349014}` (Paris, FR).

```yaml
mymap:
  type: locator
  center:
    lat: 48.864716
    lon: 2.349014
```

#### 5.2. `zoom`

The `min`, `default` and `max` zoom values, where `default` will be the one used on every first-load of the map. Default is: `{min: 2, default: 12, max: 18}`.

```yaml
mymap:
  type: locator
  zoom:
    min: 2
    default: 12
    max: 18
```

#### 5.3. `saveZoom`

Whether the field should store the zoom level of the map when the marker was added, and use it as default zoom value afterwards. Default is `false`.

```yaml
mymap:
  type: locator
  saveZoom: false
```

#### 5.4. `autoSaveZoom`

Whether the field should store the zoom level of the map when the user changes the zoom manually, and use it as default zoom value afterwards. Default is `false`.

```yaml
mymap:
  type: locator
  autoSaveZoom: false
```

#### 5.5. `display`

The informations to be displayed in the panel. Note that it will only hide them from the panel view, they will still be stored (if available) in the .txt file. To be picked from `lat`, `lon`, `number`, `address`, `postcode`, `city`, `region` and `country`. Default includes them all.

If you are using Nominatim, the field also stores the OpenStreetMap ID under the `osm`  key, which you can also display by adding it to the list.

If you don't want any information to show up, set it to `false`.

```yaml
mymap:
  type: locator
  display:
    - lat
    - lon
    - number
    - address
    - postcode
    - city
    - region
    - country
```


#### 5.6. `draggable`

If set to `true`, the marker will be repositionable in case search result isn't precise enough. After being moved, only the new `lat` and `lng` will be stored. Default is `true`.


#### 5.7. `autocomplete`

If set to `true`, **when Mapbox is used for geocoding**, you will be presented up to 5 suggestions while typing your request. Default is `true`.


#### 5.8. `liststyle`

![liststyle](https://user-images.githubusercontent.com/14079751/48487819-9cf91580-e81f-11e8-8e20-eba57f122261.jpg)

The style of the informations block, either `columns` or `table`. Default is `table`.

```yaml
mymap:
  type: locator
  liststyle: table
```


#### 5.9. `marker`

The color of the marker used, either `dark`, `light` or your own HEX value. Default is `dark`.

```yaml
mymap:
  type: locator
  marker: dark
```

#### 5.10. `language`

If this option is set with an [ISO 639-1 code](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes) (en, fr, de, etc.), the geocoding service will return results in the requested language if available. Default is `false`.

```yaml
mymap:
  type: locator
  language: false # or 'de' | 'fr' | 'en' | …
```

#### 5.11. `dblclick`

Whether a double click on the map should trigger a zoom (`zoom`) or add a marker / move the existing marker to the coordinates of the click event (`marker`). Default is `zoom`.

```yaml
mymap:
  type: locator
  dblclick: zoom # or 'marker'
```

<br/>

## 6. Global options

The same options are available globally, which means you can set them all in your installation's `config.php` file and don't worry about setting it up individually afterwards:

```php
return array(
    'sylvainjule.locator.center.lat'   => 48.864716,
    'sylvainjule.locator.center.lon'   => 2.349014,
    'sylvainjule.locator.zoom.min'     => 2,
    'sylvainjule.locator.zoom.default' => 12,
    'sylvainjule.locator.zoom.max'     => 18,
    'sylvainjule.locator.saveZoom'     => false,
    'sylvainjule.locator.autoSaveZoom' => false,
    'sylvainjule.locator.display'      => array('lat','lon','number','address','postcode','city','country'),
    'sylvainjule.locator.draggable'    => true,
    'sylvainjule.locator.autocomplete' => true,
    'sylvainjule.locator.liststyle'    => 'columns',
    'sylvainjule.locator.marker'       => 'dark',
    'sylvainjule.locator.language'     => false,
    'sylvainjule.locator.dblclick'     => 'zoom',
);
```

<br/>

## 7. Front-end usage

The location data is stored as YAML and therefore needs to be decoded with the `yaml` method or using the `toLocation` method (see below):

```php
$location = $page->mymap()->yaml();
```

Potential stored keys are:

- `lat` (Latitude)
- `lon` (Longitude)
- `number` (Street number)
- `address` (Street / road / place)
- `city` (city / village)
- `region` (region / state)
- `country` (country)
- `osm` (OpenStreetMap ID, if using Nominatim)


It is possible that the found location doesn't have one of those keys, which will therefore not be saved. It is important to always check if the key exists, and if it's not empty. Here's one way to do it:

```php
$location = $page->mymap()->yaml();

if(!empty($location['postcode'])) {
    // there is a filled 'postcode' key
}
else {
    // there is no / an empty 'postcode' key
}
```

Alternatively, you can use the `toLocation` method to convert the value to a new collection, an use it kirby-style:

```php
$location = $page->mymap()->toLocation();

// You now have access to
// $location->lat()
// $location->lon()
// ...

if($location->has('postcode')) {
    if($location->postcode()->isNotEmpty()) {
        // there is a filled 'postcode' key
    }
    else {
        // there is an empty 'postcode' key
    }
}
else {
  // there is no 'postcode' key
}
```

<br/>

## 8. Credits

**Services:**
- [Openstreetmap](https://www.openstreetmap.org/#map=5/46.449/2.210), [Wikimedia](https://maps.wikimedia.org), [Carto](https://carto.com/) or [Mapbox](https://www.mapbox.com/) as tile servers.
- [Nominatim](https://nominatim.openstreetmap.org/) or [Mapbox Search](https://www.mapbox.com/search/) as a geocoding API
- [Leaflet](https://leafletjs.com/) as a mapping library.

**K2 fields:**
- [Map-field](https://github.com/AugustMiller/kirby-map-field) by [@AugustMiller](https://github.com/AugustMiller)
- Its [open-source fork](https://github.com/fendinger/kirby-osmap-field) by [@fendinger](https://github.com/fendinger)

<br/>

## 9. License

MIT
