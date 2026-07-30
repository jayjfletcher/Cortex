# Container Bindings

Binding lifetime in `CortexServiceProvider::register()` is a deliberate choice per service:

```php
$this->app->singleton(Tools\ToolRegistry::class);        // static code registrations
$this->app->scoped(Tools\ToolDescriptionOverrides::class); // memoizes DB state
$this->app->singleton(Cortex::class);
```

- `singleton` for services holding code-level state (tool registrations) — safe for the process lifetime
- `scoped` for services that memoize database state: `ToolDescriptionOverrides` caches published descriptions, and a singleton would serve stale data across requests in long-running workers (Octane, queues)
- Rule when adding a binding: memoizes DB/request state → `scoped`; pure code/config state → `singleton`
- Actions are never bound — resolved fresh via `app(Action::class)` each call
