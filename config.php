<?php

Kirby::plugin('sylvainjule/locator', array(
	'options' => array(
		'token'        => '',
		'id'           => 'mapbox.outdoors',
		'tiles'        => 'wikimedia',
		'geocoding'    => 'nominatim',
		'display'      => array('lat','lon','number','address','postcode','city','country'),
		'zoom.min'     => 2,
		'zoom.default' => 12,
		'zoom.max'     => 18,
		'center.lat'   => 48.864716,
		'center.lon'   => 2.349014,
		'liststyle'    => 'table',
		'marker'       => 'dark',
	),
	'fields' => require_once __DIR__ . '/lib/fields.php',
    'translations' => array(
        'en' => require_once __DIR__ . '/lib/languages/en.php',
        'fr' => require_once __DIR__ . '/lib/languages/fr.php',
    ),
));