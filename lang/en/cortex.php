<?php

declare(strict_types=1);

return [

    'label' => 'Cortex',

    // Navigation
    'prompts' => 'Prompts',
    'agents' => 'Agents',
    'run_agent' => 'Run agent',
    'tools' => 'Tools',
    'servers' => 'Servers',

    // Settings panel
    'settings_label' => 'Cortex',
    'settings_description' => 'Providers, tools and MCP servers available to your agents.',
    'providers' => 'Providers',
    'registered_tools' => 'Registered tools',
    'mcp_servers' => 'MCP servers',
    'cache' => 'Publication cache',
    'enabled' => 'Enabled',
    'disabled' => 'Disabled',

    // Prompts
    'new_prompt' => 'New prompt',
    'edit_prompt' => 'Edit prompt',
    'no_prompts' => 'No prompts yet.',
    'prompt_created' => 'Prompt created.',
    'prompt_updated' => 'Prompt updated.',
    'prompt_deleted' => 'Prompt deleted.',
    'name' => 'Name',
    'slug' => 'Slug',
    'slug_hint' => 'Letters, numbers, dashes and underscores. This cannot be changed later.',
    'description' => 'Description',
    'content' => 'Content',
    'published' => 'Published',
    'updated' => 'Updated',
    'publish_immediately' => 'Publish this version immediately',
    'versioned_hint' => 'Prompt content is versioned. Add a new version from the prompt page.',

    // Versions
    'versions' => 'Versions',
    'version' => 'Version',
    'created' => 'Created',
    'new_version' => 'New version',
    'add_version' => 'Add version',
    'publish' => 'Publish',
    'version_created' => 'Version created.',
    'version_published' => 'Version published.',
    'no_versions' => 'No versions yet.',
    'view' => 'View',
    'hide' => 'Hide',

    // Agents
    'new_agent' => 'New agent',
    'edit_agent' => 'Edit agent',
    'no_agents' => 'No agents yet.',
    'agent_created' => 'Agent created.',
    'agent_updated' => 'Agent updated.',
    'agent_deleted' => 'Agent deleted.',
    'provider' => 'Provider',
    'provider_default' => 'Provider default',
    'model' => 'Model',
    'settings' => 'Settings',
    'temperature' => 'Temperature',
    'max_steps' => 'Max steps',
    'max_tokens' => 'Max tokens',
    'top_p' => 'Top P',
    'prompt' => 'Prompt',
    'no_prompt' => 'No prompt',
    'pinned_version' => 'Pinned prompt version',
    'pinned_version_hint' => 'Leave blank to always use the published version.',
    'sub_agents' => 'Sub-agents',

    // Running
    'select_agent' => 'Select an agent',
    'input' => 'Input',
    'run' => 'Run',
    'running' => 'Running…',
    'response' => 'Response',
    'usage' => 'Usage',

    // Tools and servers
    'schema' => 'Schema',
    'no_tools' => 'No tools are registered. Add them under the cortex.tools config key.',
    'no_servers' => 'No MCP servers are registered. Add them under the cortex.mcp.servers config key.',
    'instructions' => 'Instructions',
    'tool_description' => 'Tool description',
    'server_instructions' => 'Server instructions',
    'live_description' => 'Live description',
    'from_code' => 'From code',
    'override' => 'Override v:version',
    'override_hint' => 'The published override replaces what the class declares in code.',
    'no_override_hint' => 'No override exists, so the description declared in code is used.',
    'remove_override' => 'Remove override',
    'override_removed' => 'Override removed.',
    'back_to_tools' => 'Back to tools',
    'back_to_servers' => 'Back to servers',

    // Shared
    'save' => 'Save',
    'cancel' => 'Cancel',
    'delete' => 'Delete',
    'edit' => 'Edit',
    'actions' => 'Actions',
    'confirm_delete' => 'Are you sure? This cannot be undone.',

];
