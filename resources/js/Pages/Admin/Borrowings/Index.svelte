<script>
    import { useForm } from '@inertiajs/svelte';
    import AdminLayout from '../../../Components/AdminLayout.svelte';

    export let borrowings;
    export let filter;

    const returnForm = useForm({});
    const markReturned = (id) => {
        if (confirm('Mark this borrowing as returned?')) {
            $returnForm.patch(`/admin/borrowings/${id}/return`);
        }
    };

    const formatDate = (d) => d ? new Date(d).toLocaleDateString() : '—';
    const isOverdue = (b) => !b.return_date && new Date(b.due_date) < new Date();
</script>

<AdminLayout>
    <div class="page-header">
        <h1>Borrowings</h1>
    </div>

    <div class="filter-bar">
        <a href="/admin/borrowings" class:active={!filter}>All</a>
        <a href="/admin/borrowings?filter=active" class:active={filter === 'active'}>Active</a>
        <a href="/admin/borrowings?filter=overdue" class:active={filter === 'overdue'}>Overdue</a>
        <a href="/admin/borrowings?filter=returned" class:active={filter === 'returned'}>Returned</a>
    </div>

    <table class="data-table">
        <thead>
            <tr><th>User</th><th>Book</th><th>Borrowed</th><th>Due</th><th>Returned</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            {#each borrowings.data as b (b.id)}
                <tr class:overdue-row={isOverdue(b)}>
                    <td>{b.user?.name ?? '—'}</td>
                    <td>{b.book?.title ?? '—'}</td>
                    <td>{formatDate(b.created_at)}</td>
                    <td>{formatDate(b.due_date)}</td>
                    <td>{formatDate(b.return_date)}</td>
                    <td>
                        {#if b.return_date}
                            <span class="badge returned">Returned</span>
                        {:else if isOverdue(b)}
                            <span class="badge overdue">Overdue</span>
                        {:else}
                            <span class="badge active">Active</span>
                        {/if}
                    </td>
                    <td class="actions">
                        {#if !b.return_date}
                            <button class="btn-sm" on:click={() => markReturned(b.id)}>Mark Returned</button>
                        {/if}
                    </td>
                </tr>
            {/each}
        </tbody>
    </table>
</AdminLayout>

<style>
    .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    h1 { font-size: 20px; margin: 0; }
    .filter-bar { display: flex; gap: 8px; margin-bottom: 16px; }
    .filter-bar a { padding: 5px 12px; border-radius: 6px; font-size: 13px; text-decoration: none; background: #f3f4f6; color: #555; }
    .filter-bar a.active { background: #000; color: #fff; }
    .data-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; }
    .data-table th, .data-table td { padding: 10px 14px; text-align: left; font-size: 13px; border-bottom: 1px solid #f0f0f0; }
    .data-table th { background: #f9fafb; font-weight: 600; color: #555; }
    tr.overdue-row { background: #fff7f7; }
    .badge { padding: 2px 8px; border-radius: 999px; font-size: 11px; font-weight: 500; }
    .badge.active { background: #d1fae5; color: #065f46; }
    .badge.overdue { background: #fee2e2; color: #dc2626; }
    .badge.returned { background: #f3f4f6; color: #555; }
    .actions { display: flex; gap: 6px; }
    .btn-sm { padding: 4px 10px; background: #f3f4f6; color: #333; border-radius: 6px; font-size: 12px; border: none; cursor: pointer; }
</style>
