const defaults = {
    apiBase: '/cortex',
    basePath: '/cortex/ui',
    auth: { mode: 'session', token: null },
    csrfToken: null,
};

const config = { ...defaults, ...(window.CortexConfig ?? {}) };

config.auth = { ...defaults.auth, ...(config.auth ?? {}) };

export default config;
