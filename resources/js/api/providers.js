import { sdk, unwrap } from './client';

export default {
    list: () => unwrap(sdk.GET('/cortex/providers')),
};
