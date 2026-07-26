import client from './client';

export default {
    list: () => client.get('/tools'),
};
