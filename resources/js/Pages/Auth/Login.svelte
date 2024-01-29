<script>
    import { useForm } from "@inertiajs/svelte";
    import { page } from '@inertiajs/svelte';
    import Button from "../../Components/Button.svelte";
    import Input from "../../Components/Input.svelte";
    import Layout from "../../Components/Layout.svelte";

    let errorMessage = null;

    const form = useForm({
        email: '',
        password: '',
    });

    const login = () => $form.post('/login', {
        onError: () => {
            errorMessage = $page.props.errors[0] ?? null;
        }
    });

</script>

<Layout>
    <main>
        <div class="login-card">
            <h2 class="title">Let's continue your journey!</h2>

            <Input bind:value={$form.email} placeholder='Email'>
                {#if $form.errors.email}
                <div class="form-error">{$form.errors.email}</div>
                {/if}
            </Input>

            <Input bind:value={$form.password} type='password' placeholder='Password'>
                {#if $form.errors.password}
                <div class="form-error">{$form.errors.password}</div>
                {/if}
            </Input>

            <Button class="primary" on:click={login}>Login</Button>
            {#if errorMessage}
            <div class="form-error">{errorMessage}</div>
            {/if}

            <p class='register-label'>Don't have an account? <a href="/register">Register here</a></p>
        </div>
    </main>
</Layout>

<style>
    main {
        width: 100%;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .login-card {
        padding: 66px;
        box-shadow: 0 4px 15px 0 rgba(0, 0, 0, 0.09);
        border-radius: 35px;
        display: flex;
        flex-direction: column;
    }

    .login-card .title {
        font-size: 18px;
        font-weight: 500;
        text-align: center;
        margin-bottom: 25px;
    }

    .login-card p.register-label {
        margin-top: 25px;
        font-size: 13px;
        font-weight: 400;
    }

    .login-card p.register-label a {
        color: black;
    }

    @media(max-width: 480px) {
        .login-card {
            box-shadow: none;
        }
    }
</style>
