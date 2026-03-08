<?php
/**
 * Location lookup using free APIs:
 *   - OpenStreetMap Nominatim for geocoding
 *   - Overpass API for POIs and roads
 *   - FEMA NFHL for flood zone data
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RME_EOC_Location_Lookup {

    private static $nominatim_url = 'https://nominatim.openstreetmap.org/search';
    private static $overpass_url  = 'https://overpass-api.de/api/interpreter';
    private static $fema_url      = 'https://hazards.fema.gov/gis/nfhl/rest/services/public/NFHL/MapServer/28/query';
    private static $user_agent    = 'RME-EOC-Scenario-Generator/1.0 (WordPress Plugin)';

    /**
     * Geocode an address to lat/lon.
     *
     * @param string $address
     * @return array|WP_Error { lat, lon, display_name }
     */
    public static function geocode( $address ) {
        $cache_key = 'rme_eoc_geo_' . md5( $address );
        $cached    = get_transient( $cache_key );
        if ( $cached !== false ) {
            return $cached;
        }

        $response = wp_remote_get(
            add_query_arg( array(
                'q'      => $address,
                'format' => 'json',
                'limit'  => 1,
            ), self::$nominatim_url ),
            array(
                'timeout'    => 15,
                'user-agent' => self::$user_agent,
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $body ) ) {
            return new WP_Error( 'geocode_failed', 'Could not geocode address. Try a more specific address.' );
        }

        $result = array(
            'lat'          => (float) $body[0]['lat'],
            'lon'          => (float) $body[0]['lon'],
            'display_name' => $body[0]['display_name'],
        );

        set_transient( $cache_key, $result, HOUR_IN_SECONDS );
        return $result;
    }

    /**
     * Find POIs and roads near a location using Overpass API.
     *
     * @param float $lat
     * @param float $lon
     * @param int   $radius_m Search radius in meters.
     * @return array|WP_Error { hospitals, gas_stations, pharmacies, farm_stores, roads }
     */
    public static function find_nearby( $lat, $lon, $radius_m = 16000 ) {
        $cache_key = 'rme_eoc_nearby_' . md5( "$lat,$lon,$radius_m" );
        $cached    = get_transient( $cache_key );
        if ( $cached !== false ) {
            return $cached;
        }

        // Use different radii: hospitals wider (rural areas), roads narrower
        $poi_radius  = $radius_m;
        $road_radius = min( $radius_m, 5000 );
        $hwy_radius  = $radius_m;

        $query = sprintf(
            '[out:json][timeout:30];
            (
              nwr["amenity"="hospital"]["name"](around:%d,%f,%f);
              nwr["amenity"="fuel"]["name"](around:%d,%f,%f);
              nwr["amenity"="pharmacy"]["name"](around:%d,%f,%f);
              nwr["shop"~"farm|hardware|agrarian|doityourself"]["name"](around:%d,%f,%f);
              way["highway"~"residential|tertiary"]["name"](around:%d,%f,%f);
              way["highway"~"primary|secondary"]["name"](around:%d,%f,%f);
              way["highway"~"trunk|motorway"]["ref"](around:%d,%f,%f);
            );
            out body;',
            $poi_radius, $lat, $lon,   // hospitals
            $poi_radius, $lat, $lon,   // fuel
            $poi_radius, $lat, $lon,   // pharmacy
            $poi_radius, $lat, $lon,   // farm/hardware
            $road_radius, $lat, $lon,  // residential roads
            $poi_radius, $lat, $lon,   // primary roads
            $hwy_radius, $lat, $lon    // highways
        );

        $response = wp_remote_post(
            self::$overpass_url,
            array(
                'timeout'    => 45,
                'user-agent' => self::$user_agent,
                'body'       => array( 'data' => $query ),
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $body['elements'] ) ) {
            return new WP_Error( 'overpass_empty', 'No results from Overpass API. Try increasing the search radius.' );
        }

        $result = self::parse_overpass_results( $body['elements'] );
        set_transient( $cache_key, $result, HOUR_IN_SECONDS );
        return $result;
    }

    /**
     * Parse Overpass API elements into categorized arrays.
     */
    private static function parse_overpass_results( $elements ) {
        $hospitals    = array();
        $gas_stations = array();
        $pharmacies   = array();
        $farm_stores  = array();
        $residential_roads = array();
        $main_roads   = array();
        $highways     = array();

        $seen_roads = array(); // deduplicate road names

        foreach ( $elements as $el ) {
            $tags = isset( $el['tags'] ) ? $el['tags'] : array();
            $name = isset( $tags['name'] ) ? $tags['name'] : '';
            $ref  = isset( $tags['ref'] ) ? $tags['ref'] : '';

            // POIs
            if ( isset( $tags['amenity'] ) ) {
                $addr = self::build_address( $tags );
                switch ( $tags['amenity'] ) {
                    case 'hospital':
                        $hospitals[] = array( 'name' => $name, 'address' => $addr );
                        break;
                    case 'fuel':
                        $gas_stations[] = array( 'name' => $name, 'address' => $addr );
                        break;
                    case 'pharmacy':
                        $pharmacies[] = array( 'name' => $name, 'address' => $addr );
                        break;
                }
            }

            if ( isset( $tags['shop'] ) && in_array( $tags['shop'], array( 'farm', 'hardware', 'agrarian', 'doityourself' ), true ) ) {
                $addr = self::build_address( $tags );
                $farm_stores[] = array( 'name' => $name, 'address' => $addr );
            }

            // Roads
            if ( isset( $tags['highway'] ) && $el['type'] === 'way' ) {
                $road_name = $name ?: $ref;
                if ( ! $road_name || isset( $seen_roads[ $road_name ] ) ) {
                    continue;
                }
                $seen_roads[ $road_name ] = true;

                switch ( $tags['highway'] ) {
                    case 'residential':
                    case 'tertiary':
                        $residential_roads[] = $road_name;
                        break;
                    case 'primary':
                    case 'secondary':
                        $main_roads[] = $road_name;
                        break;
                    case 'trunk':
                    case 'motorway':
                        $highways[] = $ref ?: $road_name;
                        break;
                }
            }
        }

        sort( $residential_roads );
        sort( $main_roads );
        sort( $highways );

        return array(
            'hospitals'         => $hospitals,
            'gas_stations'      => $gas_stations,
            'pharmacies'        => $pharmacies,
            'farm_stores'       => $farm_stores,
            'residential_roads' => $residential_roads,
            'main_roads'        => $main_roads,
            'highways'          => $highways,
        );
    }

    /**
     * Build an address string from OSM tags.
     */
    private static function build_address( $tags ) {
        $parts = array();
        if ( ! empty( $tags['addr:housenumber'] ) && ! empty( $tags['addr:street'] ) ) {
            $parts[] = $tags['addr:housenumber'] . ' ' . $tags['addr:street'];
        }
        if ( ! empty( $tags['addr:city'] ) ) {
            $parts[] = $tags['addr:city'];
        }
        if ( ! empty( $tags['addr:state'] ) ) {
            $parts[] = $tags['addr:state'];
        }
        return implode( ', ', $parts );
    }

    /**
     * Query FEMA NFHL for flood zones near a location.
     * Returns a simplified indicator — not precise road-level data.
     *
     * @param float $lat
     * @param float $lon
     * @param int   $radius_m Buffer in meters.
     * @return array|WP_Error { has_flood_zones, zone_types }
     */
    public static function find_flood_zones( $lat, $lon, $radius_m = 3000 ) {
        $cache_key = 'rme_eoc_flood_' . md5( "$lat,$lon,$radius_m" );
        $cached    = get_transient( $cache_key );
        if ( $cached !== false ) {
            return $cached;
        }

        // Build a bounding box around the point
        $lat_offset = $radius_m / 111320.0;
        $lon_offset = $radius_m / ( 111320.0 * cos( deg2rad( $lat ) ) );

        $bbox = sprintf(
            '%f,%f,%f,%f',
            $lon - $lon_offset, $lat - $lat_offset,
            $lon + $lon_offset, $lat + $lat_offset
        );

        $response = wp_remote_get(
            add_query_arg( array(
                'geometry'     => $bbox,
                'geometryType' => 'esriGeometryEnvelope',
                'inSR'         => '4326',
                'outFields'    => 'FLD_ZONE,ZONE_SUBTY',
                'returnGeometry' => 'false',
                'f'            => 'json',
            ), self::$fema_url ),
            array(
                'timeout'    => 20,
                'user-agent' => self::$user_agent,
            )
        );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        $zone_types = array();
        if ( ! empty( $body['features'] ) ) {
            foreach ( $body['features'] as $feature ) {
                $zone = isset( $feature['attributes']['FLD_ZONE'] ) ? $feature['attributes']['FLD_ZONE'] : '';
                if ( $zone && ! in_array( $zone, $zone_types, true ) ) {
                    $zone_types[] = $zone;
                }
            }
        }

        $result = array(
            'has_flood_zones' => ! empty( $zone_types ),
            'zone_types'      => $zone_types,
        );

        set_transient( $cache_key, $result, HOUR_IN_SECONDS );
        return $result;
    }
}
