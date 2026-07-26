<template>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">Cortex</div>
            <nav>
                <router-link :to="{ name: 'prompts.index' }">Prompts</router-link>
                <router-link :to="{ name: 'agents.index' }">Agents</router-link>
                <router-link :to="{ name: 'run' }">Run</router-link>
                <router-link :to="{ name: 'tools.index' }">Tools</router-link>
            </nav>
        </aside>
        <main class="content">
            <slot />
        </main>
    </div>
</template>
