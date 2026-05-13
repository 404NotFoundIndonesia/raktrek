<script>
    import { useForm } from '@inertiajs/svelte';
    import AdminLayout from '../../../Components/AdminLayout.svelte';

    export let book;
    export let authors;
    export let genres;

    const form = useForm({
        title: book.title, author_id: book.author_id ?? '',
        synopsis: book.synopsis ?? '', publisher: book.publisher ?? '',
        publication_year: book.publication_year ?? '', language: book.language ?? '',
        page_number: book.page_number ?? '', availability: book.availability,
        genres: book.genres ? book.genres.map(g => g.id) : [],
        images: [], image_descriptions: [],
    });

    const submit = () => $form.put(`/admin/books/${book.id}`, { forceFormData: true });

    const toggleGenre = (id) => {
        if ($form.genres.includes(id)) {
            $form.genres = $form.genres.filter(g => g !== id);
        } else {
            $form.genres = [...$form.genres, id];
        }
    };

    const handleFiles = (e) => {
        $form.images = Array.from(e.target.files);
        $form.image_descriptions = $form.images.map(() => '');
    };
</script>

<AdminLayout>
    <div class="page-header">
        <h1>Edit Book</h1>
        <a href="/admin/books" class="btn-back">← Back</a>
    </div>

    <form class="form-card" on:submit|preventDefault={submit}>
        <div class="field">
            <label>Title *</label>
            <input bind:value={$form.title} class:error={$form.errors.title} />
            {#if $form.errors.title}<p class="err">{$form.errors.title}</p>{/if}
        </div>

        <div class="field">
            <label>Author</label>
            <select bind:value={$form.author_id}>
                <option value="">— None —</option>
                {#each authors as a}<option value={a.id}>{a.name}</option>{/each}
            </select>
        </div>

        <div class="field">
            <label>Synopsis</label>
            <textarea bind:value={$form.synopsis} rows="4"></textarea>
        </div>

        <div class="field-row">
            <div class="field">
                <label>Publisher</label>
                <input bind:value={$form.publisher} />
            </div>
            <div class="field">
                <label>Year</label>
                <input type="number" bind:value={$form.publication_year} />
            </div>
            <div class="field">
                <label>Language</label>
                <input bind:value={$form.language} />
            </div>
            <div class="field">
                <label>Pages</label>
                <input type="number" bind:value={$form.page_number} />
            </div>
        </div>

        <div class="field">
            <label>Availability</label>
            <select bind:value={$form.availability}>
                <option value={1}>Available</option>
                <option value={0}>Borrowed</option>
                <option value={2}>Lost</option>
                <option value={3}>Broken</option>
            </select>
        </div>

        <div class="field">
            <label>Genres</label>
            <div class="genre-list">
                {#each genres as g}
                    <label class="genre-check">
                        <input type="checkbox" checked={$form.genres.includes(g.id)} on:change={() => toggleGenre(g.id)} />
                        {g.name}
                    </label>
                {/each}
            </div>
        </div>

        {#if book.images && book.images.length > 0}
            <div class="field">
                <label>Current Images</label>
                <div class="image-list">
                    {#each book.images as img}
                        <img src="/storage/{img.path}" alt={img.description || ''} class="thumb" />
                    {/each}
                </div>
            </div>
        {/if}

        <div class="field">
            <label>Add Images</label>
            <input type="file" accept="image/*" multiple on:change={handleFiles} />
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
    .form-card { background: #fff; padding: 24px; border-radius: 12px; max-width: 700px; }
    .field { margin-bottom: 16px; }
    .field-row { display: flex; gap: 12px; flex-wrap: wrap; }
    .field-row .field { flex: 1; min-width: 120px; }
    label { display: block; font-size: 13px; font-weight: 500; color: #555; margin-bottom: 4px; }
    input, select, textarea { width: 100%; border: 1px solid #ddd; border-radius: 8px; padding: 8px 10px; font-size: 14px; box-sizing: border-box; }
    input.error { border-color: #dc2626; }
    .err { color: #dc2626; font-size: 12px; margin: 2px 0 0; }
    .genre-list { display: flex; flex-wrap: wrap; gap: 8px; }
    .genre-check { display: flex; align-items: center; gap: 4px; font-size: 13px; cursor: pointer; }
    .genre-check input { width: auto; }
    .image-list { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px; }
    .thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; }
    .btn { padding: 9px 20px; background: #000; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; }
    .btn:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
