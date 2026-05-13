<script>
    import { useForm } from '@inertiajs/svelte';
    import AdminLayout from '../../../Components/AdminLayout.svelte';

    export let genres;

    const deleteForm = useForm({});
    const deleteGenre = (id) => {
        if (confirm('Delete this genre?')) $deleteForm.delete(`/admin/genres/${id}`);
    };
</script>

<AdminLayout>
    <div class="page-header">
        <h1>Genres</h1>
        <a href="/admin/genres/create" class="btn">+ New Genre</a>
    </div>

    <table class="data-table">
        <thead><tr><th>Name</th><th>Description</th><th>Books</th><th>Actions</th></tr></thead>
        <tbody>
            {#each genres.data as genre (genre.id)}
                <tr>
                    <td>{genre.name}</td>
                    <td>{genre.description ?? '—'}</td>
                    <td>{genre.books_count}</td>
                    <td class="actions">
                        <a href="/admin/genres/{genre.id}/edit" class="btn-sm">Edit</a>
                        <button class="btn-sm danger" on:click={() => deleteGenre(genre.id)}>Delete</button>
                    </td>
                </tr>
            {/each}
        </tbody>
    </table>
</AdminLayout>

<style>
    .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
    h1 { font-size: 20px; margin: 0; }
    .data-table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 12px; overflow: hidden; }
    .data-table th, .data-table td { padding: 10px 14px; text-align: left; font-size: 13px; border-bottom: 1px solid #f0f0f0; }
    .data-table th { background: #f9fafb; font-weight: 600; color: #555; }
    .actions { display: flex; gap: 6px; }
    .btn { padding: 7px 14px; background: #000; color: #fff; border-radius: 8px; text-decoration: none; font-size: 13px; border: none; cursor: pointer; }
    .btn-sm { padding: 4px 10px; background: #f3f4f6; color: #333; border-radius: 6px; text-decoration: none; font-size: 12px; border: none; cursor: pointer; }
    .btn-sm.danger { background: #fee2e2; color: #dc2626; }
</style>
