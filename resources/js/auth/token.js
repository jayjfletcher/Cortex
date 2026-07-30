import config from '../config';

async function bearerToken(refresh = false) {
    if (typeof window.CortexToken === 'function') {
        return await window.CortexToken(refresh);
    }

    return config.auth.token;
}

/**
 * Bearer-token auth: the token comes from the server-side resolver
 * (config.auth.token) or, when defined, the window.CortexToken hook —
 * an async function receiving `refresh` and returning a token.
 */
export default {
    async headers(refresh = false) {
        const token = await bearerToken(refresh);

        return token ? { Authorization: `Bearer ${token}` } : {};
    },

    retriesOn401: () => typeof window.CortexToken === 'function',
};
