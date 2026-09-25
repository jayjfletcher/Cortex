<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Laravel\Mcp\Request as McpRequest;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;

/**
 * Base MCP request: mirrors the HTTP FormRequest `persist()` pattern so
 * tools stay thin and both surfaces resolve the same Actions.
 */
abstract class Request extends McpRequest
{
    final public function persist(): Response|ResponseFactory
    {
        try {
            if (! $this->authorize()) {
                return Response::error('Unauthorized.');
            }

            return $this->handle($this->validated());
        } catch (ModelNotFoundException) {
            return Response::error('Not found.');
        }
    }

    /**
     * Handle the validated tool call and return a response.
     *
     * @param  array<string, mixed>  $validated
     */
    abstract protected function handle(array $validated): Response|ResponseFactory;

    /**
     * Wrap a resolved resource collection in a `data` envelope for list tools.
     *
     * `Response::structured([])` throws, so an empty list must always ship
     * inside a non-empty `{ "data": [...] }` payload.
     *
     * @param  array<int, mixed>  $items
     */
    protected function structuredCollection(array $items): ResponseFactory
    {
        return Response::structured(['data' => $items]);
    }

    /**
     * Validation rules for the tool's input.
     *
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [];
    }

    /**
     * Authorize the tool call. Requests that touch a model override this to
     * check it against the policies in `cortex.policies`.
     */
    protected function authorize(): bool
    {
        return true;
    }

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

    /**
     * The validated, safe input.
     *
     * @return array<string, mixed>
     */
    protected function validated(): array
    {
        return Validator::validate($this->all(), $this->rules());
    }
}
