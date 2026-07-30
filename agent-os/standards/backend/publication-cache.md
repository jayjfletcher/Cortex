# PublicationCache

`Support\PublicationCache` caches only published content — data that changes solely when someone publishes:

- Redis available → `flexible()` stale-while-revalidate with `cortex.cache.fresh` / `cortex.cache.stale` windows
- Otherwise → `rememberForever()` until explicit invalidation
- Pin the store with `cortex.cache.store` (tests pin `array` — the Redis probe would otherwise find a local Redis and leak state)

Obligations:

- Every action that changes publication state — publish, unpublish, delete — must `forget()` the entity's key; the cache never expires published content on its own with the non-Redis store
- Keys come from the cache's own helpers (`promptKey()`, …), never hand-built strings
- Don't reach for it for anything that changes outside the publish flow — that's what makes rememberForever safe
