<?php

return array(
	'locator' => array(
		'props'    => array(
            'tiles' => function($tiles = null) {
            	// 13.11.2018 - CARTO currently serves the positron style under the 'light_all' name
            	$tiles = $tiles ?? option('sylvainjule.locator.tiles');
            	return str_replace('positron', 'light_all', $tiles);
            },
            'geocoding' => function($geocoding = null) {
            	return $geocoding ?? option('sylvainjule.locator.geocoding');
            },
            'liststyle' => function($liststyle = null) {
            	return $liststyle ?? option('sylvainjule.locator.liststyle');
            },
            'display' => function($display = null) {
            	return $display ?? option('sylvainjule.locator.display');
            },
            'draggable' => function($draggable = null) {
            	return $draggable ?? option('sylvainjule.locator.draggable');
            },
            'autocomplete' => function($autocomplete = null) {
            	return $autocomplete ?? option('sylvainjule.locator.autocomplete');
            },
            'zoom' => function($zoom = []) {
            	return array(
            		'min'     => $zoom['min']     ?? option('sylvainjule.locator.zoom.min'),
            		'default' => $zoom['default'] ?? option('sylvainjule.locator.zoom.default'),
            		'max'     => $zoom['max']     ?? option('sylvainjule.locator.zoom.max'),
            	);
            },
            'saveZoom' => function($saveZoom = null) {
                return $saveZoom ?? option('sylvainjule.locator.saveZoom');
            },
            'autoSaveZoom' => function($autoSaveZoom = null) {
                return $autoSaveZoom ?? option('sylvainjule.locator.autoSaveZoom');
            },
            'language' => function($language = null) {
                return $language ?? option('sylvainjule.locator.language');
            },
            'dblclick' => function($dblclick = null) {
                return $dblclick ?? option('sylvainjule.locator.dblclick');
            },
            'center' => function($center = []) {
            	return array(
            		'lat'     => $center['lat'] ?? option('sylvainjule.locator.center.lat'),
            		'lon'     => $center['lon'] ?? option('sylvainjule.locator.center.lon'),
            	);
            },
            'value' => function($value = null) {
            	return Yaml::decode($value);
            },
		),
		'computed' => array(
			'markerColor' => function() {
				return $this->marker ?? option('sylvainjule.locator.marker');
			},
			'mapbox' => function() {
                $idSwap = [
                    'mapbox.outdoors' => 'mapbox/outdoors-v11',
                    'mapbox.streets'  => 'mapbox/streets-v11',
                    'mapbox.light'    => 'mapbox/light-v10',
                    'mapbox.dark'     => 'mapbox/dark-v10',
                ];

                $setId = option('sylvainjule.locator.mapbox.id');
                $id    = array_key_exists($setId, $idSwap) ? $idSwap[$setId] : $setId;

				return array(
					'id'    => $id,
                	'token' => option('sylvainjule.locator.mapbox.token', ''),
				);
			}
		),
    ),
);
