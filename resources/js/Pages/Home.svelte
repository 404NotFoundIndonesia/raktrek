<script>
    import BookCard from '../Components/BookCard.svelte';
    import Layout from '../Components/Layout.svelte';
    import { usePage } from '@inertiajs/svelte';

    export let recommendations;

    $: user = usePage().props.user;
</script>

<Layout>
    <div class="container">
        <!-- Hero -->
        <section class="hero">
            <h1>Welcome to RakTrek</h1>
            <p>Discover, borrow, and track your reading journey.</p>
            <a href="/books" class="explore-btn">Explore catalogue →</a>
        </section>

        <!-- Recommendations -->
        <section class="recommendations">
            <h2>
                {#if user}Recommended for You{:else}Popular Books{/if}
            </h2>
            {#if recommendations && recommendations.length > 0}
                <div class="books-grid">
                    {#each recommendations as book (book.id)}
                        <BookCard {book} />
                    {/each}
                </div>
            {:else}
                <p class="empty">No books yet. Check back soon!</p>
            {/if}
            <a href="/books" class="see-all">See all books →</a>
        </section>
    </div>
</Layout>

<style>
    .container {
        margin-top: 100px;
        margin-left: 30px;
        margin-right: 30px;
        margin-bottom: 40px;
    }

    .hero {
        text-align: center;
        padding: 60px 0 40px;
    }

    .hero h1 {
        font-size: 36px;
        margin-bottom: 12px;
    }

    .hero p {
        color: #666;
        font-size: 16px;
        margin-bottom: 24px;
    }

    .explore-btn {
        display: inline-block;
        background: #000;
        color: #fff;
        padding: 12px 28px;
        border-radius: 12px;
        text-decoration: none;
        font-size: 15px;
        font-weight: 500;
    }

    .explore-btn:hover { background: #222; }

    .recommendations { margin-top: 20px; }

    .recommendations h2 { margin-bottom: 20px; }

    .books-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 16px;
    }

    .empty { color: #999; }

    .see-all {
        display: inline-block;
        margin-top: 20px;
        color: #555;
        text-decoration: none;
        font-size: 14px;
    }

    .see-all:hover { color: #000; }

    @media (min-width: 720px) {
        .container {
            margin-left: 200px;
            margin-right: 200px;
        }
    }

    @media (max-width: 480px) {
        .container { margin-bottom: 80px; }
        .hero h1 { font-size: 26px; }
    }
</style>
