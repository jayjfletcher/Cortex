<?php

declare(strict_types=1);

namespace JayI\Cortex\Http;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base HTTP request.
 *
 * Validation rules come from the Action the request wraps, and `persist()`
 * calls that same Action. Each request's `authorize()` checks the model it
 * touches against the policies in `cortex.policies`.
 */
abstract class Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Execute the request's use case and build the response.
     */
    abstract public function persist(): Response;

    /**
     * Check an ability against the model's policy from `cortex.policies`, as
     * the authenticated user or as a guest.
     *
     * @param  Model|class-string<Model>  $subject
     * @param  array<int, mixed>  $arguments
     */
    protected function allows(string $ability, Model|string $subject, array $arguments = []): bool
    {
        return Gate::forUser($this->user())->allows($ability, [$subject, ...$arguments]);
    }
}
