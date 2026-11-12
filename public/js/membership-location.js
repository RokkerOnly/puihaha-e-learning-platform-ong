(() => {
    const mapElement = document.getElementById("application-map");

    if (!mapElement) return;

    const latitudeInput = document.getElementById("application-latitude");
    const longitudeInput = document.getElementById("application-longitude");
    const coordinates = document.getElementById("application-coordinates");
    const weatherResult = document.getElementById("application-weather-result");

    let weatherTimer;
    let weatherController;
    let weatherVersion = 0;

    function scheduleWeather(lat, lon) {
        clearTimeout(weatherTimer);
        weatherController?.abort();

        const version = ++weatherVersion;

        weatherResult.textContent = "Loading weather...";

        weatherTimer = setTimeout(async () => {
            const controller = new AbortController();
            weatherController = controller;

            try {
                const url = new URL(
                    mapElement.dataset.weatherUrl,
                    window.location.origin
                );

                url.searchParams.set("lat", lat);
                url.searchParams.set("lon", lon);

                const response = await fetch(url, {
                    headers: {
                        Accept: "application/json",
                    },
                    signal: controller.signal,
                });

                if (!response.ok) {
                    throw new Error("Weather unavailable");
                }

                const weather = await response.json();

                if (version !== weatherVersion) return;

                const humidity = weather.humidity == null
                    ? "Unavailable"
                    : `${weather.humidity}%`;

                weatherResult.textContent =
                    `${weather.place}: ${weather.temperature}°C, ` +
                    `${weather.description}. Humidity: ${humidity}.`;
            } catch (error) {
                if (
                    error.name !== "AbortError" &&
                    version === weatherVersion
                ) {
                    weatherResult.textContent =
                        "Weather is currently unavailable. " +
                        "You can still submit your application.";
                }
            }
        }, 400);
    }

    window.gm_authFailure = () => {
        coordinates.textContent =
            "Google Maps could not load. Check the API key, " +
            "website restrictions, and billing status.";
    };

    window.initMembershipMap = async () => {
        try {
            const { Map } = await google.maps.importLibrary("maps");
            const { AdvancedMarkerElement } =
                await google.maps.importLibrary("marker");

            const savedLat = Number(latitudeInput.value);
            const savedLon = Number(longitudeInput.value);

            const hasSavedLocation =
                latitudeInput.value !== "" &&
                longitudeInput.value !== "" &&
                Number.isFinite(savedLat) &&
                Number.isFinite(savedLon) &&
                savedLat >= -90 && savedLat <= 90 &&
                savedLon >= -180 && savedLon <= 180;

            const initialLocation = hasSavedLocation
                ? { lat: savedLat, lng: savedLon }
                : { lat: 14.5995, lng: 120.9842 };

            const map = new Map(mapElement, {
                center: initialLocation,
                zoom: 13,
                mapId: "DEMO_MAP_ID",
                streetViewControl: false,
                mapTypeControl: false,
            });

            let marker;

            function selectLocation(lat, lon) {
                latitudeInput.value = lat.toFixed(7);
                longitudeInput.value = lon.toFixed(7);

                coordinates.textContent =
                    `Latitude: ${lat.toFixed(5)}, ` +
                    `Longitude: ${lon.toFixed(5)}`;

                const position = { lat, lng: lon };

                if (marker) {
                    marker.position = position;
                } else {
                    marker = new AdvancedMarkerElement({
                        map,
                        position,
                        title: "Selected application location",
                    });
                }

                scheduleWeather(lat, lon);
            }

            map.addListener("click", (event) => {
                if (!event.latLng) return;

                selectLocation(
                    event.latLng.lat(),
                    event.latLng.lng()
                );
            });

            if (hasSavedLocation) {
                selectLocation(savedLat, savedLon);
            }
        } catch (error) {
            coordinates.textContent =
                "The map could not load. Refresh the page and check the console.";
        }
    };
})();