# Config Bootstrap

All host→SPA configuration flows through one channel:

```
app.blade.php:  window.CortexConfig = @json($cortexConfig)
config.js:      { ...defaults, ...(window.CortexConfig ?? {}) }
```

- `config.js` is the only reader of `window.CortexConfig`; everything else imports the config module
- Defaults live in `config.js` (`apiBase: '/cortex'`, `basePath: '/cortex/ui'`, session auth) — SPA works with zero host config
- Nested objects (like `auth`) merge over their defaults too — partial host config never wipes sibling keys
- Never read env vars, URLs, or globals anywhere else in the SPA
- New host-configurable setting = add default in `config.js` + pass through `$cortexConfig` server-side
