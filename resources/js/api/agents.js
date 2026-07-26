import client from './client';

export default {
    list: (page = 1) => client.get('/agents', { page }),
    create: (attributes) => client.post('/agents', attributes),
    show: (slug) => client.get(`/agents/${slug}`),
    update: (slug, attributes) => client.patch(`/agents/${slug}`, attributes),
    destroy: (slug) => client.delete(`/agents/${slug}`),
    run: (slug, input) => client.post(`/agents/${slug}/run`, { input }),
};
