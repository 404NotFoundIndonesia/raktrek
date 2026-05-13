<script>
    import { useForm } from '@inertiajs/svelte';
    import AdminLayout from '../../../Components/AdminLayout.svelte';

    export let genre;
    const form = useForm({ name: genre.name, description: genre.description ?? '' });
    const submit = () => $form.put(`/admin/genres/${genre.id}`);
</script>

<AdminLayout>
    <div class="page-header">
        <h1>Edit Genre</h1>
        <a href="/admin/genres" class="btn-back">← Back</a>
    </div>
    <form class="form-card" on:submit|preventDefault={submit}>
        <div class="field">
            <label>Name *</label>
            <input bind:value={$form.name} class:error={$form.errors.name} />
            {#if $form.errors.name}<p class="err">{$form.errors.name}</p>{/if}
        </div>
        <div class="field">
            <label>Description</label>
            <textarea bind:value={$form.description} rows="3"></textarea>
        </div>
        <button type="submit" class="btn" disabled={$form.processing}>Save</button>
    </form>
</AdminLayout>

<style>
    .page-header { display: flex; align-items: center; gap: 16px; margin-bottom: 20px; }
    h1 { font-size: 20px; margin: 0; }
    .btn-back { font-size: 13px; color: #666; text-decoration: none; }
    .form-card { background: #fff; padding: 24px; border-radius: 12px; max-width: 480px; }
    .field { margin-bottom: 16px; }
    label { display: block; font-size: 13px; font-weight: 500; color: #555; margin-bottom: 4px; }
    input, textarea { width: 100%; border: 1px solid #ddd; border-radius: 8px; padding: 8px 10px; font-size: 14px; box-sizing: border-box; }
    input.error { border-color: #dc2626; }
    .err { color: #dc2626; font-size: 12px; margin: 2px 0 0; }
    .btn { padding: 9px 20px; background: #000; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; }
    .btn:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
