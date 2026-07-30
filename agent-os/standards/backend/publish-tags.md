# Publish Tags

Every publishable group carries the umbrella tag plus its own specific tag:

```php
$this->publishes([
    __DIR__.'/../config/cortex.php' => config_path('cortex.php'),
], ['cortex', 'cortex-config']);
```

- Tags: `cortex` (everything) + `cortex-config`, `cortex-views`, `cortex-lang`, `cortex-assets`, `cortex-migrations`
- Migrations use `publishesMigrations()` (re-dates files on publish)
- All `publishes()` + `commands()` registrations sit behind the `runningInConsole()` guard — keep new ones there
- New publishable group = same dual-tag shape, named `cortex-<thing>`
