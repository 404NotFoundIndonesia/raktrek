<script>
    import { useForm, page } from '@inertiajs/svelte';
    import Layout from '../../Components/Layout.svelte';

    export let waitlists;

    $: flash = $page.props.flash || {};

    const cancelForms = {};
    const getCancelForm = (id) => {
        if (!cancelForms[id]) cancelForms[id] = useForm({});
        return cancelForms[id];
    };
    const cancel = (id) => $getCancelForm(id).delete(`/waitlists/${id}`);

    const formatDate = (d) => d ? new Date(d).toLocaleDateString() : '—';
</script>

<Layout>
    <div class="container">
        <h2>My Waitlists</h2>

        {#if flash.success}
            <div class="alert alert-success">{flash.success}</div>
        {/if}

        {#if waitlists.length === 0}
            <div class="empty">
                <p>Not on any waitlist.</p>
                <a href="/books">Browse books →</a>
            </div>
        {:else}
            <div class="list">
                {#each waitlists as entry (entry.id)}
                    <div class="item {entry.is_expired ? 'expired' : ''}">
                        <div class="book-info">
                            {#if entry.book && entry.book.images && entry.book.images.length > 0}
                                <img class="cover" src="/storage/{entry.book.images[0].path}" alt={entry.book.title} />
                            {:else}
                                <div class="cover-placeholder"></div>
                            {/if}
                            <div>
                                <p class="title">
                                    <a href="/books/{entry.book_id}">{entry.book ? entry.book.title : 'Unknown'}</a>
                                </p>
                                {#if entry.book && entry.book.author}
                                    <p class="author">{entry.book.author.name}</p>
                                {/if}
                            </div>
                        </div>

                        <div class="meta">
                            <div class="meta-row">
                                <span class="label">Position</span>
                                <span class="value">#{entry.queue_position}</span>
                            </div>
                            <div class="meta-row">
                                <span class="label">Expires</span>
                                <span class="value {entry.is_expired ? 'text-danger' : ''}">
                                    {formatDate(entry.finish_date)}
                                    {#if entry.is_expired}<span class="expired-tag">Expired</span>{/if}
                                </span>
                            </div>
                        </div>

                        <div class="actions">
                            <button class="btn btn-danger" on:click={() => cancel(entry.id)}>Cancel</button>
                        </div>
                    </div>
                {/each}
            </div>
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

    .item.expired { border-color: #fca5a5; background: #fff5f5; opacity: 0.8; }

    .book-info { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 200px; }

    .cover { width: 48px; height: 64px; object-fit: cover; border-radius: 6px; flex-shrink: 0; }
    .cover-placeholder { width: 48px; height: 64px; background: #f3f4f6; border-radius: 6px; flex-shrink: 0; }

    .title { margin: 0 0 4px; font-weight: 600; font-size: 14px; }
    .title a { text-decoration: none; color: inherit; }
    .title a:hover { text-decoration: underline; }
    .author { margin: 0; font-size: 12px; color: #666; }

    .meta { display: flex; flex-direction: column; gap: 4px; min-width: 160px; }
    .meta-row { display: flex; gap: 8px; font-size: 13px; }
    .label { color: #888; width: 60px; }
    .value { color: #333; }
    .text-danger { color: #dc2626; font-weight: 600; }

    .expired-tag {
        display: inline-block;
        background: #fee2e2;
        color: #991b1b;
        font-size: 10px;
        padding: 1px 6px;
        border-radius: 4px;
        margin-left: 4px;
        font-weight: 500;
    }

    .actions { display: flex; gap: 8px; }

    .btn {
        padding: 7px 16px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        font-size: 13px;
        font-weight: 500;
    }
    .btn-danger { background: #fee2e2; color: #991b1b; }
    .btn-danger:hover { background: #fecaca; }

    @media (min-width: 720px) {
        .container { margin-left: 200px; margin-right: 200px; }
    }
    @media (max-width: 480px) {
        .container { margin-bottom: 80px; }
        .item { flex-direction: column; align-items: flex-start; }
    }
</style>
