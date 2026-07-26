import { createRouter, createWebHistory } from 'vue-router';
import config from './config';
import PromptsIndex from './views/prompts/PromptsIndex.vue';
import PromptForm from './views/prompts/PromptForm.vue';
import PromptShow from './views/prompts/PromptShow.vue';
import AgentsIndex from './views/agents/AgentsIndex.vue';
import AgentForm from './views/agents/AgentForm.vue';
import RunAgent from './views/RunAgent.vue';
import ToolsIndex from './views/ToolsIndex.vue';

export default createRouter({
    history: createWebHistory(config.basePath),
    routes: [
        { path: '/', redirect: '/prompts' },
        { path: '/prompts', name: 'prompts.index', component: PromptsIndex },
        { path: '/prompts/create', name: 'prompts.create', component: PromptForm },
        { path: '/prompts/:slug', name: 'prompts.show', component: PromptShow, props: true },
        { path: '/prompts/:slug/edit', name: 'prompts.edit', component: PromptForm, props: true },
        { path: '/agents', name: 'agents.index', component: AgentsIndex },
        { path: '/agents/create', name: 'agents.create', component: AgentForm },
        { path: '/agents/:slug/edit', name: 'agents.edit', component: AgentForm, props: true },
        { path: '/run', name: 'run', component: RunAgent },
        { path: '/tools', name: 'tools.index', component: ToolsIndex },
        { path: '/:pathMatch(.*)*', redirect: '/prompts' },
    ],
});
