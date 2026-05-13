<script>
    import { useForm } from '@inertiajs/svelte';
    import AdminLayout from '../../../Components/AdminLayout.svelte';

    export let authors;
    export let filters;

    const deleteForm = useForm({});
    const deleteAuthor = (id) => {
        if (confirm('Delete this author?')) $deleteForm.delete(`/admin/authors/${id}`);
    };
</script>

<AdminLayout>
    <div class="page-header">
        <h1>Authors</h1>
        <a href="/admin/authors/create" class="btn">+ New Author</a>
    </div>

    <table class="data-table">
        <thead>
            <tr><th>Name</th><th>Books</th><th>Actions</th></tr>
        </thead>
        <tbody>
            {#each authors.data as author (author.id)}
                <tr>
                    <td>{author.name}</td>
                    <td>{author.books_count}</td>
                    <td class="actions">
                        <a href="/admin/authors/{author.id}/edit" class="btn-sm">Edit</a>
                        <button class="btn-sm danger" on:click={() => deleteAuthor(author.id)}>Delete</button>
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
