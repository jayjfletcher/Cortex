# Release Notes

## [Unreleased](https://github.com/jayi/cortex/compare/v0.1.0...1.x)

### Added

- Prompt management with immutable versioning and a published-version pointer (`cortex_prompts`, `cortex_prompt_versions`). All Cortex tables use ULID primary keys.
- Agent management: DB-backed agents combining a prompt (published or pinned version), registered tools, provider/model settings, and sub-agents (`cortex_agents`, `cortex_agent_agent`), with cycle protection.
- `ToolRegistry` for registering `Laravel\Ai\Contracts\Tool` classes by name via config (`cortex.tools`) or `Cortex::tools()->register()`.
- Agent execution on the Laravel AI SDK: `Cortex::run($slug, $input)`, `Cortex::agent($slug)`, `POST /cortex/agents/{slug}/run`, and the `run-agent` MCP tool.
- REST API under the configurable `cortex` prefix covering prompts, versions, publish, agents, tools, and run.
- `CortexServer` MCP server with 16 tools at full parity with the API, config-gated web (`Mcp::web`) and local (`Mcp::local`) transports, disabled by default.
- `laravel/ai` (^0.11) and `laravel/mcp` (^1.0) dependencies.
- MCP server instruction management with immutable versioning and a published-version pointer (`cortex_mcp_instructions`, `cortex_mcp_instruction_versions`): published overrides replace a server's code-declared `#[Instructions]` at runtime.
- `McpServerRegistry` for registering MCP server classes by name via config (`cortex.mcp.servers`) or `Cortex::servers()->register()`; Cortex's own server is always registered as `cortex`.
- `JayI\Cortex\Mcp\Server` base class and `HasVersionedInstructions` trait so any Laravel MCP server can serve its published instruction override.
- REST endpoints under `/cortex/servers` for listing servers and managing instruction overrides, six matching MCP tools on `CortexServer` (now 22 tools), and a Servers section in the dashboard with a versioned instructions editor.

- Model events: every Eloquent hook of every Cortex model dispatches its own class in `JayI\Cortex\Events\Model` (`{Model}{Hook}Event`, e.g. `PromptVersionCreatedEvent`) through the `DispatchesModelEvents` trait. All implement `JayI\Cortex\Contracts\ModelLifecycleEvent`.
- Action events: every action dispatches a start event before its work and a finish event with its result (`JayI\Cortex\Events\Action`, e.g. `AgentRunningActionEvent` / `AgentRanActionEvent`). Start events implement `ActionStartingEvent`. Finish events implement `ActionFinishedEvent`, dispatch after commit and are skipped when the action throws. See `docs/events.md`.
- Policies for every model (`JayI\Cortex\Policies`), registered with the Gate from the new `cortex.policies` config. Every API endpoint and MCP tool that touches a model now authorizes through them, as the signed-in user or as a guest. The bundled policies allow everything, since Cortex records have no owner, so existing behaviour is unchanged. Version policies defer to their prompt or override through the Gate. See `docs/policies.md`.

### Changed

- The dashboard is server-rendered Blade built on `jayi/atrium`, which Cortex now requires, replacing the Vue 3 SPA. Cortex registers an Atrium plugin with navigation, routes under `/atrium/cortex/...`, search over prompts and agents, and a settings panel. Atrium owns the path, middleware and `viewAtrium` gate, so the `ui.auth` config, the `UiTokenResolver` contract and the `/cortex/ui` route are gone; `ui.enabled` remains as the switch.
- Requires `laravel/framework` instead of `illuminate/support`, since the package uses form requests, events and queues from the framework.

### Removed

- The TypeScript SDK (`@jayi/cortex-sdk`, `sdk/`), its npm workspace and `sdk:generate`/`sdk:build` scripts, and the `dedoc/scramble` dev dependency that exported its OpenAPI spec.
- The `cortex-assets` publish tag and the empty `public/` directory it published. The dashboard is Blade rendered through Atrium and ships no assets of its own.
- The skeleton `cortex:placeholder` Artisan command and the `cortex::messages.placeholder` translation.

### Fixed

- Agents calling an MCP tool whose `handle()` type-hints its own `Laravel\Mcp\Request` subclass now pass their arguments to it. Previously laravel/ai's `McpServerTool` bound the arguments only as the base request, so such tools, including Cortex's own MCP tools, received an empty request and failed validation.


## [v0.1.0](https://github.com/jayi/cortex/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
