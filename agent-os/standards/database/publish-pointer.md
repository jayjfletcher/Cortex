# Publish Pointer

Publishable entities point at their live version with a pointer column — deliberately without a foreign key:

```php
// No FK: circular reference with cortex_prompt_versions; integrity
// is enforced when publishing.
$table->ulid('published_version_id')->nullable()->index();
```

- The parent table and versions table reference each other — an FK would deadlock creation order, so the pointer stays FK-free with a comment saying why
- Integrity is enforced at the seam instead: the publish action resolves the version with `firstOrFail()` before assigning
- Publishing/unpublishing must invalidate `PublicationCache` for the entity
- Unpublished state = `null` pointer; resource serializes `published_version` only `whenLoaded`
- New publishable entity? Copy this shape: pointer column, migration comment, publish action, cache invalidation
