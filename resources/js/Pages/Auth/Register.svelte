<script>
    import { useForm } from "@inertiajs/svelte";
    import { page } from '@inertiajs/svelte';
    import Button from "../../Components/Button.svelte";
    import Input from "../../Components/Input.svelte";
    import Layout from "../../Components/Layout.svelte";

    let errorMessage = null;

    const form = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const register = () => $form.post('/register', {
        onError: () => {
            errorMessage = $page.props.errors[0] ?? null;
        }
    });

</script>

<Layout>
    <main>
        <div class="register-card">
            <h2 class="title">Let's start your journey!</h2>

            <Input bind:value={$form.name} placeholder='Name'>
                {#if $form.errors.name}
                <div class="form-error">{$form.errors.name}</div>
                {/if}
            </Input>

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

            <Input bind:value={$form.password_confirmation} type='password' placeholder='Password Confirmation'>
                {#if $form.errors.password_confirmation}
                <div class="form-error">{$form.errors.password_confirmation}</div>
                {/if}
            </Input>

            <Button class="primary" on:click={register}>Register</Button>
            {#if errorMessage}
            <div class="form-error">{errorMessage}</div>
            {/if}

            <p class='register-label'>Already have an account? <a href="/login">Login here</a></p>
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

    .register-card {
        padding: 66px;
        box-shadow: 0 4px 15px 0 rgba(0, 0, 0, 0.09);
        border-radius: 35px;
        display: flex;
        flex-direction: column;
    }

    .register-card .title {
        font-size: 18px;
        font-weight: 500;
        text-align: center;
        margin-bottom: 25px;
    }

    .register-card p.register-label {
        margin-top: 25px;
        font-size: 13px;
        font-weight: 400;
    }

    .register-card p.register-label a {
        color: black;
    }

    @media(max-width: 480px) {
        .register-card {
            box-shadow: none;
        }
    }
</style>
