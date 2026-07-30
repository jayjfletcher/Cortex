# API Modules

Views never call `fetch` or the SDK directly. Each resource gets a module in `resources/js/api/`:

```js
import { sdk, unwrap } from './client';

export default {
    list: (page = 1) => unwrap(sdk.GET('/cortex/prompts', { params: { query: { page } } })),
    show: (slug) => unwrap(sdk.GET('/cortex/prompts/{prompt}', { params: { path: { prompt: slug } } })),
};
```

- `unwrap()` converts openapi-fetch results to payload-or-throw: returns `data`, throws `ApiError(status, message, errors)` on failure — every view handles errors the same way
- `client.js` owns auth headers, the single 401 retry, and error normalization — bypassing it silently loses all three
- SDK is the generated `@jayi/cortex-sdk` OpenAPI client; spec churn stays inside `api/`, views only see named methods
- Path params are slugs (matching backend slug binding), never ids
