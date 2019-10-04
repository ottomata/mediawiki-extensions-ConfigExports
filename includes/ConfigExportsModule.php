<?php

namespace MediaWiki\Extension\ConfigExports;

use \ResourceLoaderFileModule;


/**
 *
 */
class ConfigExportsModule extends \ResourceLoaderFileModule {
    /** @inheritDoc */
    public function getScript( \ResourceLoaderContext $context ) {
        return \Xml::encodeJsCall(
            'mw.config.set',
            [ ConfigExports::getConfigExportsFromContext( $context ); ]
        ) . parent::getScript( $context );
    }
}
