<script>
    import { useForm, page } from '@inertiajs/svelte';
    import AdminLayout from '../../../Components/AdminLayout.svelte';

    export let users;
    export let filters;

    const actionForm = useForm({});

    const deactivate = (id) => {
        if (confirm('Deactivate this user?')) {
            $actionForm.delete(`/admin/users/${id}`);
        }
    };

    const activate = (id) => $actionForm.post(`/admin/users/${id}/activate`);

    const changeRole = (id, role) => {
        $actionForm.patch(`/admin/users/${id}/role`, { data: { role } });
    };

    const currentUserId = $page.props.user?.id;
</script>

<AdminLayout>
    <div class="page-header">
        <h1>Users</h1>
    </div>

    {#if $page.props.flash?.success}
        <div class="alert-success">{$page.props.flash.success}</div>
    {/if}

    <table class="data-table">
        <thead>
            <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
            {#each users.data as user (user.id)}
                <tr class:inactive={!!user.deleted_at}>
                    <td>{user.name}</td>
                    <td>{user.email}</td>
                    <td>
                        {#if !user.deleted_at && user.id !== currentUserId}
                            <select value={user.role} on:change={(e) => changeRole(user.id, e.target.value)} class="role-select">
                                <option value="member">Member</option>
                                <option value="staff">Staff</option>
                            </select>
                        {:else}
                            {user.role}
                        {/if}
                    </td>
                    <td>{user.deleted_at ? 'Inactive' : 'Active'}</td>
                    <td class="actions">
                        {#if !user.deleted_at && user.id !== currentUserId}
                            <button class="btn-sm danger" on:click={() => deactivate(user.id)}>Deactivate</button>
                        {:else if user.deleted_at}
                            <button class="btn-sm" on:click={() => activate(user.id)}>Activate</button>
                        {/if}
                    </td>
                </tr>
            {/each}
        </tbody>
    </table>
</AdminLayout>

<style>
    .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
    h1 { font-size: 20px; margin: 0; }
    .alert-success { background: #d1fae5; color: #065f46; padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
    .data-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; }
    .data-table th, .data-table td { padding: 10px 14px; text-align: left; font-size: 13px; border-bottom: 1px solid #f0f0f0; }
    .data-table th { background: #f9fafb; font-weight: 600; color: #555; }
    tr.inactive { opacity: 0.5; }
    .actions { display: flex; gap: 6px; }
    .btn-sm { padding: 4px 10px; background: #f3f4f6; color: #333; border-radius: 6px; font-size: 12px; border: none; cursor: pointer; }
    .btn-sm.danger { background: #fee2e2; color: #dc2626; }
    .role-select { border: 1px solid #ddd; border-radius: 6px; padding: 3px 6px; font-size: 12px; }
</style>
