<?php

namespace MediaWiki\Extension\ConfigExports;

use \ResourceLoaderFileModule;


/**
 *
 */
class ConfigExportsModule extends \ResourceLoaderFileModule {
    /** @inheritDoc */
    public function getScript( \ResourceLoaderContext $context ) {
        $exportedConfigs = ConfigExports::getConfigExportsFromContext( $context );

        return \Xml::encodeJsCall( 'mw.config.set', [ $exportedConfigs ] )
            . parent::getScript( $context );
    }
}
