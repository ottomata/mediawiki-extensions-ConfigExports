<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * @file
 */

namespace MediaWiki\Extension\ConfigExports;

use \ResourceLoaderContext;

class ConfigExports {

    const CONFIG_KEYS_PARAM = 'configkeys';


    public static function getConfigExports($config, $desiredKeys = null) {
        $configExportsWhitelist = $config->get( 'ConfigExportsWhitelist' );

        if ( !$configExportsWhitelist ) {
            throw new Exception("Must configure ConfigExportsWhitelist");
        }

        $configKeys = $configExportsWhitelist;
        if ($desiredKeys) {
            if (!is_array($desiredKeys)) {
                $desiredKeys = explode("|", $desiredKeys);
            }
            $configKeys = array_intersect($desiredKeys, $configExportsWhitelist);
            // TODO: warn or error if desiredKey is not allowed.
        }

        $exportedConfigs = [];
        foreach ( $configKeys as $key ) {
            $exportedConfigs[$key] = $config->get( $key );
        }

        return $exportedConfigs;
    }

    public static function getConfigExportsFromContext( \ResourceLoaderContext $context ) {
        $config = $context->getConfig();

        $desiredKeys = null;
        // If the request has passed in e.g. ?CONFIG_KEYS_PARAM=key1,key2,
        // only try to export those keys.
        $queryParams = $context->getRequest()->getQueryValues();
        if (array_key_exists(self::CONFIG_KEYS_PARAM, $queryParams)) {
            $desiredKeys = $queryParams[self::CONFIG_KEYS_PARAM];
        }

        return self::getConfigExports($config, $desiredKeys);
    }


    /**
     * Iterates through the config keys in $wgConfigExportsWhitelist
     * and sets them in js config vars, making them available to
     * JS clients as mw.config.get('ConfigKey')
     */
    public static function onMakeGlobalVariablesScript( array &$vars, \OutputPage $out ) {
        $config = $out->getConfig();

        $exportedConfigs = self::getConfigExports($config);
        foreach ($exportedConfigs as $key => $value) {
            $vars[$key] = $value;
        }
    }
}
