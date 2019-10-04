<?php

namespace MediaWiki\Extension\ConfigExports;

use ApiBase;

class ApiConfigExports extends \ApiBase {
    // 10 minutes
    const CACHE_MAX_AGE = 600;

    public function execute() {
        // TODO: does caching this work?
        $this->getMain()->setCacheMode('public');
        $this->getMain()->setCacheMaxAge(self::CACHE_MAX_AGE);

        $config = $this->getConfig();
        $desiredKeys = $this->getParameter(ConfigExports::CONFIG_KEYS_PARAM);

        // TODO this always returns an object like { "0" => { ... } }.
        // Can we just return the config object?
        $this->getResult()->addValue( null, null, ConfigExports::getConfigExports($config, $desiredKeys) );
    }

    public function getAllowedParams() {
        $config = $this->getConfig();
        $configExportsWhitelist = $config->get( 'ConfigExportsWhitelist' );

        return [
            ConfigExports::CONFIG_KEYS_PARAM => [
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
