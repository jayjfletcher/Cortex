<?php

declare(strict_types=1);

namespace JayI\Cortex\Http;

use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpFoundation\Response;

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
}
