<?php

declare(strict_types=1);

use Illuminate\Testing\Fluent\AssertableJson;
use JayI\Cortex\Mcp\CortexServer;
use JayI\Cortex\Mcp\McpServerRegistry;
use JayI\Cortex\Mcp\Tools\CreateServerInstructionVersionTool;
use JayI\Cortex\Mcp\Tools\DeleteServerInstructionsTool;
use JayI\Cortex\Mcp\Tools\ListServerInstructionVersionsTool;
use JayI\Cortex\Mcp\Tools\ListServersTool;
use JayI\Cortex\Mcp\Tools\PublishServerInstructionVersionTool;
use JayI\Cortex\Mcp\Tools\ShowServerInstructionsTool;
use JayI\Cortex\Models\McpInstruction;
use JayI\Cortex\Tests\Fixtures\EchoServer;

beforeEach(function () {
    app(McpServerRegistry::class)->register('echo', EchoServer::class);
});

it('lists servers in a data envelope with parity to the http payload', function () {
    $http = $this->getJson(route('cortex.servers.index'))->json('data');

    CortexServer::tool(ListServersTool::class)
        ->assertOk()
        ->assertStructuredContent(['data' => $http]);
});

it('creates an instruction version with parity to the http payload', function () {
    CortexServer::tool(CreateServerInstructionVersionTool::class, [
        'server' => 'echo',
        'content' => 'v1 instructions',
        'publish' => true,
    ])->assertOk();

    $http = $this->getJson(route('cortex.servers.instructions.versions.index', ['server' => 'echo']))->json('data.0');

    CortexServer::tool(ShowServerInstructionsTool::class, ['server' => 'echo'])
        ->assertOk()
        ->assertStructuredContent(
            fn (AssertableJson $json) => $json
                ->where('server', 'echo')
                ->where('published_version', $http['version'])
                ->where('published_content', $http['content'])
                ->etc(),
        );
});

it('shows instructions with parity to the http payload', function () {
    CortexServer::tool(CreateServerInstructionVersionTool::class, [
        'server' => 'echo',
        'content' => 'published',
        'publish' => true,
    ])->assertOk();

    $http = $this->getJson(route('cortex.servers.instructions.show', ['server' => 'echo']))->json('data');

    CortexServer::tool(ShowServerInstructionsTool::class, ['server' => 'echo'])
        ->assertOk()
        ->assertStructuredContent($http);
});

it('lists instruction versions newest first with parity to the http payload', function () {
    CortexServer::tool(CreateServerInstructionVersionTool::class, ['server' => 'echo', 'content' => 'v1'])->assertOk();
    CortexServer::tool(CreateServerInstructionVersionTool::class, ['server' => 'echo', 'content' => 'v2'])->assertOk();

    $http = $this->getJson(route('cortex.servers.instructions.versions.index', ['server' => 'echo']))->json('data');

    CortexServer::tool(ListServerInstructionVersionsTool::class, ['server' => 'echo'])
        ->assertOk()
        ->assertStructuredContent(['data' => $http]);
});

it('lists zero instruction versions without erroring', function () {
    McpInstruction::query()->create(['server' => 'echo']);

    CortexServer::tool(ListServerInstructionVersionsTool::class, ['server' => 'echo'])
        ->assertOk()
        ->assertStructuredContent(['data' => []]);
});

it('publishes an instruction version with parity to the http payload', function () {
    CortexServer::tool(CreateServerInstructionVersionTool::class, ['server' => 'echo', 'content' => 'v1'])->assertOk();
    CortexServer::tool(CreateServerInstructionVersionTool::class, ['server' => 'echo', 'content' => 'v2'])->assertOk();

    $mcp = CortexServer::tool(PublishServerInstructionVersionTool::class, ['server' => 'echo', 'version' => 1])
        ->assertOk();

    $http = $this->getJson(route('cortex.servers.instructions.show', ['server' => 'echo']))->json('data');

    $mcp->assertStructuredContent($http);

    expect($http['published_version'])->toBe(1);
});

it('deletes an instruction override', function () {
    CortexServer::tool(CreateServerInstructionVersionTool::class, ['server' => 'echo', 'content' => 'v1', 'publish' => true])
        ->assertOk();

    CortexServer::tool(DeleteServerInstructionsTool::class, ['server' => 'echo'])
        ->assertOk()
        ->assertSee('Server instructions override deleted.');

    expect(McpInstruction::query()->count())->toBe(0);
});

it('errors not found for unregistered servers', function () {
    CortexServer::tool(ShowServerInstructionsTool::class, ['server' => 'missing'])
        ->assertHasErrors(['Not found.']);

    CortexServer::tool(CreateServerInstructionVersionTool::class, ['server' => 'missing', 'content' => 'x'])
        ->assertHasErrors(['Not found.']);
});

it('errors not found when no override exists yet', function () {
    CortexServer::tool(ShowServerInstructionsTool::class, ['server' => 'echo'])
        ->assertHasErrors(['Not found.']);

    CortexServer::tool(DeleteServerInstructionsTool::class, ['server' => 'echo'])
        ->assertHasErrors(['Not found.']);
});

it('validates create instruction version input', function () {
    CortexServer::tool(CreateServerInstructionVersionTool::class, ['server' => 'echo'])
        ->assertHasErrors();
});
