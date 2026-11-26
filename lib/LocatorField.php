<?php

namespace SylvainJule\Locator;

use Kirby\Data\Data;
use Kirby\Form\Field\InputField;
use Kirby\Reflection\Attributes\Prop;
use Kirby\Toolkit\A;

/**
 * A map & geolocation field
 */
class LocatorField extends InputField
{
    /**
     * Shows up to 5 suggestions while typing (Mapbox geocoding only)
     */
    #[Prop]
    protected bool $autocomplete = true;

    /**
     * Saves the zoom level whenever the map is zoomed
     */
    #[Prop]
    protected bool $autoSaveZoom = false;

    /**
     * Default center of the map (`lat`, `lon`)
     */
    #[Prop]
    protected array $center = [
        'lat' => 48.864716,
        'lon' => 2.349014,
    ];

    /**
     * Collapses the map once a location has been selected
     */
    #[Prop]
    protected bool $collapse = true;

    /**
     * Behavior on double click: `zoom` or `marker`
     */
    #[Prop]
    protected string $dblclick = 'zoom';

    /**
     * Location details to display below the map, or `false`
     */
    #[Prop]
    protected array|bool $display = [
        'lat',
        'lon',
        'number',
        'address',
        'postcode',
        'city',
        'region',
        'country',
        'countryCode',
    ];

    /**
     * Makes the marker draggable
     */
    #[Prop]
    protected bool $draggable = true;

    /**
     * Geocoding service: `nominatim` or `mapbox`
     */
    #[Prop]
    protected string|bool $geocoding = 'nominatim';

    /**
     * Language of the geocoding results, or `false`
     */
    #[Prop]
    protected string|bool $language = false;

    /**
     * Style of the details block: `columns` or `table`
     */
    #[Prop]
    protected string $liststyle = 'table';

    /**
     * Mapbox settings (`id`, `token`)
     */
    #[Prop]
    protected array $mapbox = [
        'id'    => null,
        'token' => '',
    ];

    /**
     * Marker color: `dark`, `light` or any CSS color
     */
    #[Prop(export: 'markerColor')]
    protected string $marker = 'dark';

    /**
     * Stores the zoom level along with the location
     */
    #[Prop]
    protected bool $saveZoom = false;

    /**
     * Tile server of the map
     */
    #[Prop]
    protected string $tiles = 'positron';

    /**
     * Zoom levels of the map (`min`, `default`, `max`)
     */
    #[Prop]
    protected array $zoom = [
        'min'     => 2,
        'default' => 12,
        'max'     => 18,
    ];

    protected array $value = [];

    public function config(): array
    {
        // plugin options use dot notation (e.g. `zoom.min`),
        // props expect them as nested arrays
        return A::nest($this->kirby()->option('sylvainjule.locator', []));
    }

    public function fill(mixed $value): static
    {
        return parent::fill(
            value: Data::decode($value, 'yaml')
        );
    }

    public function mapbox(): array
    {
        $aliases = [
            'mapbox.outdoors' => 'mapbox/outdoors-v11',
            'mapbox.streets'  => 'mapbox/streets-v11',
            'mapbox.light'    => 'mapbox/light-v10',
            'mapbox.dark'     => 'mapbox/dark-v10',
        ];

        $id = $this->mapbox['id'] ?? null;

        return [
            'id'    => $aliases[$id ?? ''] ?? $id,
            'token' => $this->mapbox['token'] ?? '',
        ];
    }

    public function tiles(): string
    {
        // CARTO serves the positron style under the `light_all` name
        return str_replace('positron', 'light_all', $this->tiles);
    }
}
