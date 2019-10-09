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
use \Exception;
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
    const TARGET_KEYS_CONFIG_NAME = 'ConfigExportsKeys';

    /**
     * Returns an array of target Mediawiki configs that are allowed in
     * $wgConfigExportsKeysWhitelist. If $targetKeys is not provided,
     * they will be looked up as a combination of $wgConfigExportsKeys
     * and any registered extension attributes ConfigExportsKeys.
     *
     * @param  [Config] $config
     * @param  [array]  $targetKeys
     * @return [object]
     */
    public static function getConfigExports($config, $targetKeys = null) {
        $logger = LoggerFactory::getInstance( 'ConfigExports' );

        if ( !$config->has( self::WHITELIST_CONFIG_NAME ) ) {
            throw new Exception( 'Must configure ' . self::WHITELIST_CONFIG_NAME );
        }

        $configKeysWhitelist = $config->get( self::WHITELIST_CONFIG_NAME );

        if ( !$targetKeys ) {
            // If $targetKeys is not set, get target keys from config
            // and registered extension attributes.

            // Mediawiki can configure globally target keys that it
            // always wants to be exported by default.
            $targetKeysFromConfig = $config->has( self::TARGET_KEYS_CONFIG_NAME ) ?
                $config->get( self::TARGET_KEYS_CONFIG_NAME ) : [];

            $extRegistry = ExtensionRegistry::getInstance();
            $targetKeysFromExtensions = $extRegistry->getAttribute( self::TARGET_KEYS_CONFIG_NAME );

            $logger->debug('target keys from config: ' . join(',', $targetKeysFromConfig));
            $logger->debug('target keys from extensions: ' . join(',', $targetKeysFromExtensions));

            // Union the keys from config and extensions.
            $targetKeys = array_unique(
                array_merge( $targetKeysFromConfig, $targetKeysFromExtensions )
            );
        }

        if ( !is_array( $targetKeys ) )  {
            throw new Exception( '$targetKeys must be an array, got: ' . $targetKeys );
        }

        // We target keys being given as ConfigName or as ConfigName.subkey.
        // If any target key has a '.', then split it and assume head
        // is the config name, whereas the tail is the subkey.
        // NOTE: this only supports top single level addressing of subkeys.
        // Additional '.' chars will not result in deeper hierarchical addressing.
        $exportedConfigs = [];
        foreach ( $targetKeys as $key ) {

            $targetConfigName = $key;
            $subKeyIndex = strpos($key, '.');

            if ($subKeyIndex) {
                $targetConfigName = substr($key, 0, $subKeyIndex);
                $subKey = substr($key, $subKeyIndex + 1);
            }

            if ( !in_array( $targetConfigName, $configKeysWhitelist )) {
                throw new Exception(
                    "Config '$targetConfigName' is not whitelisted for export."
                );
            }

            $logger->debug( "Exporting $key" );

            $targetConfigValue = $config->get( $targetConfigName );

            if ( !$subKeyIndex ) {
                // If no '.' was found in the $key, then we know that
                // this is asking for the full MW Config at $targetConfigName.
                // Just set it.
                $exportedConfigs[$targetConfigName] = $targetConfigValue;

            } else {
                // Else we need to address a $subKey inside $targetConfigValue.
                // $targetConfigValue must be an array with this $subKey.
                if ( !is_array( $targetConfigValue ) ) {
                    throw new Excpetion(
                        "Cannot address into '$targetConfigName' with '$subKey', " .
                        "$targetConfigName is not an array."
                    );
                }

                if ( !array_key_exists( $subKey, $targetConfigValue ) ) {
                    throw new Exception(
                        "Config '$targetConfigName' does not have entry '$subKey'."
                    );
                }

                $exportedConfigs[$targetConfigName][$subKey] = $targetConfigValue[$subKey];
            }
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
        // By not passing $targetKeys here, getConfigExports will look them
        // up from $wgConfigExportsKeys and in extension attribufes ConfigExportsKeys.
        $exportedConfigs = self::getConfigExports($config);

        foreach ($exportedConfigs as $key => $value) {
            $vars[$key] = $value;
        }
    }
}
