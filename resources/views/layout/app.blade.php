<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Raktrek is a web-based platform designed to streamline and enhance the management and user experience of a library. It aims to provide users with a convenient way to explore the library's catalogue, borrow books, and manage their reading activities.">
    <meta name="author" content="RakTrek">
    <meta name="keywords"
          content="raktrek, library, perpustakaan, buku, bootstrap, bootstrap 5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="shortcut icon" href="https://avatars.githubusercontent.com/u/87377917?s=200&v=4"/>

    <title>RakTrek</title>

    <link href="/css/app.css" rel="stylesheet">
    @stack('style')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>

<body>
@yield('body')

<script defer src="/js/app.js"></script>
@stack('script')
</body>
</html>
