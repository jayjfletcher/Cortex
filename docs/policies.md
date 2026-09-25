# Policies

Cortex registers a policy for each of its models from `cortex.policies`, and the JSON API and MCP tools check every call that touches a model through the Gate:

```php
'policies' => [
    Agent::class => \JayI\Cortex\Policies\AgentPolicy::class,
    Prompt::class => \JayI\Cortex\Policies\PromptPolicy::class,
    PromptVersion::class => \JayI\Cortex\Policies\PromptVersionPolicy::class,
    ToolDescription::class => \JayI\Cortex\Policies\ToolDescriptionPolicy::class,
    ToolDescriptionVersion::class => \JayI\Cortex\Policies\ToolDescriptionVersionPolicy::class,
    McpInstruction::class => \JayI\Cortex\Policies\McpInstructionPolicy::class,
    McpInstructionVersion::class => \JayI\Cortex\Policies\McpInstructionVersionPolicy::class,
],
```

## What the bundled policies allow

Cortex records have no owner. Prompts, agents and overrides are shared configuration, so there is no "owner may do anything" rule to apply. Instead:

- **`AgentPolicy`, `PromptPolicy`, `ToolDescriptionPolicy`, `McpInstructionPolicy`** allow every ability, for signed-in users and guests alike. Your route and MCP middleware stay the only gate, exactly as before policies existed. Nothing that worked without them is refused.
- **`PromptVersionPolicy`, `ToolDescriptionVersionPolicy`, `McpInstructionVersionPolicy`** ask the Gate about the parent prompt or override. Listing or reading versions needs `view` on the parent. Adding or publishing a version needs `update` on the parent. Versions are immutable, so no ability changes or deletes one.

Because version policies go through the Gate, replacing the prompt policy also governs its versions.

## What each endpoint checks

Calls are checked as the authenticated user, or as a guest when nobody is signed in.

| Endpoint | MCP tool | Ability | Subject |
| --- | --- | --- | --- |
| `GET /agents` | `list-agents-tool` | `viewAny` | `Agent::class` |
| `POST /agents` | `create-agent-tool` | `create` | `Agent::class` |
| `GET /agents/{slug}` | `show-agent-tool` | `view` | the agent |
| `PATCH /agents/{slug}` | `update-agent-tool` | `update` | the agent |
| `DELETE /agents/{slug}` | `delete-agent-tool` | `delete` | the agent |
| `POST /agents/{slug}/run` | `run-agent-tool` | `run` | the agent |
| `GET /prompts` | `list-prompts-tool` | `viewAny` | `Prompt::class` |
| `POST /prompts` | `create-prompt-tool` | `create` | `Prompt::class` |
| `GET /prompts/{slug}` | `show-prompt-tool` | `view` | the prompt |
| `PATCH /prompts/{slug}` | `update-prompt-tool` | `update` | the prompt |
| `DELETE /prompts/{slug}` | `delete-prompt-tool` | `delete` | the prompt |
| `GET /prompts/{slug}/versions` | `list-prompt-versions-tool` | `viewAny` | `[PromptVersion::class, $prompt]` |
| `POST /prompts/{slug}/versions` | `create-prompt-version-tool` | `create` | `[PromptVersion::class, $prompt]` |
| `GET /prompts/{slug}/versions/{version}` | `show-prompt-version-tool` | `view` | the version |
| `POST /prompts/{slug}/versions/{version}/publish` | `publish-prompt-version-tool` | `publish` | the version |
| `GET /tools/{tool}/description` | | `view` | the override |
| `DELETE /tools/{tool}/description` | | `delete` | the override |
| `GET /tools/{tool}/description/versions` | | `viewAny` | `[ToolDescriptionVersion::class, $description]` |
| `POST /tools/{tool}/description/versions` | | `create` | `[ToolDescriptionVersion::class, $description]` |
| `POST /tools/{tool}/description/versions/{version}/publish` | | `publish` | the version |
| `GET /servers/{server}/instructions` | `show-server-instructions-tool` | `view` | the override |
| `DELETE /servers/{server}/instructions` | `delete-server-instructions-tool` | `delete` | the override |
| `GET /servers/{server}/instructions/versions` | `list-server-instruction-versions-tool` | `viewAny` | `[McpInstructionVersion::class, $instruction]` |
| `POST /servers/{server}/instructions/versions` | `create-server-instruction-version-tool` | `create` | `[McpInstructionVersion::class, $instruction]` |
| `POST /servers/{server}/instructions/versions/{version}/publish` | `publish-server-instruction-version-tool` | `publish` | the version |

Creating the first version of a tool description or server instruction override also creates the override. That call is checked against an unsaved override for that tool or server, so your policy sees `$description->tool` or `$instruction->server` either way.

`GET /tools`, `GET /servers` and `GET /providers` and their MCP tools read the tool and server registries and the provider config. No model is involved, so they check no policy.

An HTTP call that is refused returns `403`. An MCP call that is refused returns the error `Unauthorized.`.

## Replacing a policy

- **Per ability:** extend a bundled policy and override the method for that ability. Then point its model at your class in `cortex.policies`:

  ```php
  namespace App\Policies;

  use Illuminate\Contracts\Auth\Authenticatable;
  use JayI\Cortex\Models\Prompt;
  use JayI\Cortex\Policies\PromptPolicy as CortexPromptPolicy;

  class PromptPolicy extends CortexPromptPolicy
  {
      // Only prompt editors may change prompts, or add and publish versions.
      public function update(?Authenticatable $user, Prompt $prompt): bool
      {
          return $user?->can('edit-prompts') ?? false;
      }
  }
  ```

  ```php
  'policies' => [
      Prompt::class => App\Policies\PromptPolicy::class,
      // ...
  ],
  ```

- **Whole policy:** point the model at your own class. It does not have to extend the bundled one.
- **Signed-in users only:** type the user parameter as non-nullable (`Authenticatable $user`). The Gate then refuses guests without calling the method.

Your application's own `$user->can('run', $agent)` checks use the same policies.
