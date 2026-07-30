# Feature Toggles

Optional surfaces (dashboard UI, MCP web/local transports) register conditionally in the service provider, driven entirely by config:

```php
if ($config->get('cortex.ui.enabled') !== true) {
    return;
}

if ($config->get('cortex.mcp.web.enabled') === true) { ... }
```

- Compare against literal `true` — fail closed: `'1'`, `'yes'`, or typo'd config never accidentally exposes an endpoint
- Path, route, handle, and middleware for each surface come from config — nothing hardcoded in the provider
- Registration lives in a private `register…()` method per surface, called from `boot()`
- New optional surface = same shape: `cortex.<surface>.enabled` flag, strict check, config-driven wiring
