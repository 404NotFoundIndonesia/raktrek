<script>
    import { useForm, usePage } from '@inertiajs/svelte';
    import Layout from '../../../Components/Layout.svelte';

    export let book;
    export let queue;

    $: flash = usePage().props.flash || {};

    const removeForms = {};
    const getForm = (id) => {
        if (!removeForms[id]) removeForms[id] = useForm({});
        return removeForms[id];
    };
    const remove = (id) => $getForm(id).delete(`/admin/waitlists/${id}`);

    const formatDate = (d) => d ? new Date(d).toLocaleDateString() : '—';
</script>

<Layout>
    <div class="container">
        <a href="/books/{book.id}" class="back-link">← {book.title}</a>
        <h2>Waitlist — {book.title}</h2>

        {#if flash.success}
            <div class="alert alert-success">{flash.success}</div>
        {/if}

        {#if queue.length === 0}
            <p class="empty">No members in waitlist.</p>
        {:else}
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Member</th>
                        <th>Joined</th>
                        <th>Expires</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    {#each queue as entry (entry.id)}
                        <tr>
                            <td>{entry.queue_position}</td>
                            <td>{entry.user ? entry.user.name : '—'}</td>
                            <td>{formatDate(entry.created_at)}</td>
                            <td class={entry.is_expired ? 'text-danger' : ''}>{formatDate(entry.finish_date)}</td>
                            <td>
                                <button class="btn-remove" on:click={() => remove(entry.id)}>Remove</button>
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
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

    .back-link { display: inline-block; margin-bottom: 12px; color: #666; text-decoration: none; font-size: 14px; }
    .back-link:hover { color: #000; }

    h2 { margin-bottom: 20px; }

    .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
    .alert-success { background: #d1fae5; color: #065f46; }

    .empty { color: #999; }

    .table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .table th, .table td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #e5e5e5; }
    .table th { color: #888; font-weight: 500; }

    .text-danger { color: #dc2626; }

    .btn-remove {
        background: #fee2e2;
        color: #991b1b;
        border: none;
        border-radius: 6px;
        padding: 4px 12px;
        cursor: pointer;
        font-size: 12px;
    }
    .btn-remove:hover { background: #fecaca; }

    @media (min-width: 720px) { .container { margin-left: 200px; margin-right: 200px; } }
    @media (max-width: 480px) { .container { margin-bottom: 80px; } }
</style>
