# @jayi/cortex-sdk

Type-safe client for the Cortex management API, generated from the package's
OpenAPI spec (`openapi.json`, exported by `npm run sdk:generate` in the
package root).

```ts
import { createCortexClient } from "@jayi/cortex-sdk";

const cortex = createCortexClient({
    baseUrl: "https://example.test",
    accessToken: token, // optional; pass a custom fetch for dynamic auth
});

const { data, error } = await cortex.GET("/cortex/agents");
```

The Cortex dashboard consumes this SDK with a custom `fetch` that injects the
configured auth driver's headers and retries once on 401.
