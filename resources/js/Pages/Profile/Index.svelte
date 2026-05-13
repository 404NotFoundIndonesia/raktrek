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

    const prefs = user.notification_preferences ?? {};
    const notifForm = useForm({
        book_available_email:    prefs.book_available_email    ?? true,
        due_date_reminder_email: prefs.due_date_reminder_email ?? true,
        overdue_alert_email:     prefs.overdue_alert_email     ?? true,
        new_book_email:          prefs.new_book_email          ?? true,
    });

    const saveNotifPrefs = () => $notifForm.put('/profile/notification-preferences', { preserveScroll: true });
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
                <h2 class="section-title">Notification Preferences</h2>
                <p class="pref-hint">Choose which email notifications you want to receive.</p>

                <label class="toggle-row">
                    <input type="checkbox" bind:checked={$notifForm.book_available_email} />
                    <span>Email when a waitlisted book becomes available</span>
                </label>
                <label class="toggle-row">
                    <input type="checkbox" bind:checked={$notifForm.due_date_reminder_email} />
                    <span>Email reminder 3 days before loan due date</span>
                </label>
                <label class="toggle-row">
                    <input type="checkbox" bind:checked={$notifForm.overdue_alert_email} />
                    <span>Email alert when a loan is overdue</span>
                </label>
                <label class="toggle-row">
                    <input type="checkbox" bind:checked={$notifForm.new_book_email} />
                    <span>Email when a favourite author adds a new book</span>
                </label>

                <Button class="primary" on:click={saveNotifPrefs} disabled={$notifForm.processing}>
                    Save Preferences
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

    .pref-hint { font-size: 13px; color: #888; margin: 0 0 14px; }

    .toggle-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
        cursor: pointer;
        font-size: 14px;
    }

    .toggle-row input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
        accent-color: #000;
    }

    .form-error { color: #dc2626; font-size: 12px; margin: 2px 0 0; }
</style>
