<script>
    import { page, inertia } from '@inertiajs/svelte';

    const currentRoute = $page.props.currentRouteName;
</script>

<div class="admin-shell">
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="/" use:inertia class="logo">{$page.props.appName}</a>
            <span class="admin-label">Admin</span>
        </div>

        <nav class="sidebar-nav">
            <a href="/admin" use:inertia class="nav-link" class:active={currentRoute === 'admin.dashboard'}>Dashboard</a>
            <a href="/admin/books" use:inertia class="nav-link" class:active={currentRoute?.startsWith('admin.books')}>Books</a>
            <a href="/admin/authors" use:inertia class="nav-link" class:active={currentRoute?.startsWith('admin.authors')}>Authors</a>
            <a href="/admin/genres" use:inertia class="nav-link" class:active={currentRoute?.startsWith('admin.genres')}>Genres</a>
            <a href="/admin/users" use:inertia class="nav-link" class:active={currentRoute?.startsWith('admin.users')}>Users</a>
            <a href="/admin/borrowings" use:inertia class="nav-link" class:active={currentRoute === 'admin.borrowings.index'}>Borrowings</a>
            <a href="/admin/reports" use:inertia class="nav-link" class:active={currentRoute === 'admin.reports.index'}>Reports</a>
        </nav>

        <div class="sidebar-footer">
            <a href="/" use:inertia class="nav-link">← Public site</a>
        </div>
    </aside>

    <main class="admin-main">
        <slot />
    </main>
</div>

<style>
    .admin-shell {
        display: flex;
        min-height: 100vh;
    }

    .sidebar {
        width: 220px;
        background: #111;
        color: #fff;
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        z-index: 50;
    }

    .sidebar-header {
        padding: 20px 20px 10px;
        border-bottom: 1px solid #222;
    }

    .logo {
        font-size: 18px;
        font-weight: 700;
        color: #fff;
        text-decoration: none;
        display: block;
    }

    .admin-label {
        font-size: 11px;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .sidebar-nav {
        flex: 1;
        padding: 16px 0;
        display: flex;
        flex-direction: column;
    }

    .nav-link {
        padding: 10px 20px;
        color: #aaa;
        text-decoration: none;
        font-size: 14px;
        transition: background 0.1s, color 0.1s;
    }

    .nav-link:hover { background: #1f1f1f; color: #fff; }
    .nav-link.active { background: #1f1f1f; color: #fff; font-weight: 600; }

    .sidebar-footer {
        padding: 16px 0;
        border-top: 1px solid #222;
    }

    .admin-main {
        margin-left: 220px;
        flex: 1;
        padding: 30px;
        background: #f9fafb;
        min-height: 100vh;
    }
</style>
