<?php

declare(strict_types=1);

namespace JayI\Cortex\Mcp\Requests;

use JayI\Cortex\Actions\DeletePromptAction;
use Laravel\Mcp\Response;

final class DeletePromptMcpRequest extends PromptMcpRequest
{
    protected function rules(): array
    {
        return [
            'slug' => ['required', 'string'],
        ];
    }

    protected function handle(array $validated): Response
    {
        app(DeletePromptAction::class)->execute($this->prompt());

        return Response::text('Prompt deleted.');
    }
}
