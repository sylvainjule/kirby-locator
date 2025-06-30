<?php

Kirby::plugin(
    name: 'sylvainjule/locator',
    info: [
        'homepage' => 'https://github.com/sylvainjule/kirby-locator'
    ],
    version: '2.0.0',
    extends: [
        'options' => [
            'token'        => '',
            'id'           => 'mapbox.outdoors',
            'tiles'        => 'positron',
            'geocoding'    => 'nominatim',
            'display'      => array('lat','lon','number','address','postcode','city', 'region', 'country', 'countryCode'),
            'draggable'    => true,
            'autocomplete' => true,
            'zoom.min'     => 2,
            'zoom.default' => 12,
            'zoom.max'     => 18,
            'center.lat'   => 48.864716,
            'center.lon'   => 2.349014,
            'liststyle'    => 'table',
            'marker'       => 'dark',
            'saveZoom'     => false,
            'autoSaveZoom' => false,
            'language'     => false,
            'dblclick'     => 'zoom'
        ],
        'fields' => require_once __DIR__ . '/lib/fields.php',
        'fieldMethods' => require_once __DIR__ . '/lib/fieldMethods.php',
        'translations' => [
            'de' => require_once __DIR__ . '/lib/languages/de.php',
            'en' => require_once __DIR__ . '/lib/languages/en.php',
            'fr' => require_once __DIR__ . '/lib/languages/fr.php',
            'pl' => require_once __DIR__ . '/lib/languages/pl.php',
            'tr' => require_once __DIR__ . '/lib/languages/tr.php',
        ]
    ]
);
