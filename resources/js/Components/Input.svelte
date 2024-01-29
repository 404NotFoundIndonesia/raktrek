<script>
    export let type = 'text';
    export let value = '';
    export let placeholder = '';

    const defaultType = type;

	const typeWorkaround = node => node.type = type
    const toggleShowPassword = () => {
        type = type === 'text' ? 'password' : 'text';
    }
</script>

<div class="wrapper">
    <input use:typeWorkaround {...{ type }} bind:value placeholder={placeholder} />
    <slot />

    {#if defaultType === 'password'}
    <div aria-label="toggle show password" aria-hidden="true" on:click={toggleShowPassword}>
        {#if type === 'password'}
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#2c3e50" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
            <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
          </svg>
        {:else}
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-eye-closed" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#2c3e50" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M21 9c-2.4 2.667 -5.4 4 -9 4c-3.6 0 -6.6 -1.333 -9 -4" />
            <path d="M3 15l2.5 -3.8" />
            <path d="M21 14.976l-2.492 -3.776" />
            <path d="M9 17l.5 -4" />
            <path d="M15 17l-.5 -4" />
        </svg>
        {/if}
    </div>
    {/if}
</div>

<style>
    input {
        min-width: 300px;
        padding: 10px 15px;
        border-radius: 15px;
        border: 1px solid #070707;
    }

    .wrapper {
        position: relative;
        margin-bottom: 15px;
    }

    .wrapper > div {
        position: absolute;
        z-index: 50;
        top: 25%;
        right: 15px;
    }

    ::placeholder {
        font-size: 12px;
        color: #b6b6b6;
    }
</style>

