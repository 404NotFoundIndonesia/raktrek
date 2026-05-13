<script>
    import AdminLayout from '../../../Components/AdminLayout.svelte';

    export let mostBorrowed;
    export let overdueCount;
    export let overdueBorrowings;
    export let activeMembers;

    const formatDate = (d) => d ? new Date(d).toLocaleDateString() : '—';
</script>

<AdminLayout>
    <div class="page-header">
        <h1>Reports</h1>
    </div>

    <div class="stats-row">
        <div class="stat-card danger">
            <div class="stat-label">Overdue Borrowings</div>
            <div class="stat-value">{overdueCount}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active Members (30d)</div>
            <div class="stat-value">{activeMembers}</div>
        </div>
    </div>

    <section class="section">
        <h2>Most Borrowed (Last 30 Days)</h2>
        {#if mostBorrowed.length === 0}
            <p class="empty">No borrowing data for this period.</p>
        {:else}
            <table class="data-table">
                <thead><tr><th>#</th><th>Title</th><th>Author</th><th>Borrow Count</th></tr></thead>
                <tbody>
                    {#each mostBorrowed as book, i (book.id)}
                        <tr>
                            <td>{i + 1}</td>
                            <td>{book.title}</td>
                            <td>{book.author?.name ?? '—'}</td>
                            <td>{book.borrow_count_30d}</td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        {/if}
    </section>

    <section class="section">
        <h2>Current Overdue Borrowings</h2>
        {#if overdueBorrowings.length === 0}
            <p class="empty">No overdue borrowings.</p>
        {:else}
            <table class="data-table">
                <thead><tr><th>User</th><th>Book</th><th>Due Date</th><th>Days Overdue</th></tr></thead>
                <tbody>
                    {#each overdueBorrowings as b (b.id)}
                        {@const days = Math.floor((Date.now() - new Date(b.due_date)) / 86400000)}
                        <tr>
                            <td>{b.user?.name ?? '—'}</td>
                            <td>{b.book?.title ?? '—'}</td>
                            <td>{formatDate(b.due_date)}</td>
                            <td class="overdue-days">{days}d</td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        {/if}
    </section>
</AdminLayout>

<style>
    .page-header { display: flex; align-items: center; margin-bottom: 20px; }
    h1 { font-size: 20px; margin: 0; }
    h2 { font-size: 16px; margin: 0 0 12px; }
    .stats-row { display: flex; gap: 12px; margin-bottom: 24px; }
    .stat-card { background: #fff; border-radius: 12px; padding: 16px 20px; min-width: 160px; }
    .stat-card.danger { border-left: 4px solid #dc2626; }
    .stat-label { font-size: 12px; color: #888; margin-bottom: 4px; }
    .stat-value { font-size: 28px; font-weight: 700; }
    .section { margin-bottom: 32px; }
    .empty { color: #888; font-size: 13px; }
    .data-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; }
    .data-table th, .data-table td { padding: 10px 14px; text-align: left; font-size: 13px; border-bottom: 1px solid #f0f0f0; }
    .data-table th { background: #f9fafb; font-weight: 600; color: #555; }
    .overdue-days { color: #dc2626; font-weight: 600; }
</style>
