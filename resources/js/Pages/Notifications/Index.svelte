<script>
    import { useForm, page } from '@inertiajs/svelte';
    import Layout from '../../Components/Layout.svelte';

    export let notifications;

    const markForm = useForm({});

    const markAsRead = (id) => {
        $markForm.post(`/notifications/${id}/read`, { preserveScroll: true });
    };

    const formatDate = (dateStr) => new Date(dateStr).toLocaleString();
</script>

<Layout>
    <div class="container">
        <h2>Notifications</h2>

        {#if $page.props.flash?.success}
            <div class="alert-success">{$page.props.flash.success}</div>
        {/if}

        {#if notifications.length === 0}
            <p class="empty">No notifications yet.</p>
        {:else}
            <div class="list">
                {#each notifications as n (n.id)}
                    <div class="item {n.read_at ? 'read' : 'unread'}">
                        <div class="item-body">
                            <p class="message">{n.data.message ?? 'Notification'}</p>
                            <p class="meta">{formatDate(n.created_at)}</p>
                        </div>
                        <div class="item-actions">
                            {#if n.data.link}
                                <a href={n.data.link} class="btn-link">View</a>
                            {/if}
                            {#if !n.read_at}
                                <button class="btn-read" on:click={() => markAsRead(n.id)} disabled={$markForm.processing}>
                                    Mark read
                                </button>
                            {:else}
                                <span class="read-label">Read</span>
                            {/if}
                        </div>
                    </div>
                {/each}
            </div>
        {/if}
    </div>
</Layout>

<style>
    .container {
        margin: 100px 30px 60px;
        max-width: 700px;
    }

    h2 { font-size: 22px; margin: 0 0 20px; }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        padding: 10px 14px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 14px;
    }

    .empty { color: #999; font-size: 14px; }

    .list { display: flex; flex-direction: column; gap: 10px; }

    .item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 16px;
        border-radius: 10px;
        border: 1px solid #e5e5e5;
    }

    .item.unread { background: #f0f9ff; border-color: #bae6fd; }
    .item.read { background: #fff; }

    .item-body { flex: 1; }

    .message { font-size: 14px; margin: 0 0 4px; }
    .meta { font-size: 12px; color: #999; margin: 0; }

    .item-actions { display: flex; flex-direction: column; gap: 6px; align-items: flex-end; }

    .btn-link {
        font-size: 12px;
        color: #2563eb;
        text-decoration: none;
        padding: 3px 10px;
        border: 1px solid #bfdbfe;
        border-radius: 6px;
        white-space: nowrap;
    }

    .btn-link:hover { background: #eff6ff; }

    .btn-read {
        font-size: 12px;
        color: #555;
        background: none;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 3px 10px;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn-read:disabled { opacity: 0.5; cursor: not-allowed; }
    .btn-read:hover:not(:disabled) { background: #f3f4f6; }

    .read-label { font-size: 12px; color: #aaa; }

    @media (min-width: 720px) {
        .container { margin-left: 200px; margin-right: 200px; }
    }
</style>
