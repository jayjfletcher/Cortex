# Release Notes

## [Unreleased](https://github.com/jayi/cortex/compare/v0.1.0...1.x)

### Added

- Prompt management with immutable versioning and a published-version pointer (`cortex_prompts`, `cortex_prompt_versions`). All Cortex tables use ULID primary keys.
- Agent management: DB-backed agents combining a prompt (published or pinned version), registered tools, provider/model settings, and sub-agents (`cortex_agents`, `cortex_agent_agent`), with cycle protection.
- `ToolRegistry` for registering `Laravel\Ai\Contracts\Tool` classes by name via config (`cortex.tools`) or `Cortex::tools()->register()`.
- Agent execution on the Laravel AI SDK: `Cortex::run($slug, $input)`, `Cortex::agent($slug)`, `POST /cortex/agents/{slug}/run`, and the `run-agent` MCP tool.
- REST API under the configurable `cortex` prefix covering prompts, versions, publish, agents, tools, and run.
- `CortexServer` MCP server with 16 tools at full parity with the API, config-gated web (`Mcp::web`) and local (`Mcp::local`) transports, disabled by default.
- `laravel/ai` (^0.10) and `laravel/mcp` (^0.9) dependencies.


## [v0.1.0](https://github.com/jayi/cortex/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
