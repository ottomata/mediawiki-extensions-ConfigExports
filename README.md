# ConfigExports MediaWiki Extension

Exports whitelisted MediaWiki configs via ResourceLoader and an MW action API endpoint.


## Configuration

```php
/** @var array list of MediaWiki config keys (for use with $config->getConfig())
 *             to allow this extension to expose.
 */
$wgConfigExportsWhitelist = [
    'EventServiceStreamConfig',
    'OtherMWConfigKey',
    // ...
];
```

## API Endpoint

Get all MediaWiki configs in `$wgConfigExportsWhitelist`
```
GET /w/api.php?action=configexports&format=json
```

Get specified MediaWiki configs.  Only configs listed in `$wgConfigExportsWhitelist`
are allowed values of the `configkeys` query parameter.
```
GET /w/api.php?action=configexports&format=json&configkeys=EventServiceStreamConfig|OtherMWConfigKey
```

## ResourceLoader

Config keys listed in `$wgConfigExportsWhitelist` will be set in `mw.config`.
Retrieve them with e.g.

```javascript
var streamConfig = mw.config.get('EventServiceStreamConfig');
````

## TODO:
Allow requesting specific config keys via ResourceLoader if possible.

