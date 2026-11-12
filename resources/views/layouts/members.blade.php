<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Dashboard') | Puihaha E-Learning Platform</title>

    <link rel="stylesheet" href="{{ asset('css/members.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}">
</head>

<body class="app-body">
    <div class="app-shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('dashboard') }}">
                <span class="brand-logo">P</span>

                <span>
                    <strong>Puihaha</strong>
                    <small>E-Learning Platform</small>
                </span>
            </a>

            <p class="nav-label">MAIN MENU</p>

            <nav class="sidebar-nav" aria-label="Main navigation">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'selected' : '' }}"
                    @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                    <span aria-hidden="true">▦</span>
                    Dashboard
                </a>

                <a href="{{ route('members.index') }}" class="{{ request()->routeIs('members.*') ? 'selected' : '' }}"
                    @if(request()->routeIs('members.*')) aria-current="page" @endif>
                    <span aria-hidden="true">♙</span>
                    Members
                </a>

                <a href="{{ route('applications.index') }}"
                    class="{{ request()->routeIs('applications.*') ? 'selected' : '' }}"
                    @if(request()->routeIs('applications.*')) aria-current="page" @endif>
                    <span aria-hidden="true">▤</span>
                    Applications
                </a>

                <a href="{{ route('categories.index') }}"
                    class="{{ request()->routeIs('categories.*') ? 'selected' : '' }}"
                    @if(request()->routeIs('categories.*')) aria-current="page" @endif>
                    <span aria-hidden="true">▦</span>
                    Categories
                </a>

                <a href="{{ route('levels.index') }}" class="{{ request()->routeIs('levels.*') ? 'selected' : '' }}"
                    @if(request()->routeIs('levels.*')) aria-current="page" @endif>
                    <span aria-hidden="true">☆</span>
                    Membership Levels
                </a>

                <a href="{{ route('transactions.index') }}" class="{{ request()->routeIs('transactions.*')
                        && ! request()->routeIs('transactions.payments.*')
                        ? 'selected'
                        : '' }}" @if ( request()->routeIs('transactions.*')
                    && ! request()->routeIs('transactions.payments.*')
                    )
                    aria-current="page"
                    @endif>
                    Transactions
                </a>

                <a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.*', 'transactions.payments.*')
                        ? 'selected'
                        : '' }}">
                    <span aria-hidden="true">₱</span>
                    Payments
                </a>

                @auth
                <form action="{{ route('logout') }}" method="POST">
                    @csrf

                    <button class="btn btn-secondary btn-small" type="submit">
                        Sign out
                    </button>
                </form>
                @endauth
            </nav>

            <div class="sidebar-footer">
                Puihaha E-Learning Platform
                <small>Membership Management &amp; Transactions</small>
            </div>
        </aside>

        <main class="workspace">
            <header class="workspace-header">
                <div>
                    <p class="eyebrow">Membership Management and Transaction Processing System</p>
                    <h1>@yield('title', 'Members')</h1>
                    <p class="page-subtitle">@yield('subtitle')</p>
                </div>

                <time class="date-chip" datetime="{{ now('Asia/Manila')->toDateString() }}">
                    {{ now('Asia/Manila')->format('F j, Y') }}
                </time>
            </header>

            <section class="@yield('panel-class', 'card') @yield('container-class')">
                @if ($errors->any())
                <div class="alert" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if (session('success'))
                <div class="alert alert-success" role="status">
                    {{ session('success') }}
                </div>
                @endif

                @yield('content')
            </section>
        </main>
    </div>
</body>

</html>