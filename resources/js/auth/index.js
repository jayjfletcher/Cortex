import config from '../config';
import oauth from './oauth';
import session from './session';
import token from './token';

/**
 * Auth driver contract:
 *   headers(refresh)  -> object of request headers (required)
 *   boot()            -> async setup before the app mounts (optional)
 *   retriesOn401()    -> whether a 401 warrants one retry with refresh (optional)
 *
 * Built-in drivers are keyed by the configured auth mode. A host page can
 * bridge its own scheme by setting the mode to 'custom' and defining
 * window.CortexAuth with the same shape before the dashboard script loads.
 */
const drivers = { session, token, oauth };

export function authDriver() {
    if (config.auth.mode === 'custom' && typeof window.CortexAuth === 'object' && window.CortexAuth !== null) {
        return window.CortexAuth;
    }

    return drivers[config.auth.mode] ?? drivers.session;
}

export async function bootAuth() {
    await authDriver().boot?.();
}
