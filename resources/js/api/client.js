import config from '../config';

export class ApiError extends Error {
    constructor(status, message, errors = {}) {
        super(message);
        this.status = status;
        this.errors = errors;
    }
}

function xsrfTokenFromCookie() {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : null;
}

async function bearerToken(refresh = false) {
    if (typeof window.CortexToken === 'function') {
        return await window.CortexToken(refresh);
    }

    return config.auth.token;
}

async function authHeaders(refresh = false) {
    if (config.auth.mode === 'token') {
        const token = await bearerToken(refresh);

        return token ? { Authorization: `Bearer ${token}` } : {};
    }

    const xsrf = xsrfTokenFromCookie();

    if (xsrf) {
        return { 'X-XSRF-TOKEN': xsrf };
    }

    return config.csrfToken ? { 'X-CSRF-TOKEN': config.csrfToken } : {};
}

async function send(method, path, { query, body } = {}, retrying = false) {
    const url = new URL(config.apiBase + path, window.location.origin);

    for (const [key, value] of Object.entries(query ?? {})) {
        if (value !== null && value !== undefined && value !== '') {
            url.searchParams.set(key, value);
        }
    }

    const response = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            ...(body ? { 'Content-Type': 'application/json' } : {}),
            ...(await authHeaders(retrying)),
        },
        body: body ? JSON.stringify(body) : undefined,
    });

    if (response.status === 401 && config.auth.mode === 'token' && !retrying && typeof window.CortexToken === 'function') {
        return send(method, path, { query, body }, true);
    }

    if (response.status === 204) {
        return null;
    }

    const payload = await response.json().catch(() => null);

    if (!response.ok) {
        throw new ApiError(
            response.status,
            payload?.message ?? `Request failed with status ${response.status}.`,
            payload?.errors ?? {},
        );
    }

    return payload;
}

export default {
    get: (path, query) => send('GET', path, { query }),
    post: (path, body) => send('POST', path, { body }),
    patch: (path, body) => send('PATCH', path, { body }),
    delete: (path) => send('DELETE', path),
};
