// Fetch every page of a paginated index endpoint (used to fill selectors).
export default async function fetchAllPages(list) {
    const items = [];
    let page = 1;
    let lastPage = 1;

    do {
        const response = await list(page);

        items.push(...response.data);
        lastPage = response.meta?.last_page ?? 1;
        page += 1;
    } while (page <= lastPage);

    return items;
}
