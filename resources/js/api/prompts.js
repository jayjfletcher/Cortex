import client from './client';

export default {
    list: (page = 1) => client.get('/prompts', { page }),
    create: (attributes) => client.post('/prompts', attributes),
    show: (slug) => client.get(`/prompts/${slug}`),
    update: (slug, attributes) => client.patch(`/prompts/${slug}`, attributes),
    destroy: (slug) => client.delete(`/prompts/${slug}`),
    versions: (slug, page = 1) => client.get(`/prompts/${slug}/versions`, { page }),
    version: (slug, version) => client.get(`/prompts/${slug}/versions/${version}`),
    createVersion: (slug, attributes) => client.post(`/prompts/${slug}/versions`, attributes),
    publishVersion: (slug, version) => client.post(`/prompts/${slug}/versions/${version}/publish`),
};
