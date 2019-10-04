<?php

namespace MediaWiki\Extension\ConfigExports;

use ApiBase;

class ApiConfigExports extends \ApiBase {

    // TODO enable caching:
    // https://www.mediawiki.org/wiki/API:Extensions#Caching

    /**
     * In this example we're returning one ore more properties
     * of wgExampleFooStuff. In a more realistic example, this
     * method would probably
     */
    public function execute() {

        $config = $this->getConfig();
        $desiredKeys = $this->getParameter(ConfigExports::CONFIG_KEYS_PARAM);

        wfDebugLog('CONFIGMODULE', "Desired configkeys API " . $desiredKeys);
        // TODO this always returns an object like { "0" => { ... } }.
        // Can we just return the config object?
        $this->getResult()->addValue( null, null, ConfigExports::getConfigExports($config, $desiredKeys) );
    }

    public function getAllowedParams() {
        return [
            ConfigExports::CONFIG_KEYS_PARAM => [
                ApiBase::PARAM_TYPE => 'string',
            ],
        ];
    }

    protected function getExamplesMessages() {
        return [
            'action=configexports'
                => 'apihelp-query+example-example-1',
            'action=configexports&configkeys=MediaWikiConfigA,ConfigKeyB'
                => 'apihelp-query+example-example-2',
        ];
    }
}
