<script>
    import { useForm, router } from "@inertiajs/svelte";
    import { page } from "@inertiajs/svelte";
    import Button from "../../Components/Button.svelte";
    import Input from "../../Components/Input.svelte";
    import Layout from "../../Components/Layout.svelte";

    export let user;

    const profileForm = useForm({
        name:    user.name    ?? '',
        email:   user.email   ?? '',
        phone:   user.phone   ?? '',
        address: user.address ?? '',
    });

    const passwordForm = useForm({
        current_password:      '',
        password:              '',
        password_confirmation: '',
    });

    const updateProfile = () => $profileForm.put('/profile', {
        preserveScroll: true,
    });

    const changePassword = () => $passwordForm.put('/profile/password', {
        preserveScroll: true,
        onSuccess: () => $passwordForm.reset(),
    });
</script>

<Layout>
    <main>
        <div class="profile-page">

            <section class="card">
                <h2 class="section-title">Profile</h2>

                {#if $page.props.flash?.success}
                <div class="alert-success">{$page.props.flash.success}</div>
                {/if}

                <Input bind:value={$profileForm.name} placeholder="Name">
                    {#if $profileForm.errors.name}
                    <div class="form-error">{$profileForm.errors.name}</div>
                    {/if}
                </Input>

                <Input bind:value={$profileForm.email} placeholder="Email">
                    {#if $profileForm.errors.email}
                    <div class="form-error">{$profileForm.errors.email}</div>
                    {/if}
                </Input>

                <Input bind:value={$profileForm.phone} placeholder="Phone (optional)">
                    {#if $profileForm.errors.phone}
                    <div class="form-error">{$profileForm.errors.phone}</div>
                    {/if}
                </Input>

                <Input bind:value={$profileForm.address} placeholder="Address (optional)">
                    {#if $profileForm.errors.address}
                    <div class="form-error">{$profileForm.errors.address}</div>
                    {/if}
                </Input>

                <Button class="primary" on:click={updateProfile} disabled={$profileForm.processing}>
                    Save Changes
                </Button>
            </section>

            <section class="card">
                <h2 class="section-title">Change Password</h2>

                <Input bind:value={$passwordForm.current_password} type="password" placeholder="Current Password">
                    {#if $passwordForm.errors.current_password}
                    <div class="form-error">{$passwordForm.errors.current_password}</div>
                    {/if}
                </Input>

                <Input bind:value={$passwordForm.password} type="password" placeholder="New Password">
                    {#if $passwordForm.errors.password}
                    <div class="form-error">{$passwordForm.errors.password}</div>
                    {/if}
                </Input>

                <Input bind:value={$passwordForm.password_confirmation} type="password" placeholder="Confirm New Password">
                    {#if $passwordForm.errors.password_confirmation}
                    <div class="form-error">{$passwordForm.errors.password_confirmation}</div>
                    {/if}
                </Input>

                <Button class="primary" on:click={changePassword} disabled={$passwordForm.processing}>
                    Change Password
                </Button>
            </section>

        </div>
    </main>
</Layout>

<style>
    main {
        padding: 80px 20px 20px;
        min-height: 100vh;
    }

    .profile-page {
        max-width: 560px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    .card {
        padding: 40px;
        box-shadow: 0 4px 15px 0 rgba(0, 0, 0, 0.09);
        border-radius: 25px;
        display: flex;
        flex-direction: column;
    }

    .section-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 20px;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border-radius: 10px;
        padding: 10px 15px;
        margin-bottom: 15px;
        font-size: 13px;
    }
</style>
