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

use \ExtensionRegistry;
use MediaWiki\Logger\LoggerFactory;

class ConfigExports {

    /**
     * Name of the global config that specifies what keys are allowed to be exported.
     */
    const WHITELIST_CONFIG_NAME = 'ConfigExportsKeysWhitelist';

    /**
     * Name of the global config that specifies what config keys should be exported.
     * Also the extension attibute name to be used by other
     * extensions to specify config keys they want exported.
     * Other extension's extension.json files can set this e.g.:
     *
     *  "attributes": {
     *      "ConfigExports": {
     *          "Keys": ["ConfigKey1", "ConfigKey2"]
     *      }
     *  }
     *
     * See: https://www.mediawiki.org/wiki/Manual:Extension_registration#Attributes
     */
    const DESIRED_CONFIG_NAME = 'ConfigExportsKeys';


    /**
     * Returns an array of desired Mediawiki configs that are allowed in
     * $wgConfigExportsKeysWhitelist. If $desiredKeys is not provided,
     * they will be looked up as a combination of $wgConfigExportsKeys
     * and any registered extension attributes ConfigExportsKeys.
     *
     * @param  [Config] $config
     * @param  [array]  $desiredKeys
     * @return [object]
     */
    public static function getConfigExports($config, $desiredKeys = null) {
        $logger = LoggerFactory::getInstance( 'ConfigExports' );

        if ( !$config->has( self::WHITELIST_CONFIG_NAME ) ) {
            throw new Exception( 'Must configure ' . self::WHITELIST_CONFIG_NAME );
        }

        $keysWhitelist = $config->get( self::WHITELIST_CONFIG_NAME );

        if ( !$desiredKeys ) {
            // If $desiredKeys is not set, get desired keys from config
            // and registered extension attributes.

            // Mediawiki can configure globally configs that it
            // always wants to be exported by default.
            $desiredKeysFromConfig = $config->has( self::DESIRED_CONFIG_NAME ) ?
                $config->get( self::DESIRED_CONFIG_NAME ) :
                [];

            $extRegistry = ExtensionRegistry::getInstance();
            $desiredKeysFromExtensions = $extRegistry->getAttribute( self::DESIRED_CONFIG_NAME );

            $logger->debug('Desired keys from config: ' . join(',', $desiredKeysFromConfig));
            $logger->debug('Desired keys from extensions: ' . join(',', $desiredKeysFromExtensions));

            // Union the keys from config and extensions.
            $desiredKeys = array_unique(
                array_merge( $desiredKeysFromConfig, $desiredKeysFromExtensions )
            );
        }

        if ( !is_array( $desiredKeys ) )  {
            throw new Exception( '$desiredKeys must be an array, got: ' . $desiredKeys );
        }

        // TODO: warn or error if a desiredKey is not allowed.
        $keysToExport = array_intersect( $desiredKeys, $keysWhitelist );
        $logger->debug('Exporting configs: ' . join(',', $keysToExport));

        $exportedConfigs = [];
        foreach ( $keysToExport as $key ) {
            $exportedConfigs[$key] = $config->get( $key );
        }

        return $exportedConfigs;
    }

    /**
     * Sets mw.config keys from $wgConfigExportsKeys and declared
     * extension attributes ConfigExportsKeys.
     *
     * @param  array       &$vars
     * @param  \OutputPage $out
     */
    public static function onMakeGlobalVariablesScript( array &$vars, \OutputPage $out ) {
        $config = $out->getConfig();
        // By not passing $desiredKeys here, getConfigExports will look them
        // up from $wgConfigExportsKeys and in extension attribufes ConfigExportsKeys.
        $exportedConfigs = self::getConfigExports($config);

        foreach ($exportedConfigs as $key => $value) {
            $vars[$key] = $value;
        }
    }
}
