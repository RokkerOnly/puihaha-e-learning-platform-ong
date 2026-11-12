<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Administrator Login | Puihaha</title>

    <link rel="stylesheet" href="{{ asset('css/members.css') }}">
</head>

<body class="login-page">
    <main class="login-shell">
        <section class="login-intro" aria-labelledby="login-heading">
            <a class="brand" href="{{ url('/') }}">
                <span class="brand-logo" aria-hidden="true">P</span>

                <span>
                    <strong>Puihaha</strong>
                    <small>E-Learning Platform</small>
                </span>
            </a>

            <div>
                <p class="eyebrow">THE MEMBERSHIP OFFICE</p>

                <h1 id="login-heading">
                    Your records.<br>
                    In good order.
                </h1>

                <p class="login-description">
                    Manage members, membership options, and their records
                    from one workspace.
                </p>
            </div>

            <p class="login-footer">
                Membership Management and Transaction Processing System
            </p>
        </section>

        <section class="login-form-panel" aria-labelledby="signin-heading">
            <p class="eyebrow">ADMINISTRATOR ACCESS</p>

            <h2 id="signin-heading">Sign in</h2>

            <p class="login-description">
                Use your administrator account to continue.
            </p>

            @if (session('success'))
            <div class="alert alert-success" role="status">
                {{ session('success') }}
            </div>
            @endif

            @if ($errors->any())
            <div class="alert" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('login.store') }}" method="POST">
                @csrf

                <div class="field">
                    <label for="email">Email address</label>

                    <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="255"
                        autocomplete="username" required autofocus>
                </div>

                <div class="field">
                    <label for="password">Password</label>

                    <input id="password" type="password" name="password" autocomplete="current-password" required>
                </div>

                <button class="btn login-submit" type="submit">
                    Sign in
                    <span aria-hidden="true">→</span>
                </button>
            </form>

            <div class="membership-signup">
                <p>Not a member yet?</p>

                <a href="{{ route('membership.create') }}">
                    Sign up for membership →
                </a>
            </div>

            <p class="login-help">
                Access is limited to authorized administrators.
                Contact the system administrator if you need an account.
            </p>
        </section>
    </main>
</body>

</html>