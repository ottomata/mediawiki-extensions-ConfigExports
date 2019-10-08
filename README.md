# ConfigExports MediaWiki Extension

Exports whitelisted MediaWiki configs via ResourceLoader into `mw.config`
and via a MediaWiki action API endpoint.


## Configuration

The minimal configuration must provide the whitelist of MediaWiki Config keys
to allow exporting.  This extension does not allow auto export of all MediaWiki config.
Config keys must be explicitly whitelisted here.

```php
/** @var array list of MediaWiki config keys (for use with $config->getConfig())
 *             to allow this extension to expose.
 */
$wgConfigExportsKeysWhitelist = [
    'EventServiceStreamConfig',
    'OtherMWConfigKey',
    // ...
];
```

## API Endpoint

Get all MediaWiki configs in `$wgConfigExportsKeysWhitelist`
```
GET /w/api.php?action=config_exports&format=json
```

Get specified MediaWiki configs.  Only configs listed in `$wgConfigExportsKeysWhitelist`
are allowed values of the `configs` query parameter.
```
GET /w/api.php?action=config_exports&format=json&configs=EventServiceStreamConfig|OtherMWConfigKey
```

## ResourceLoader

This ConfigExports extension registers a MakeGlobalVariablesScript that
sets configs in `mw.config` based on the MediaWiki Config `$wgConfigExportsKeys`,
as well as config keys listed in any registered extension attrigutes `ConfigExportsKeys`.


`$wgConfigExportsKeys` allows for global configuration of keys that should always be
exported by ResourceLoader.

Other extensions may register keys they'd like to export in attributes in their extention.json file:
```json
"attributes": {
    "ConfigExports": {
        "Keys": ["EventServiceStreamConfig", "AnotherMWConfigKey"]
    }
}
```

The final list of config keys to export will be the union of `$wgConfigExportsKeys` with
all loaded extensions' `ConfigExportKeys` attribute.

Once the page is loaded, ResourceLoader will make all exported configs available in `mw.config`:

```javascript
var streamConfig = mw.config.get('EventServiceStreamConfig');
````
