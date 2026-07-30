import { createCortexClient } from '@jayi/cortex-sdk';
import { authDriver } from '../auth';
import config from '../config';

export class ApiError extends Error {
    constructor(status, message, errors = {}) {
        super(message);
        this.status = status;
        this.errors = errors;
    }
}

/**
 * Dispatch a request with the active auth driver's headers, retrying once
 * with refreshed credentials when the driver supports it.
 */
async function authedFetch(input) {
    const request = input instanceof Request ? input : new Request(input);

    const attempt = async (refresh) => {
        const clone = request.clone();

        for (const [key, value] of Object.entries(await authDriver().headers(refresh))) {
            clone.headers.set(key, value);
        }

        return globalThis.fetch(clone, { credentials: 'same-origin' });
    };

    let response = await attempt(false);

    if (response.status === 401 && (authDriver().retriesOn401?.() ?? false)) {
        response = await attempt(true);
    }

    return response;
}

/**
 * The generated OpenAPI client. Spec paths carry the /cortex prefix, so the
 * base URL is the API origin.
 */
export const sdk = createCortexClient({
    baseUrl: new URL(config.apiBase, window.location.origin).origin,
    fetch: authedFetch,
});

/**
 * Convert an openapi-fetch result into the payload-or-throw shape the views
 * consume.
 */
export async function unwrap(promise) {
    const { data, error, response } = await promise;

    if (!response.ok) {
        throw new ApiError(
            response.status,
            error?.message ?? `Request failed with status ${response.status}.`,
            error?.errors ?? {},
        );
    }

    return data ?? null;
}
