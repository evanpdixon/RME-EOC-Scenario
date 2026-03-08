<?php
/**
 * Pre-built location configurations.
 * Ported from ZTH_Location_Configs.py.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RME_EOC_Location_Presets {

    public static function get_all() {
        return array(
            'henderson_tx'  => self::henderson_tx(),
            'stoneboro_pa'  => self::stoneboro_pa(),
            'spiro_ok'      => self::spiro_ok(),
            'stony_point_nc' => self::stony_point_nc(),
            'jackson_oh'    => self::jackson_oh(),
        );
    }

    public static function henderson_tx() {
        return array(
            'label'         => 'Henderson, Texas',
            'location'      => 'Henderson, Texas',
            'venue'         => '200 N Mill St, Henderson, TX 75652',
            'main_road'     => 'Rusk Ave',
            'flooded_road'  => 'FM 1798',
            'blocked_road'  => 'Main St',
            'highway'       => 'US-79',
            'main_st'       => 'US-79 S',
            'addr_a'        => '412',
            'addr_grandma'  => '416',
            'addr_uncle'    => '408',
            'addr_neighbor' => '418',
            'grandma_name'  => 'Hazel Pittman',
            'uncle_name'    => 'John',
            'hospital'      => 'UT Health Henderson, 300 Wilson St',
            'gas_station'   => 'Murphy USA on US-79 S',
            'local_store'   => 'Tractor Supply on US-79 S',
        );
    }

    public static function stoneboro_pa() {
        return array(
            'label'         => 'Stoneboro, Pennsylvania',
            'location'      => 'Stoneboro, Pennsylvania',
            'venue'         => 'Griffin Arms, 4581 Sandy Lake Greenville Rd, Stoneboro, PA 16153',
            'main_road'     => 'Mercer St',
            'flooded_road'  => 'Sandy Creek Rd',
            'blocked_road'  => 'Lake St',
            'highway'       => 'PA-358',
            'main_st'       => 'Mercer St',
            'addr_a'        => '2511',
            'addr_grandma'  => '2515',
            'addr_uncle'    => '2507',
            'addr_neighbor' => '2517',
            'grandma_name'  => 'Hazel Pittman',
            'uncle_name'    => 'John',
            'hospital'      => 'UPMC Horizon-Greenville, 110 N Main St, Greenville PA',
            'gas_station'   => 'Anchors Away on Mercer St',
            'local_store'   => 'farm supply store on PA-358',
        );
    }

    public static function spiro_ok() {
        return array(
            'label'         => 'Spiro, Oklahoma',
            'location'      => 'Spiro, Oklahoma',
            'venue'         => 'Refuge Medical Training Center, 905 W Broadway St, Spiro, OK 74959',
            'main_road'     => 'S Main St',
            'flooded_road'  => 'Lock and Dam Rd',
            'blocked_road'  => 'S Broadway St',
            'highway'       => 'US-271',
            'main_st'       => 'W Broadway St',
            'addr_a'        => '318',
            'addr_grandma'  => '322',
            'addr_uncle'    => '314',
            'addr_neighbor' => '324',
            'grandma_name'  => 'Hazel Pittman',
            'uncle_name'    => 'John',
            'hospital'      => 'Eastern Oklahoma Medical Center, 105 Wall St, Poteau OK',
            'gas_station'   => "Love's Travel Stop on US-271",
            'local_store'   => 'Atwoods Ranch and Home in Poteau',
        );
    }

    public static function jackson_oh() {
        return array(
            'label'         => 'Jackson, Ohio',
            'location'      => 'Jackson, Ohio',
            'venue'         => '86 Tick Ridge Rd, Jackson, OH',
            'main_road'     => 'Pattonsville Rd',
            'flooded_road'  => 'Goose Run Rd',
            'blocked_road'  => 'E Broadway St',
            'highway'       => 'US-35',
            'main_st'       => 'E Main St',
            'addr_a'        => '2847',
            'addr_grandma'  => '2849',
            'addr_uncle'    => '2845',
            'addr_neighbor' => '2851',
            'grandma_name'  => 'Hazel Pittman',
            'uncle_name'    => 'John',
            'hospital'      => 'Jackson Area Medical Center / Holzer Jackson',
            'gas_station'   => 'Marathon station on E Main St',
            'local_store'   => 'Tractor Supply on E Main St',
        );
    }

    public static function stony_point_nc() {
        return array(
            'label'         => 'Stony Point, North Carolina (HQ)',
            'location'      => 'Stony Point, North Carolina',
            'venue'         => '8784 NC Highway 90 E, Stony Point, NC 28678',
            'main_road'     => 'NC-90',
            'flooded_road'  => 'Rocky Creek Rd',
            'blocked_road'  => 'Wilkesboro Hwy',
            'highway'       => 'I-77',
            'main_st'       => 'NC-90',
            'addr_a'        => '8780',
            'addr_grandma'  => '8776',
            'addr_uncle'    => '8788',
            'addr_neighbor' => '8774',
            'grandma_name'  => 'Hazel Pittman',
            'uncle_name'    => 'John',
            'hospital'      => 'Iredell Memorial Hospital, 557 Brookdale Dr, Statesville NC',
            'gas_station'   => 'BP station on NC-90',
            'local_store'   => 'Tractor Supply on US-21 in Statesville',
        );
    }
}
