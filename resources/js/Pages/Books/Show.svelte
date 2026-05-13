<script>
    import Layout from '../../Components/Layout.svelte';

    export let book;
    export let averageRating;
    export let availabilityLabel;
    export let userBorrowing;
    export let userWaitlistPosition;

    let activeImage = book.images && book.images.length > 0 ? book.images[0] : null;

    const badgeClass = (label) => {
        if (label === 'Available') return 'badge badge-available';
        if (label === 'Borrowed') return 'badge badge-borrowed';
        if (label === 'On Waitlist') return 'badge badge-waitlist';
        return 'badge badge-other';
    };

    const stars = (rating) => {
        const full = Math.floor(rating);
        const half = rating - full >= 0.5;
        return { full, half, empty: 5 - full - (half ? 1 : 0) };
    };

    $: ratingStars = stars(averageRating);
</script>

<Layout>
    <div class="container">
        <a href="/books" class="back-link">← Back to catalogue</a>

        <div class="book-layout">
            <!-- Gallery -->
            <div class="gallery">
                <div class="main-image">
                    {#if activeImage}
                        <img src="/storage/{activeImage.path}" alt={activeImage.description || book.title} />
                    {:else}
                        <div class="no-image">
                            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5">
                                <path d="M3 19a9 9 0 0 1 9 0a9 9 0 0 1 9 0"/>
                                <path d="M3 6a9 9 0 0 1 9 0a9 9 0 0 1 9 0"/>
                                <line x1="3" y1="6" x2="3" y2="19"/>
                                <line x1="12" y1="6" x2="12" y2="19"/>
                                <line x1="21" y1="6" x2="21" y2="19"/>
                            </svg>
                            <p>No image</p>
                        </div>
                    {/if}
                </div>
                {#if book.images && book.images.length > 1}
                    <div class="thumbnails">
                        {#each book.images as img}
                            <button
                                class="thumb {activeImage && activeImage.id === img.id ? 'active' : ''}"
                                on:click={() => activeImage = img}
                            >
                                <img src="/storage/{img.path}" alt={img.description || ''} />
                            </button>
                        {/each}
                    </div>
                {/if}
            </div>

            <!-- Info -->
            <div class="info">
                <h1>{book.title}</h1>

                {#if book.author}
                    <p class="author-name">by <a href="#author">{book.author.name}</a></p>
                {/if}

                <!-- Rating -->
                <div class="rating-row">
                    <span class="stars">
                        {#each Array(ratingStars.full) as _}★{/each}{#if ratingStars.half}½{/if}{#each Array(ratingStars.empty) as _}☆{/each}
                    </span>
                    <span class="rating-value">{averageRating > 0 ? averageRating : 'No ratings yet'}</span>
                </div>

                <!-- Availability -->
                <div class="availability-row">
                    <span class={badgeClass(availabilityLabel)}>{availabilityLabel}</span>
                    {#if userBorrowing}
                        <span class="user-status">You have this book — due {new Date(userBorrowing.due_date).toLocaleDateString()}</span>
                    {:else if userWaitlistPosition}
                        <span class="user-status">You are #{userWaitlistPosition} in the waitlist</span>
                    {/if}
                </div>

                <!-- Action button placeholder (Phase 5) -->
                {#if !userBorrowing && !userWaitlistPosition}
                    {#if availabilityLabel === 'Available'}
                        <button class="action-btn primary" disabled>Borrow (coming soon)</button>
                    {:else if availabilityLabel === 'Borrowed' || availabilityLabel === 'On Waitlist'}
                        <button class="action-btn secondary" disabled>Join Waitlist (coming soon)</button>
                    {/if}
                {/if}

                <!-- Genres -->
                {#if book.genres && book.genres.length > 0}
                    <div class="genres">
                        {#each book.genres as genre}
                            <a href="/books?genre={genre.id}" class="genre-tag">{genre.name}</a>
                        {/each}
                    </div>
                {/if}

                <!-- Metadata -->
                <table class="meta-table">
                    <tbody>
                        <tr><th>Publisher</th><td>{book.publisher}</td></tr>
                        <tr><th>Year</th><td>{book.publication_year}</td></tr>
                        <tr><th>Language</th><td>{book.language}</td></tr>
                        <tr><th>Pages</th><td>{book.page_number}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Synopsis -->
        <section class="synopsis">
            <h3>Synopsis</h3>
            <p>{book.synopsis}</p>
        </section>

        <!-- Author card -->
        {#if book.author}
            <section class="author-section" id="author">
                <h3>About the Author</h3>
                <div class="author-card">
                    {#if book.author.photo}
                        <img class="author-photo" src="/storage/{book.author.photo}" alt={book.author.name} />
                    {/if}
                    <div>
                        <p class="author-card-name">{book.author.name}</p>
                        {#if book.author.about}
                            <p class="author-about">{book.author.about}</p>
                        {/if}
                    </div>
                </div>
            </section>
        {/if}

        <!-- Reviews -->
        <section class="reviews-section">
            <h3>Reviews ({book.reviews ? book.reviews.length : 0})</h3>
            {#if book.reviews && book.reviews.length > 0}
                <div class="reviews-list">
                    {#each book.reviews as review}
                        <div class="review-item">
                            <div class="review-header">
                                <span class="reviewer">{review.user ? review.user.name : 'Anonymous'}</span>
                                <span class="review-stars">{'★'.repeat(Math.round(review.rate))}{'☆'.repeat(5 - Math.round(review.rate))}</span>
                                <span class="review-date">{new Date(review.created_at).toLocaleDateString()}</span>
                            </div>
                            {#if review.comment}
                                <p class="review-comment">{review.comment}</p>
                            {/if}
                        </div>
                    {/each}
                </div>
            {:else}
                <p class="no-reviews">No reviews yet. Be the first to review!</p>
            {/if}
        </section>
    </div>
</Layout>

<style>
    .container {
        margin-top: 100px;
        margin-left: 30px;
        margin-right: 30px;
        margin-bottom: 60px;
    }

    .back-link {
        display: inline-block;
        margin-bottom: 20px;
        color: #666;
        text-decoration: none;
        font-size: 14px;
    }

    .back-link:hover { color: #000; }

    .book-layout {
        display: flex;
        gap: 40px;
        align-items: flex-start;
    }

    .gallery { width: 280px; flex-shrink: 0; }

    .main-image {
        width: 100%;
        aspect-ratio: 2/3;
        border-radius: 12px;
        overflow: hidden;
        background: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .main-image img { width: 100%; height: 100%; object-fit: cover; }

    .no-image {
        display: flex;
        flex-direction: column;
        align-items: center;
        color: #bbb;
        font-size: 12px;
    }

    .thumbnails {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 10px;
    }

    .thumb {
        width: 56px;
        height: 56px;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid transparent;
        padding: 0;
        cursor: pointer;
        background: #f5f5f5;
    }

    .thumb.active { border-color: #000; }
    .thumb img { width: 100%; height: 100%; object-fit: cover; }

    .info { flex: 1; }

    h1 { font-size: 26px; margin: 0 0 8px; }

    .author-name { color: #666; margin: 0 0 12px; font-size: 15px; }
    .author-name a { color: inherit; }

    .rating-row { display: flex; align-items: center; gap: 10px; margin-bottom: 12px; }
    .stars { color: #f59e0b; font-size: 18px; letter-spacing: 2px; }
    .rating-value { font-size: 14px; color: #555; }

    .availability-row { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }

    .badge {
        font-size: 12px;
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 500;
    }

    .badge-available { background: #d1fae5; color: #065f46; }
    .badge-borrowed { background: #fee2e2; color: #991b1b; }
    .badge-waitlist { background: #fef3c7; color: #92400e; }
    .badge-other { background: #f3f4f6; color: #374151; }

    .user-status { font-size: 13px; color: #555; }

    .action-btn {
        padding: 10px 24px;
        border-radius: 10px;
        border: none;
        cursor: not-allowed;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 16px;
        opacity: 0.6;
    }

    .action-btn.primary { background: #000; color: #fff; }
    .action-btn.secondary { background: #f3f4f6; color: #333; }

    .genres { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px; }

    .genre-tag {
        font-size: 12px;
        background: #f3f4f6;
        color: #374151;
        padding: 4px 10px;
        border-radius: 6px;
        text-decoration: none;
    }

    .genre-tag:hover { background: #e5e7eb; }

    .meta-table { border-collapse: collapse; font-size: 13px; width: 100%; max-width: 360px; }
    .meta-table th, .meta-table td { padding: 6px 0; text-align: left; }
    .meta-table th { color: #888; width: 100px; font-weight: 500; }

    .synopsis, .author-section, .reviews-section {
        margin-top: 40px;
    }

    .synopsis p { color: #555; line-height: 1.7; }

    .author-card { display: flex; gap: 16px; align-items: flex-start; }
    .author-photo { width: 64px; height: 64px; border-radius: 50%; object-fit: cover; }
    .author-card-name { font-weight: 600; margin: 0 0 4px; }
    .author-about { color: #555; font-size: 14px; margin: 0; }

    .reviews-list { display: flex; flex-direction: column; gap: 16px; }

    .review-item {
        border: 1px solid #e5e5e5;
        border-radius: 10px;
        padding: 14px 16px;
    }

    .review-header { display: flex; align-items: center; gap: 12px; margin-bottom: 6px; flex-wrap: wrap; }
    .reviewer { font-weight: 600; font-size: 14px; }
    .review-stars { color: #f59e0b; font-size: 14px; }
    .review-date { color: #aaa; font-size: 12px; margin-left: auto; }
    .review-comment { color: #555; font-size: 14px; margin: 0; }

    .no-reviews { color: #999; font-size: 14px; }

    @media (min-width: 720px) {
        .container { margin-left: 200px; margin-right: 200px; }
    }

    @media (max-width: 640px) {
        .book-layout { flex-direction: column; }
        .gallery { width: 100%; max-width: 300px; margin: 0 auto; }
        .container { margin-bottom: 80px; }
    }
</style>
