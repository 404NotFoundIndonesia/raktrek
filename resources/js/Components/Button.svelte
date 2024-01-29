<script>
	let buttonProps = { class: [ $$restProps.class ] }
</script>

<button on:click
        on:mouseover
        on:mouseenter
        on:mouseleave
        on:focus
        {...buttonProps}>
        <slot/>
</button>

<style>
    button {
        border-radius: 15px;
        padding: 10px 15px;
        border: none;
        cursor: pointer;
    }

    .primary {
        background-color: #000000;
        color: white;
    }

    .primary:hover {
        background-color: #222222;
    }

    .danger {
        color: white;
        background-color: #ff3535;
    }

    .danger:hover {
        background-color: #dd1d1d;
    }
</style>
