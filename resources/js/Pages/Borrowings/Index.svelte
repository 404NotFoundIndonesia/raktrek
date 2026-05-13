<script>
    import { useForm, page } from '@inertiajs/svelte';
    import Layout from '../../Components/Layout.svelte';

    export let borrowings;

    $: flash = $page.props.flash || {};

    const renewForms = {};
    const returnForms = {};

    const getRenewForm = (id) => {
        if (!renewForms[id]) renewForms[id] = useForm({});
        return renewForms[id];
    };

    const getReturnForm = (id) => {
        if (!returnForms[id]) returnForms[id] = useForm({});
        return returnForms[id];
    };

    const renew = (id) => $getRenewForm(id).patch(`/borrowings/${id}/renew`);
    const returnBook = (id) => $getReturnForm(id).patch(`/borrowings/${id}/return`);

    const formatDate = (dateStr) => dateStr ? new Date(dateStr).toLocaleDateString() : '—';
</script>

<Layout>
    <div class="container">
        <h2>My Borrowings</h2>

        {#if flash.success}
            <div class="alert alert-success">{flash.success}</div>
        {/if}

        {#if borrowings.data.length === 0}
            <div class="empty">
                <p>No borrowing history yet.</p>
                <a href="/books">Browse books →</a>
            </div>
        {:else}
            <div class="list">
                {#each borrowings.data as b (b.id)}
                    <div class="item {b.is_overdue ? 'overdue' : ''} {b.return_date ? 'returned' : ''}">
                        <div class="book-info">
                            {#if b.book && b.book.images && b.book.images.length > 0}
                                <img class="cover" src="/storage/{b.book.images[0].path}" alt={b.book.title} />
                            {:else}
                                <div class="cover-placeholder"></div>
                            {/if}
                            <div>
                                <p class="title">
                                    <a href="/books/{b.book_id}">{b.book ? b.book.title : 'Unknown Book'}</a>
                                </p>
                                {#if b.book && b.book.author}
                                    <p class="author">{b.book.author.name}</p>
                                {/if}
                            </div>
                        </div>

                        <div class="dates">
                            <div class="date-row">
                                <span class="label">Due</span>
                                <span class="value {b.is_overdue ? 'text-danger' : ''}">
                                    {formatDate(b.due_date)}
                                    {#if b.is_overdue}<span class="overdue-tag">Overdue</span>{/if}
                                </span>
                            </div>
                            <div class="date-row">
                                <span class="label">Returned</span>
                                <span class="value">{formatDate(b.return_date)}</span>
                            </div>
                        </div>

                        <div class="actions">
                            {#if !b.return_date}
                                <button class="btn btn-secondary" on:click={() => renew(b.id)}>Renew</button>
                                <button class="btn btn-primary" on:click={() => returnBook(b.id)}>Return</button>
                            {:else}
                                <span class="status-returned">Returned</span>
                            {/if}
                        </div>
                    </div>
                {/each}
            </div>

            {#if borrowings.last_page > 1}
                <div class="pagination">
                    {#each Array.from({length: borrowings.last_page}, (_, i) => i + 1) as page}
                        <a
                            href="/my/borrowings?page={page}"
                            class="page-btn {page === borrowings.current_page ? 'active' : ''}"
                        >{page}</a>
                    {/each}
                </div>
            {/if}
        {/if}
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

    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 14px;
    }

    .alert-success { background: #d1fae5; color: #065f46; }

    .empty { text-align: center; padding: 60px 0; color: #999; }
    .empty a { color: #555; }

    .list { display: flex; flex-direction: column; gap: 12px; }

    .item {
        display: flex;
        align-items: center;
        gap: 16px;
        border: 1px solid #e5e5e5;
        border-radius: 12px;
        padding: 16px;
        flex-wrap: wrap;
    }

    .item.overdue { border-color: #fca5a5; background: #fff5f5; }
    .item.returned { opacity: 0.7; }

    .book-info { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 200px; }

    .cover {
        width: 48px;
        height: 64px;
        object-fit: cover;
        border-radius: 6px;
        flex-shrink: 0;
    }

    .cover-placeholder {
        width: 48px;
        height: 64px;
        background: #f3f4f6;
        border-radius: 6px;
        flex-shrink: 0;
    }

    .title { margin: 0 0 4px; font-weight: 600; font-size: 14px; }
    .title a { text-decoration: none; color: inherit; }
    .title a:hover { text-decoration: underline; }
    .author { margin: 0; font-size: 12px; color: #666; }

    .dates { display: flex; flex-direction: column; gap: 4px; min-width: 160px; }

    .date-row { display: flex; gap: 8px; font-size: 13px; }
    .label { color: #888; width: 60px; }
    .value { color: #333; }
    .text-danger { color: #dc2626; font-weight: 600; }

    .overdue-tag {
        display: inline-block;
        background: #fee2e2;
        color: #991b1b;
        font-size: 10px;
        padding: 1px 6px;
        border-radius: 4px;
        margin-left: 4px;
        font-weight: 500;
    }

    .actions { display: flex; gap: 8px; align-items: center; }

    .btn {
        padding: 7px 16px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
    }

    .btn-primary { background: #000; color: #fff; }
    .btn-primary:hover { background: #222; }
    .btn-secondary { background: #f3f4f6; color: #333; }
    .btn-secondary:hover { background: #e5e7eb; }

    .status-returned { font-size: 12px; color: #6b7280; }

    .pagination {
        display: flex;
        gap: 8px;
        justify-content: center;
        margin-top: 24px;
    }

    .page-btn {
        padding: 6px 12px;
        border-radius: 8px;
        border: 1px solid #ddd;
        text-decoration: none;
        color: #333;
        font-size: 13px;
    }

    .page-btn.active { background: #000; color: #fff; border-color: #000; }

    @media (min-width: 720px) {
        .container { margin-left: 200px; margin-right: 200px; }
    }

    @media (max-width: 480px) {
        .container { margin-bottom: 80px; }
        .item { flex-direction: column; align-items: flex-start; }
    }
</style>
