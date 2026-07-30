# Domain Guards

Business-rule failures in actions throw `ValidationException::withMessages` — not custom exceptions:

```php
if ($prompt->agents()->exists()) {
    throw ValidationException::withMessages([
        'prompt' => 'The prompt is attached to one or more agents and cannot be deleted.',
    ]);
}
```

- Key the message by the relevant input field (`prompt`, `sub_agents`, `prompt_version`)
- Why: both surfaces render it for free — HTTP gets structured 422, MCP gets tool error — and the SPA slots field-keyed errors into existing form error display
- No custom domain exception classes; no abort()/HttpException in actions
- Examples: delete-in-use, circular sub-agent refs, version pinned without prompt
