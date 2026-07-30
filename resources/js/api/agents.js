import { sdk, unwrap } from './client';

export default {
    list: (page = 1) => unwrap(sdk.GET('/cortex/agents', { params: { query: { page } } })),
    create: (attributes) => unwrap(sdk.POST('/cortex/agents', { body: attributes })),
    show: (slug) => unwrap(sdk.GET('/cortex/agents/{agent}', { params: { path: { agent: slug } } })),
    update: (slug, attributes) => unwrap(sdk.PATCH('/cortex/agents/{agent}', { params: { path: { agent: slug } }, body: attributes })),
    destroy: (slug) => unwrap(sdk.DELETE('/cortex/agents/{agent}', { params: { path: { agent: slug } } })),
    run: (slug, input) => unwrap(sdk.POST('/cortex/agents/{agent}/run', { params: { path: { agent: slug } }, body: { input } })),
};
