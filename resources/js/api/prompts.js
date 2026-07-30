import { sdk, unwrap } from './client';

export default {
    list: (page = 1) => unwrap(sdk.GET('/cortex/prompts', { params: { query: { page } } })),
    create: (attributes) => unwrap(sdk.POST('/cortex/prompts', { body: attributes })),
    show: (slug) => unwrap(sdk.GET('/cortex/prompts/{prompt}', { params: { path: { prompt: slug } } })),
    update: (slug, attributes) => unwrap(sdk.PATCH('/cortex/prompts/{prompt}', { params: { path: { prompt: slug } }, body: attributes })),
    destroy: (slug) => unwrap(sdk.DELETE('/cortex/prompts/{prompt}', { params: { path: { prompt: slug } } })),
    versions: (slug, page = 1) => unwrap(sdk.GET('/cortex/prompts/{prompt}/versions', { params: { path: { prompt: slug }, query: { page } } })),
    version: (slug, version) => unwrap(sdk.GET('/cortex/prompts/{prompt}/versions/{version}', { params: { path: { prompt: slug, version } } })),
    createVersion: (slug, attributes) => unwrap(sdk.POST('/cortex/prompts/{prompt}/versions', { params: { path: { prompt: slug } }, body: attributes })),
    publishVersion: (slug, version) => unwrap(sdk.POST('/cortex/prompts/{prompt}/versions/{version}/publish', { params: { path: { prompt: slug, version } } })),
};
