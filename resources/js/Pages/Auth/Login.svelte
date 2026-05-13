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

            <div class="divider"><span>or</span></div>

            <a href="/auth/google" class="google-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Sign in with Google
            </a>

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

    .divider {
        display: flex;
        align-items: center;
        margin: 15px 0;
        color: #aaa;
        font-size: 12px;
    }

    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-top: 1px solid #e5e5e5;
    }

    .divider span {
        padding: 0 10px;
    }

    .google-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 10px 15px;
        border: 1px solid #e5e5e5;
        border-radius: 15px;
        background: white;
        color: #333;
        font-size: 13px;
        text-decoration: none;
        cursor: pointer;
        transition: background 0.2s;
    }

    .google-btn:hover {
        background: #f5f5f5;
    }

    @media(max-width: 480px) {
        .login-card {
            box-shadow: none;
        }
    }
</style>
