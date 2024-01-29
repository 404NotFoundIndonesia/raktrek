<script>
    import {page} from '@inertiajs/svelte';

    let openMenu = false;

    const toggleMenu = () => {
        openMenu = !openMenu;
    }
</script>

<header class="title-header">
    {#if $page.props.user != null}
        <button class="burger" class:active={openMenu} on:click={toggleMenu}>
            <div class="burger-bar-top"></div>
            <div class="burger-bar-middle"></div>
            <div class="burger-bar-bottom"></div>
        </button>
    {/if}

    <span>{$page.props.appName}</span>
</header>

<main>
    <slot />
</main>

<style>
    header.title-header {
        position: fixed;
        top: 0;
        left: 0;
        box-shadow: 0 4px 15px 0 rgba(0, 0, 0, 0.09);
        background-color: white;
        padding: 20px 30px;
        border-bottom-right-radius: 35px;
        display: flex;
        align-items: center;
        z-index: 100;
    }

    header.title-header > button.burger {
        margin-right: 10px;
        background-color: black;
        border: none;
        border-radius: 8px;
        width: 38px;
        height: 28px;
        padding: 0;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        align-items: center;
        padding: 7px 0;
    }

    header.title-header > button.burger > div {
        position: relative;
        width: 20px;
        height: 3px;
        border-radius: 2px;
        position: relative;
        background: white;
        transition: all 0.3s ease-in-out;
        transform-origin: 0%;
    }

    header.title-header > button.burger.active > .burger-bar-middle {
        opacity: 0;
    }

    header.title-header > button.burger.active > .burger-bar-top {
        transform: rotate(45deg) translate(1px, -3px);
    }

    header.title-header > button.burger.active > .burger-bar-bottom {
        transform: rotate(-45deg)  translate(1px, 3px);
    }

    header.title-header > span {
        font-size: 24px;
        line-height: 1;
        font-weight: 500;
        display: inline-block;
    }

    main {
        position: relative;
        z-index: 1;
    }

    @media(max-width: 480px) {
        header.title-header > button.burger {
            display: none;
        }
    }
</style>
