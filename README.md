# ConfigExports MediaWiki Extension

Exports whitelisted MediaWiki configs via ResourceLoader into `mw.config`
and via a MediaWiki action API endpoint.

## Configuration

The minimal configuration must provide the whitelist of MediaWiki Config names
to allow exporting.  This extension does not allow auto export of all MediaWiki config.
Configs must be explicitly whitelisted here.

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

## Config Selection

Both the API endpoint and the ResourceLoader hook allow for selection of specific configs in
the same way.  The API endpoint specifies target configs using the `configs` API query parameter,
while ResourceLoader allows target configs to both be specified in MediaWiki Config itself and
by other [extension's attributes](https://www.mediawiki.org/wiki/Manual:Extension_registration#Attributes).
(More on this below.)

Configs may be be selected by top level MediaWiki Config name:

- API: `?configs=MWConfigKey1|MWConfigKey2`
- Extension Attributes: `["MWConfigKey1", "MWConfigKey2"]`

If the target MediaWiki config value is an object, fields to return can be filtered by key:

- API: `?configs=MWConfigKey1.subkey1|MWConfigKey2.subkey2`
- Extension Attributes: `["MWConfigKey1.subkey1", "MWConfigKey2.subkey2"]`

Sub-key filtering is done using PHPs `fnmatch`, so glob pattern matching is supported:

- API: `?configs=MWConfigKey1.sub-key-pattern-*`
- Extension Attributes: `["MWConfigKey1.sub-key-pattern-*]`

Note that sub key filtering is only supported at the top level keys of any given config object.
You cannot use globs to match across MediaWiki config names, and you cannot
match sub-sub keys of config objects.


## API Endpoint

Get all whitelisted MediaWiki configs in `$wgConfigExportsKeysWhitelist`

```
GET /w/api.php?action=config_exports&format=json
```

Get specified MediaWiki configs.  Only MediaWiki config names listed in `$wgConfigExportsKeysWhitelist`
will be allowed to be exported.  Requesting non whitelisted config names will result in an error.

```
GET /w/api.php?action=config_exports&format=json&configs=EventServiceStreamConfig|OtherMWConfigKey.subkey*
```


## ResourceLoader

This ConfigExports extension registers a MakeGlobalVariablesScript that
sets configs in `mw.config` based on the MediaWiki Config `$wgConfigExportsKeys`,
as well as config keys listed in any registered extension attributes `ConfigExportsKeys`.


`$wgConfigExportsKeys` allows for global configuration of keys that should always be
exported by ResourceLoader.

Other extensions may register keys they'd like to export in attributes in their extention.json file:

```json
"attributes": {
    "ConfigExports": {
        "Keys": ["EventServiceStreamConfig.page-edit-*", "AnotherMWConfigKey"]
    }
}
```

The final list of config keys to export will be the union of `$wgConfigExportsKeys` with
all loaded extensions' `ConfigExportKeys` attribute.

Once the page is loaded, ResourceLoader will make all exported configs available in `mw.config`:

```javascript
var streamConfig = mw.config.get('EventServiceStreamConfig');
````

