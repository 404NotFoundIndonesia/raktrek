<script>
    import { router } from '@inertiajs/svelte';
    import BookCard from '../../Components/BookCard.svelte';
    import Button from '../../Components/Button.svelte';
    import Input from '../../Components/Input.svelte';
    import Layout from '../../Components/Layout.svelte';

    export let books;
    export let genres;
    export let filters;

    let search = filters.search || '';
    let selectedGenre = filters.genre || '';
    let selectedYear = filters.year || '';
    let selectedLanguage = filters.language || '';
    let selectedAvailability = filters.availability ?? '';
    let selectedSort = filters.sort || 'title';

    let debounceTimer;

    const applyFilters = () => {
        router.get('/books', {
            search: search || undefined,
            genre: selectedGenre || undefined,
            year: selectedYear || undefined,
            language: selectedLanguage || undefined,
            availability: selectedAvailability !== '' ? selectedAvailability : undefined,
            sort: selectedSort !== 'title' ? selectedSort : undefined,
        }, {
            preserveState: true,
            replace: true,
        });
    };

    const onSearchInput = () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(applyFilters, 400);
    };

    const clearFilters = () => {
        search = '';
        selectedGenre = '';
        selectedYear = '';
        selectedLanguage = '';
        selectedAvailability = '';
        selectedSort = 'title';
        router.get('/books', {}, { preserveState: true, replace: true });
    };
</script>

<Layout>
    <div class="container">
        <h2>Explore Books</h2>

        <div class="search-bar">
            <Input placeholder="Search by title…" bind:value={search} on:input={onSearchInput}>
                <div slot="end-input" style="margin-left: 5px;">
                    <Button class="primary" on:click={applyFilters}>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                    </Button>
                </div>
            </Input>
        </div>

        <div class="layout">
            <aside class="filters">
                <h4>Filters</h4>

                <label>Genre
                    <select bind:value={selectedGenre} on:change={applyFilters}>
                        <option value="">All</option>
                        {#each genres as genre}
                            <option value={genre.id}>{genre.name}</option>
                        {/each}
                    </select>
                </label>

                <label>Availability
                    <select bind:value={selectedAvailability} on:change={applyFilters}>
                        <option value="">All</option>
                        <option value="1">Available</option>
                        <option value="0">Borrowed</option>
                    </select>
                </label>

                <label>Sort by
                    <select bind:value={selectedSort} on:change={applyFilters}>
                        <option value="title">Title</option>
                        <option value="rating">Rating</option>
                        <option value="year">Year</option>
                    </select>
                </label>

                <button class="clear-btn" on:click={clearFilters}>Clear filters</button>
            </aside>

            <main>
                {#if books.data.length === 0}
                    <div class="empty">No books found.</div>
                {:else}
                    <div class="grid">
                        {#each books.data as book (book.id)}
                            <BookCard {book} />
                        {/each}
                    </div>
                {/if}

                {#if books.last_page > 1}
                    <div class="pagination">
                        {#each Array.from({length: books.last_page}, (_, i) => i + 1) as page}
                            <a
                                href={books.links.find(l => l.label == page)?.url || '#'}
                                class="page-btn {page === books.current_page ? 'active' : ''}"
                            >{page}</a>
                        {/each}
                    </div>
                {/if}
            </main>
        </div>
    </div>
</Layout>

<style>
    .container {
        margin-top: 100px;
        margin-left: 30px;
        margin-right: 30px;
        margin-bottom: 40px;
    }

    h2 { margin-bottom: 20px; }

    .search-bar { margin-bottom: 20px; max-width: 500px; }

    .layout {
        display: flex;
        gap: 24px;
    }

    aside.filters {
        min-width: 180px;
        max-width: 200px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    aside.filters h4 { margin: 0; }

    aside.filters label {
        display: flex;
        flex-direction: column;
        gap: 4px;
        font-size: 13px;
    }

    aside.filters select {
        padding: 6px 10px;
        border-radius: 8px;
        border: 1px solid #ddd;
        font-size: 13px;
    }

    .clear-btn {
        background: none;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 6px 10px;
        cursor: pointer;
        font-size: 12px;
        color: #666;
    }

    .clear-btn:hover { background: #f5f5f5; }

    main { flex: 1; }

    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 16px;
    }

    .empty {
        text-align: center;
        color: #999;
        padding: 60px 0;
    }

    .pagination {
        display: flex;
        gap: 8px;
        justify-content: center;
        margin-top: 32px;
        flex-wrap: wrap;
    }

    .page-btn {
        padding: 6px 12px;
        border-radius: 8px;
        border: 1px solid #ddd;
        text-decoration: none;
        color: #333;
        font-size: 13px;
    }

    .page-btn.active {
        background: #000;
        color: #fff;
        border-color: #000;
    }

    @media (min-width: 720px) {
        .container {
            margin-left: 200px;
            margin-right: 200px;
        }
    }

    @media (max-width: 600px) {
        .layout { flex-direction: column; }
        aside.filters { max-width: 100%; }
        .container { margin-bottom: 80px; }
    }
</style>
