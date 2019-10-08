<?php

namespace MediaWiki\Extension\ConfigExports;

use ApiBase;

/**
 * Enables requesting whitelisted Mediawiki configs via the API.
 * Usage:
 *
 * Get all whitelisted configs:
 *     GET /w/api.php?format=json&action=config_exports
 *
 * Get specified configs (the specified configs must be whitelisted)
 *     GET /w/api.php?format=json&action=config_exports&configs=ConfigKey1|ConfigKey2
 *
 */
class ApiConfigExports extends \ApiBase {
    // 10 minutes
    const CACHE_MAX_AGE = 600;

    /**
     * API query param used to specify desired keys to get from MW config.
     */
    const API_PARAM = 'configs';

    public function execute() {
        // TODO: does caching this work?
        $this->getMain()->setCacheMode('public');
        $this->getMain()->setCacheMaxAge(self::CACHE_MAX_AGE);

        $config = $this->getConfig();
        $desiredKeys = $this->getParameter(self::API_PARAM);

        // TODO: this always returns an object like { "0" => { key1 => value1, ... } }.
        // Can we just return the config object like { key1 => value1, ... }
        $this->getResult()->addValue(
            null, null, ConfigExports::getConfigExports($config, $desiredKeys)
        );
    }

    public function getAllowedParams() {
        $config = $this->getConfig();
        $configExportsWhitelist = $config->get( ConfigExports::WHITELIST_CONFIG_NAME );

        return [
            self::API_PARAM => [
                // Only the whitelisted config keys are allowed to be requested.
                ApiBase::PARAM_TYPE => $configExportsWhitelist,
                ApiBase::PARAM_ISMULTI => true,
            ],
        ];
    }

    // TODO:
    // protected function getExamplesMessages() {
    //     return [
    //         'action=configexports'
    //             => 'apihelp-query+example-example-1',
    //         'action=configexports&configkeys=MediaWikiConfigA,ConfigKeyB'
    //             => 'apihelp-query+example-example-2',
    //     ];
    // }
}
