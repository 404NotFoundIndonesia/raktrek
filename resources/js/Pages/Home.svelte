<script>
    import Layout from "../Components/Layout.svelte";

</script>
<Layout>
    <div class='container'>
        <h1>Home</h1>
        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Deserunt repudiandae ratione architecto.</p>
        <h3>Home</h3>
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
