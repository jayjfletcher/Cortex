# Immutable Versions

Version rows (`PromptVersion`, `ToolDescriptionVersion`) are never edited — new content is a new row with the next version number.

```php
protected static function booted(): void
{
    self::updating(function (): never {
        throw new LogicException('Prompt versions are immutable. Create a new version instead.');
    });
}
```

- Why model-level: agents can pin specific versions — silently editing published or pinned content would change agent behavior invisibly. The `updating()` hook makes the invariant unbreakable even by future code or tinker
- Schema backs it: `unique(['prompt_id', 'version'])`, version numbers assigned sequentially by the create action
- Any new versioned entity must follow the same shape: immutable rows + hook + unique compound index
