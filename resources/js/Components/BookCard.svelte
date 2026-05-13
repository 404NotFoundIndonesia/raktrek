<script>
    export let book;

    $: coverImage = book.images && book.images.length > 0
        ? '/storage/' + book.images[0].path
        : null;

    $: rating = book.reviews_avg_rate ? parseFloat(book.reviews_avg_rate).toFixed(1) : null;

    const badgeClass = (label) => {
        if (label === 'Available') return 'badge badge-available';
        if (label === 'Borrowed') return 'badge badge-borrowed';
        if (label === 'On Waitlist') return 'badge badge-waitlist';
        return 'badge badge-other';
    };
</script>

<a href="/books/{book.id}" class="card">
    <div class="cover">
        {#if coverImage}
            <img src={coverImage} alt={book.title} />
        {:else}
            <div class="cover-placeholder">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0"/>
                    <path d="M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0"/>
                    <line x1="3" y1="6" x2="3" y2="19"/>
                    <line x1="12" y1="6" x2="12" y2="19"/>
                    <line x1="21" y1="6" x2="21" y2="19"/>
                </svg>
            </div>
        {/if}
    </div>
    <div class="info">
        <p class="title">{book.title}</p>
        {#if book.author}
            <p class="author">{book.author.name}</p>
        {/if}
        <div class="meta">
            {#if book.availability_label}
                <span class={badgeClass(book.availability_label)}>{book.availability_label}</span>
            {/if}
            {#if rating}
                <span class="rating">★ {rating}</span>
            {/if}
        </div>
        {#if book.genres && book.genres.length > 0}
            <div class="genres">
                {#each book.genres.slice(0, 2) as genre}
                    <span class="genre-tag">{genre.name}</span>
                {/each}
            </div>
        {/if}
    </div>
</a>

<style>
    .card {
        display: flex;
        flex-direction: column;
        border: 1px solid #e5e5e5;
        border-radius: 12px;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
        transition: box-shadow 0.2s;
    }

    .card:hover {
        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    }

    .cover {
        width: 100%;
        aspect-ratio: 2/3;
        background: #f5f5f5;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cover-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }

    .info {
        padding: 12px;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .title {
        font-weight: 600;
        font-size: 14px;
        margin: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .author {
        font-size: 12px;
        color: #666;
        margin: 0;
    }

    .meta {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 4px;
    }

    .badge {
        font-size: 11px;
        padding: 2px 8px;
        border-radius: 20px;
        font-weight: 500;
    }

    .badge-available { background: #d1fae5; color: #065f46; }
    .badge-borrowed { background: #fee2e2; color: #991b1b; }
    .badge-waitlist { background: #fef3c7; color: #92400e; }
    .badge-other { background: #f3f4f6; color: #374151; }

    .rating {
        font-size: 12px;
        color: #f59e0b;
        font-weight: 500;
    }

    .genres {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 4px;
    }

    .genre-tag {
        font-size: 10px;
        background: #f3f4f6;
        color: #374151;
        padding: 2px 6px;
        border-radius: 4px;
    }
</style>
