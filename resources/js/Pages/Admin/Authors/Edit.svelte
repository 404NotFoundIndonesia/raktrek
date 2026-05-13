<script>
    import { useForm } from '@inertiajs/svelte';
    import AdminLayout from '../../../Components/AdminLayout.svelte';

    export let author;

    const form = useForm({ name: author.name, about: author.about ?? '', photo: null });
    const handlePhoto = (e) => { $form.photo = e.target.files[0] ?? null; };
    const submit = () => $form.put(`/admin/authors/${author.id}`, { forceFormData: true });
</script>

<AdminLayout>
    <div class="page-header">
        <h1>Edit Author</h1>
        <a href="/admin/authors" class="btn-back">← Back</a>
    </div>

    <form class="form-card" on:submit|preventDefault={submit}>
        <div class="field">
            <label>Name *</label>
            <input bind:value={$form.name} class:error={$form.errors.name} />
            {#if $form.errors.name}<p class="err">{$form.errors.name}</p>{/if}
        </div>
        <div class="field">
            <label>About</label>
            <textarea bind:value={$form.about} rows="4"></textarea>
        </div>
        {#if author.photo}
            <div class="field">
                <label>Current Photo</label>
                <img src="/storage/{author.photo}" alt={author.name} class="photo-preview" />
            </div>
        {/if}
        <div class="field">
            <label>New Photo (optional)</label>
            <input type="file" accept="image/*" on:change={handlePhoto} />
        </div>
        <button type="submit" class="btn" disabled={$form.processing}>
            {$form.processing ? 'Saving…' : 'Save Changes'}
        </button>
    </form>
</AdminLayout>

<style>
    .page-header { display: flex; align-items: center; gap: 16px; margin-bottom: 20px; }
    h1 { font-size: 20px; margin: 0; }
    .btn-back { font-size: 13px; color: #666; text-decoration: none; }
    .form-card { background: #fff; padding: 24px; border-radius: 12px; max-width: 560px; }
    .field { margin-bottom: 16px; }
    label { display: block; font-size: 13px; font-weight: 500; color: #555; margin-bottom: 4px; }
    input, textarea { width: 100%; border: 1px solid #ddd; border-radius: 8px; padding: 8px 10px; font-size: 14px; box-sizing: border-box; }
    input.error { border-color: #dc2626; }
    .err { color: #dc2626; font-size: 12px; margin: 2px 0 0; }
    .photo-preview { width: 80px; height: 80px; object-fit: cover; border-radius: 50%; }
    .btn { padding: 9px 20px; background: #000; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; }
    .btn:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
