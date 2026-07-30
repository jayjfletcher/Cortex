import { sdk, unwrap } from './client';

export default {
    list: () => unwrap(sdk.GET('/cortex/tools')),
    description: (tool) => unwrap(sdk.GET('/cortex/tools/{tool}/description', { params: { path: { tool } } })),
    destroyDescription: (tool) => unwrap(sdk.DELETE('/cortex/tools/{tool}/description', { params: { path: { tool } } })),
    descriptionVersions: (tool) => unwrap(sdk.GET('/cortex/tools/{tool}/description/versions', { params: { path: { tool } } })),
    createDescriptionVersion: (tool, attributes) => unwrap(sdk.POST('/cortex/tools/{tool}/description/versions', { params: { path: { tool } }, body: attributes })),
    publishDescriptionVersion: (tool, version) => unwrap(sdk.POST('/cortex/tools/{tool}/description/versions/{version}/publish', { params: { path: { tool, version } } })),
};
