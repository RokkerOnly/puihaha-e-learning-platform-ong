<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Membership Application | Puihaha</title>

    <link rel="stylesheet" href="{{ asset('css/members.css') }}">
</head>

<body class="app-body">
    <main class="workspace">
        <header class="workspace-header">
            <div>
                <p class="eyebrow">PUIHAHA E-LEARNING PLATFORM</p>
                <h1>Membership Application</h1>
                <p class="page-subtitle">
                    Submit your details to apply for membership.
                </p>
            </div>

            <a href="{{ route('login') }}" class="btn btn-secondary">
                Administrator login
            </a>
        </header>

        <section class="card narrow" aria-label="Application form">
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

            @if ($categories->isEmpty() || $levels->isEmpty())
            <div class="alert" role="alert">
                Applications are currently unavailable.
                Please try again later.
            </div>
            @else
            <form action="{{ route('membership.store') }}" method="POST">
                @csrf

                <div class="field">
                    <label for="full_name">Full name</label>
                    <input id="full_name" name="full_name" value="{{ old('full_name') }}" maxlength="150"
                        autocomplete="name" required>
                </div>

                <div class="field">
                    <label for="email">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="255"
                        autocomplete="email" required>
                    <p class="transaction-help">
                        Your application acknowledgment will be sent here.
                    </p>
                </div>

                <div class="field">
                    <label for="phone">Phone number (optional)</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" maxlength="30"
                        autocomplete="tel">
                </div>

                <div class="field">
                    <label for="address">Address (optional)</label>
                    <input id="address" name="address" value="{{ old('address') }}" maxlength="255"
                        autocomplete="street-address">
                </div>

                <div class="field">
                    <label for="category_id">Membership category</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Choose a category</option>

                        @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id')==$category->id)
                            >
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="level_id">Membership level</label>
                    <select id="level_id" name="level_id" required>
                        <option value="">Choose a level</option>

                        @foreach ($levels as $level)
                        <option value="{{ $level->id }}" @selected(old('level_id')==$level->id)
                            >
                            {{ $level->name }}
                            — PHP {{ number_format($level->fee, 2) }}
                            / {{ $level->validity_months }} months
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <p class="location-heading">Location on map</p>

                    <p class="transaction-help">
                        Click the map to select the location for your application.
                        It will be saved with your submitted details.
                    </p>

                    <div id="application-map" data-weather-url="{{ route('weather.show') }}"
                        aria-label="Application location map"></div>

                    <input id="application-latitude" type="hidden" name="latitude" value="{{ old('latitude') }}">

                    <input id="application-longitude" type="hidden" name="longitude" value="{{ old('longitude') }}">

                    <p id="application-coordinates" class="transaction-help" role="status">
                        No location selected. The map starts in Manila.
                    </p>

                    <div class="application-weather">
                        <strong>Weather at selected location</strong>

                        <p id="application-weather-result" role="status">
                            Select a location to load its current weather.
                        </p>

                        <small>
                            Weather data:
                            <<a href="https://openweathermap.org/" target="_blank" rel="noopener noreferrer">
                                OpenWeather
                                </a>
                        </small>
                    </div>
                </div>

                <p class="transaction-notice">
                    Submitting this form records a pending application.
                    Membership activation follows payment and approval.
                </p>

                <div class="actions">
                    <button type="submit" class="btn">
                        Submit application
                    </button>
                </div>
            </form>
            @endif
        </section>
    </main>
    <script src="{{ asset('js/membership-location.js') }}"></script>

    @if (config('services.google_maps.key'))
    <script async
        src="https://maps.googleapis.com/maps/api/js?key={{ urlencode(config('services.google_maps.key')) }}&loading=async&callback=initMembershipMap&v=weekly">
    </script>
    @endif
</body>

</html>