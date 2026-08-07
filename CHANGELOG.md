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
- MCP server instruction management with immutable versioning and a published-version pointer (`cortex_mcp_instructions`, `cortex_mcp_instruction_versions`): published overrides replace a server's code-declared `#[Instructions]` at runtime.
- `McpServerRegistry` for registering MCP server classes by name via config (`cortex.mcp.servers`) or `Cortex::servers()->register()`; Cortex's own server is always registered as `cortex`.
- `JayI\Cortex\Mcp\Server` base class and `HasVersionedInstructions` trait so any Laravel MCP server can serve its published instruction override.
- REST endpoints under `/cortex/servers` for listing servers and managing instruction overrides, six matching MCP tools on `CortexServer` (now 22 tools), and a Servers section in the dashboard with a versioned instructions editor.


## [v0.1.0](https://github.com/jayi/cortex/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
