# Auth Drivers

SPA auth is a pluggable driver, selected by `config.auth.mode` (`session` | `token` | `oauth` | `custom`):

```js
// driver contract
{
    headers(refresh),   // required — async, returns request headers
    boot(),             // optional — async setup before app mounts
    retriesOn401(),     // optional — whether 401 warrants one retry with refresh
}
```

- Why: package ships into unknown Laravel apps — session, API token, and OAuth setups all exist; `custom` mode lets a host page define `window.CortexAuth` (same contract, set before the script loads) without forking the SPA
- `client.js` applies driver headers to every request and retries exactly once on 401 when `retriesOn401()` is true — drivers never fetch on their own
- New auth scheme = new driver file in `resources/js/auth/` registered in `drivers` map; don't add mode checks elsewhere
- Unknown mode falls back to `session`
