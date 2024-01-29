<script>
    import Button from "../Components/Button.svelte";
    import Input from "../Components/Input.svelte";
    import Layout from "../Components/Layout.svelte";

    let searchText = '';

    const submitSearch = () => {

    };

</script>
<Layout>
    <div class='container'>
        <h3>Explore</h3>

        <Input placeholder="Get your book's keyword" bind:value={searchText}>
            <div slot='end-input' style="margin-left: 5px;">
                <Button class='primary' on:click={submitSearch}>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-search" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="#fff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                        <path d="M21 21l-6 -6" />
                    </svg>
                </Button>
            </div>
        </Input>

        Lorem ipsum dolor sit amet consectetur, adipisicing elit. Cumque adipisci natus laboriosam eum exercitationem vitae veniam saepe laborum cupiditate facere ab, iure aspernatur necessitatibus.
    </div>
</Layout>

<style>
    div.container {
        margin-top: 100px;
        margin-left: 30px;
        margin-right: 30px;
    }

    @media(min-width: 720px) {
        div.container {
            margin-left: 200px;
            margin-right: 200px;
        }
    }

    @media(max-width: 480px) {
        div.container {
            margin-bottom: 80px;
        }
    }
</style>
