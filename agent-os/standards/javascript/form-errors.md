# Form & Error Handling

Form views keep two error channels plus busy flags:

```js
const form = ref({ ... });
const errors = ref({});      // field errors, keyed by input name
const error = ref(null);     // banner message
const loading = ref(editing); // initial fetch
const saving = ref(false);    // submit in flight

try {
    await prompts.create({ ... });
} catch (e) {
    if (e instanceof ApiError && e.status === 422) {
        errors.value = e.errors;   // → <FieldErrors :errors="errors.name" />
    } else {
        error.value = e.message;   // → <Alert>
    }
} finally {
    saving.value = false;
}
```

- 422 → `errors` feeds `FieldErrors` under each input; anything else → `error` banner via `Alert` — mirrors backend field-keyed ValidationException standard
- Reset both channels at submit start; clear busy flag in `finally`
- Optional text fields submit `value || null`, not empty strings
- Use shared components (`Alert`, `FieldErrors`, `Spinner`, `ConfirmButton`, `Pagination`) — don't reinvent per view
