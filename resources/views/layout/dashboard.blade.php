@extends('layout.app')

@section('body')
    <div class="wrapper">
        @include('layout.sidebar')

        <div class="main">
            @include('layout.navbar')

            <main class="content">
                <div class="container-fluid p-0">

                    <h1 class="h3 mb-3"><strong>Analytics</strong> Dashboard</h1>

                </div>
            </main>

            @include('layout.footer')
        </div>
    </div>
@endsection
