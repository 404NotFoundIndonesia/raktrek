<script>
    import AdminLayout from '../../Components/AdminLayout.svelte';
    export let stats;
</script>

<AdminLayout>
    <h1>Dashboard</h1>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{stats.total_books}</div>
            <div class="stat-label">Total Books</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{stats.active_borrowings}</div>
            <div class="stat-label">Active Loans</div>
        </div>
        <div class="stat-card overdue">
            <div class="stat-value">{stats.overdue_count}</div>
            <div class="stat-label">Overdue</div>
        </div>
        <div class="stat-card">
            <div class="stat-value">{stats.total_members}</div>
            <div class="stat-label">Members</div>
        </div>
    </div>

    <div class="quick-links">
        <a href="/admin/books/create" class="btn">+ New Book</a>
        <a href="/admin/authors/create" class="btn">+ New Author</a>
        <a href="/admin/genres/create" class="btn">+ New Genre</a>
        <a href="/admin/borrowings" class="btn btn-secondary">View Borrowings</a>
        <a href="/admin/reports" class="btn btn-secondary">Reports</a>
    </div>
</AdminLayout>

<style>
    h1 { font-size: 22px; margin: 0 0 24px; }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 16px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
    }

    .stat-card.overdue .stat-value { color: #dc2626; }

    .stat-value { font-size: 32px; font-weight: 700; margin-bottom: 4px; }
    .stat-label { font-size: 13px; color: #888; }

    .quick-links { display: flex; gap: 10px; flex-wrap: wrap; }

    .btn {
        padding: 8px 16px;
        background: #000;
        color: #fff;
        border-radius: 8px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
    }

    .btn.btn-secondary { background: #f3f4f6; color: #333; }
    .btn:hover { opacity: 0.85; }
</style>
