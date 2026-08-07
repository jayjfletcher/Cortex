import { sdk, unwrap } from './client';

export default {
    list: () => unwrap(sdk.GET('/cortex/servers')),
    instructions: (server) => unwrap(sdk.GET('/cortex/servers/{server}/instructions', { params: { path: { server } } })),
    destroyInstructions: (server) => unwrap(sdk.DELETE('/cortex/servers/{server}/instructions', { params: { path: { server } } })),
    instructionVersions: (server) => unwrap(sdk.GET('/cortex/servers/{server}/instructions/versions', { params: { path: { server } } })),
    createInstructionVersion: (server, attributes) => unwrap(sdk.POST('/cortex/servers/{server}/instructions/versions', { params: { path: { server } }, body: attributes })),
    publishInstructionVersion: (server, version) => unwrap(sdk.POST('/cortex/servers/{server}/instructions/versions/{version}/publish', { params: { path: { server, version } } })),
};
