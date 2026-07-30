import config from '../config';

const TOKENS_KEY = 'cortex:oauth:tokens';
const PENDING_KEY = 'cortex:oauth:pending';

function base64UrlEncode(bytes) {
    return btoa(String.fromCharCode(...bytes))
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=+$/, '');
}

function randomString(bytes = 48) {
    return base64UrlEncode(crypto.getRandomValues(new Uint8Array(bytes)));
}

async function codeChallenge(verifier) {
    const digest = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(verifier));

    return base64UrlEncode(new Uint8Array(digest));
}

function redirectUri() {
    return new URL(config.basePath, window.location.origin).toString();
}

function storedTokens() {
    try {
        return JSON.parse(sessionStorage.getItem(TOKENS_KEY));
    } catch {
        return null;
    }
}

function storeTokens(payload) {
    sessionStorage.setItem(
        TOKENS_KEY,
        JSON.stringify({
            access_token: payload.access_token,
            refresh_token: payload.refresh_token ?? null,
            // Refresh a minute before actual expiry.
            expires_at: Date.now() + (payload.expires_in ?? 3600) * 1000 - 60_000,
        }),
    );
}

async function requestTokens(body) {
    const response = await fetch(config.auth.oauth.tokenUrl, {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ client_id: config.auth.oauth.clientId, ...body }),
    });

    const payload = await response.json().catch(() => null);

    if (!response.ok || !payload?.access_token) {
        throw new Error(payload?.error_description ?? 'OAuth token request failed.');
    }

    storeTokens(payload);

    return payload.access_token;
}

function redirectToAuthorize() {
    const verifier = randomString();
    const state = randomString(24);

    sessionStorage.setItem(
        PENDING_KEY,
        JSON.stringify({
            verifier,
            state,
            returnTo: window.location.pathname + window.location.search,
        }),
    );

    return codeChallenge(verifier).then((challenge) => {
        const url = new URL(config.auth.oauth.authorizeUrl, window.location.origin);

        url.searchParams.set('response_type', 'code');
        url.searchParams.set('client_id', config.auth.oauth.clientId);
        url.searchParams.set('redirect_uri', redirectUri());
        url.searchParams.set('scope', (config.auth.oauth.scopes ?? []).join(' '));
        url.searchParams.set('state', state);
        url.searchParams.set('code_challenge', challenge);
        url.searchParams.set('code_challenge_method', 'S256');

        window.location.assign(url);

        // Navigation is underway; never resolve so no request fires with a stale token.
        return new Promise(() => {});
    });
}

/**
 * Complete the authorization-code exchange when the page URL carries a
 * callback. Runs before the app mounts so the router never sees the
 * code/state parameters.
 */
async function handleOauthCallback() {
    const params = new URLSearchParams(window.location.search);
    const code = params.get('code');

    if (!code) {
        return;
    }

    let pending = null;

    try {
        pending = JSON.parse(sessionStorage.getItem(PENDING_KEY));
    } catch {
        pending = null;
    }

    sessionStorage.removeItem(PENDING_KEY);

    if (!pending || params.get('state') !== pending.state) {
        return;
    }

    await requestTokens({
        grant_type: 'authorization_code',
        redirect_uri: redirectUri(),
        code_verifier: pending.verifier,
        code,
    });

    window.history.replaceState({}, '', pending.returnTo || config.basePath);
}

/**
 * A valid access token: cached until near expiry, renewed via the refresh
 * grant when possible, otherwise re-acquired through the authorize redirect.
 */
async function oauthToken(refresh = false) {
    const tokens = storedTokens();

    if (tokens && !refresh && Date.now() < tokens.expires_at) {
        return tokens.access_token;
    }

    if (tokens?.refresh_token) {
        try {
            return await requestTokens({
                grant_type: 'refresh_token',
                refresh_token: tokens.refresh_token,
            });
        } catch {
            sessionStorage.removeItem(TOKENS_KEY);
        }
    }

    return redirectToAuthorize();
}

/**
 * Authorization-code + PKCE against the configured endpoints, as a public
 * client. Tokens live in sessionStorage; expired tokens renew through the
 * refresh grant and fall back to a full authorize redirect.
 */
export default {
    boot: handleOauthCallback,

    async headers(refresh = false) {
        const token = await oauthToken(refresh);

        return token ? { Authorization: `Bearer ${token}` } : {};
    },

    retriesOn401: () => true,
};
