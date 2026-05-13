<script>
    import { useForm, router } from '@inertiajs/svelte';
    import AdminLayout from '../../../Components/AdminLayout.svelte';

    export let books;
    export let filters;

    let search = filters.search ?? '';

    const applySearch = () => router.get('/admin/books', { search }, { preserveState: true });

    const deleteForm = useForm({});
    const deleteBook = (id) => {
        if (confirm('Soft-delete this book?')) {
            $deleteForm.delete(`/admin/books/${id}`);
        }
    };
</script>

<AdminLayout>
    <div class="page-header">
        <h1>Books</h1>
        <a href="/admin/books/create" class="btn">+ New Book</a>
    </div>

    <form class="search-row" on:submit|preventDefault={applySearch}>
        <input class="search-input" bind:value={search} placeholder="Search by title…" />
        <button type="submit" class="btn">Search</button>
    </form>

    <table class="data-table">
        <thead>
            <tr><th>Title</th><th>Author</th><th>Year</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            {#each books.data as book (book.id)}
                <tr class:deleted={!!book.deleted_at}>
                    <td>{book.title}</td>
                    <td>{book.author?.name ?? '—'}</td>
                    <td>{book.publication_year}</td>
                    <td>{book.deleted_at ? 'Deleted' : book.availability_label}</td>
                    <td class="actions">
                        {#if !book.deleted_at}
                            <a href="/admin/books/{book.id}/edit" class="btn-sm">Edit</a>
                            <button class="btn-sm danger" on:click={() => deleteBook(book.id)}>Delete</button>
                        {/if}
                    </td>
                </tr>
            {/each}
        </tbody>
    </table>

    <div class="pagination">
        {#if books.prev_page_url}
            <a href={books.prev_page_url} class="btn-sm">← Prev</a>
        {/if}
        <span>{books.current_page} / {books.last_page}</span>
        {#if books.next_page_url}
            <a href={books.next_page_url} class="btn-sm">Next →</a>
        {/if}
    </div>
</AdminLayout>

<style>
    .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
    h1 { font-size: 20px; margin: 0; }
    .search-row { display: flex; gap: 8px; margin-bottom: 16px; }
    .search-input { flex: 1; border: 1px solid #ddd; border-radius: 8px; padding: 8px 12px; font-size: 14px; }
    .data-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; }
    .data-table th, .data-table td { padding: 10px 14px; text-align: left; font-size: 13px; border-bottom: 1px solid #f0f0f0; }
    .data-table th { background: #f9fafb; font-weight: 600; color: #555; }
    tr.deleted { opacity: 0.45; }
    .actions { display: flex; gap: 6px; }
    .btn { padding: 7px 14px; background: #000; color: #fff; border-radius: 8px; text-decoration: none; font-size: 13px; border: none; cursor: pointer; }
    .btn-sm { padding: 4px 10px; background: #f3f4f6; color: #333; border-radius: 6px; text-decoration: none; font-size: 12px; border: none; cursor: pointer; }
    .btn-sm.danger { background: #fee2e2; color: #dc2626; }
    .pagination { display: flex; align-items: center; gap: 12px; margin-top: 16px; font-size: 13px; }
</style>
