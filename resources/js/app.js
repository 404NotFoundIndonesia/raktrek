import './bootstrap';
import './../css/app.css';

import { createInertiaApp } from '@inertiajs/svelte'

document.addEventListener('contextmenu', event => event.preventDefault());

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.svelte', { eager: true })
        return pages[`./Pages/${name}.svelte`]
    },
    setup({ el, App, props }) {
        new App({ target: el, props })
    },
})
