# Route Conventions

All API routes live in `routes/cortex.php` inside one group:

```php
Route::prefix($prefix)->middleware($middleware)->name('cortex.')->group(...);
```

- Prefix and middleware come from `cortex.routes.*` config — never hardcode
- Every route is named under `cortex.` (`cortex.prompts.versions.publish`)
- Model binding by slug: `{prompt:slug}`, `{agent:slug}` — never by id
- Version segments constrained: `->whereNumber('version')`
- Domain operations (publish, run) are `POST` with a verb suffix on the resource path — never overload PATCH with mode flags:

```php
Route::post('prompts/{prompt:slug}/versions/{version}/publish', ...);
Route::post('agents/{agent:slug}/run', ...);
```

- Explicit route definitions per action — no `Route::resource()`
