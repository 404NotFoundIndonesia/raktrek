<script>
    import {page,inertia,router} from '@inertiajs/svelte';
    import { fly } from 'svelte/transition';

    let openMenu = false;

    const currentRoute = $page.props.currentRouteName;

    const toggleMenu = () => openMenu = !openMenu;
    const logout = () => router.post('/logout');
</script>

<header class="title-header">
    <button class="burger" class:active={openMenu} on:click={toggleMenu}>
        <div class="burger-bar-top"></div>
        <div class="burger-bar-middle"></div>
        <div class="burger-bar-bottom"></div>
    </button>

    <span>{$page.props.appName}</span>
</header>

<main>
    <slot />
</main>

<nav class="footer-menu">
    <div>
        <a href="/" use:inertia class="footer-menu-link" class:active={ currentRoute === 'home' }>
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-home-star" width="30" height="30" viewBox="0 0 24 24" stroke-width="1" stroke="#222" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M19.258 10.258l-7.258 -7.258l-9 9h2v7a2 2 0 0 0 2 2h4" />
                <path d="M9 21v-6a2 2 0 0 1 2 -2h1.5" />
                <path d="M17.8 20.817l-2.172 1.138a.392 .392 0 0 1 -.568 -.41l.415 -2.411l-1.757 -1.707a.389 .389 0 0 1 .217 -.665l2.428 -.352l1.086 -2.193a.392 .392 0 0 1 .702 0l1.086 2.193l2.428 .352a.39 .39 0 0 1 .217 .665l-1.757 1.707l.414 2.41a.39 .39 0 0 1 -.567 .411l-2.172 -1.138z" />
            </svg>
            <span>Home</span>
        </a>
    </div>
    <div>
        <a href="/explore" use:inertia class="footer-menu-link" class:active={ currentRoute === 'explore' }>
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-imac-search" width="30" height="30" viewBox="0 0 24 24" stroke-width="1" stroke="#222" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M11 17h-7a1 1 0 0 1 -1 -1v-12a1 1 0 0 1 1 -1h16a1 1 0 0 1 1 1v8" />
                <path d="M3 13h10" />
                <path d="M8 21h4" />
                <path d="M10 17l-.5 4" />
                <path d="M18 18m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                <path d="M20.2 20.2l1.8 1.8" />
              </svg>
            <span>Explore</span>
        </a>
    </div>
    {#if $page.props.user != null}
    <div>
        <a href="/login" use:inertia class="footer-menu-link">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-user-circle" width="30" height="30" viewBox="0 0 24 24" stroke-width="1" stroke="#000" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                <path d="M12 10m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                <path d="M6.168 18.849a4 4 0 0 1 3.832 -2.849h4a4 4 0 0 1 3.834 2.855" />
            </svg>
            <span>Profile</span>
        </a>
    </div>
    <div>
        <span aria-hidden="true" on:click={logout} class="footer-menu-link">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-logout" width="30" height="30" viewBox="0 0 24 24" stroke-width="1" stroke="#000" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" />
                <path d="M9 12h12l-3 -3" />
                <path d="M18 15l3 -3" />
            </svg>
            <span>Logout</span>
        </span>
    </div>
    {:else}
    <div>
        <a href="/login" use:inertia class="footer-menu-link" class:active={ currentRoute === 'auth.login' || currentRoute === 'auth.register' }>
            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-login-2" width="30" height="30" viewBox="0 0 24 24" stroke-width="1" stroke="#000" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M9 8v-2a2 2 0 0 1 2 -2h7a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-7a2 2 0 0 1 -2 -2v-2" />
                <path d="M3 12h13l-3 -3" />
                <path d="M13 15l3 -3" />
            </svg>
            <span>Login</span>
        </a>
    </div>
    {/if}
</nav>

{#if openMenu}
<nav class="side-menu" transition:fly={{y:-50}}>
    <a href="/" use:inertia class:active={ currentRoute === 'home' }>Home</a>
    <a href="/explore" use:inertia class:active={ currentRoute === 'explore' }>Explore</a>
    {#if $page.props.user != null}
    <a href="/" use:inertia class:active={ currentRoute === 'profile' }>Profile</a>
    <span aria-hidden="true" on:click={logout} class:active={ currentRoute === 'auth.login' }>Logout</span>
    {:else}
    <a href="/login" use:inertia class:active={ currentRoute === 'auth.login' || currentRoute === 'auth.register' }>Login</a>
    {/if}
</nav>
{/if}

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
        cursor: pointer;
    }

    header.title-header > button.burger:hover {
        background-color: #222222;
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

    nav.side-menu {
        position: fixed;
        top: 66px;
        left: 28px;
        z-index: 99;
        display: flex;
        flex-direction: column;
        background-color: #222;
        color: white;
        box-shadow: 0 4px 15px 0 rgba(0, 0, 0, 0.09);
        width: 100px !important;
        padding: 20px;
        border-end-end-radius: 15px;
        border-end-start-radius: 15px;
    }

    nav.side-menu * {
        cursor: pointer;
        color: rgb(203, 203, 203);
        text-decoration: none;
    }

    nav.side-menu *:hover {
        text-decoration: underline;
    }

    nav.side-menu .active {
        text-decoration: underline;
        font-weight: 600;
        color: white;
    }

    nav.footer-menu {
        display: none;
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        justify-content: space-around;
        align-items: center;
        padding: 10px 0;
        box-shadow: 0 -5px 15px 0 rgba(0,0,0,0.09);
        z-index: 100;
    }

    nav.footer-menu .footer-menu-link {
        cursor: pointer;
        font-weight: 300;
        color: #222;
    }

    nav.footer-menu .footer-menu-link > svg {
        stroke-width: 1;
        stroke: #222;
        width: 30;
        height: 30;
    }

    nav.footer-menu .footer-menu-link.active {
        font-weight: 500;
    }

    nav.footer-menu .footer-menu-link.active > svg {
        stroke-width: 1.5;
        stroke: #000;
    }

    nav.footer-menu > div > a, nav.footer-menu > div > span {
        text-decoration: none;
        color: black;
        text-align: center;
        font-size: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    main {
        position: relative;
        z-index: 1;
    }

    @media(max-width: 480px) {
        header.title-header > button.burger {
            display: none;
        }

        nav.side-menu {
            display: none;
        }

        nav.footer-menu {
            display: flex;
        }
    }
</style>
