import config from '../config';

function xsrfTokenFromCookie() {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : null;
}

/**
 * Same-origin session auth: cookies ride along automatically; attach the
 * CSRF token so state-changing requests pass the VerifyCsrfToken middleware.
 */
export default {
    async headers() {
        const xsrf = xsrfTokenFromCookie();

        if (xsrf) {
            return { 'X-XSRF-TOKEN': xsrf };
        }

        return config.csrfToken ? { 'X-CSRF-TOKEN': config.csrfToken } : {};
    },

    retriesOn401: () => false,
};
