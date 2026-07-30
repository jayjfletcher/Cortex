# Prompt Resolution

`AgentFactory` resolves agent instructions in strict order:

1. No prompt → `''` (agent runs without instructions)
2. Pinned version (`prompt_version_id`) → its content, read directly — pinned content is immutable, so there's nothing to cache or invalidate
3. Published version → through `PublicationCache::remember(promptKey)`
4. Prompt exists but nothing published → `PromptNotPublishedException`

```php
$published = $this->cache->remember(
    $this->cache->promptKey((string) $prompt->getKey()),
    fn (): ?string => $prompt->publishedVersion?->content,
);
```

- `null` (unpublished) is never cached — publishing takes effect immediately without waiting for expiry
- Publishing actions invalidate the key explicitly; anything else touching publication state must do the same
- Keep this order — pinning exists so agents can opt out of following the published pointer
