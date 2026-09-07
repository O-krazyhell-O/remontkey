document.addEventListener("DOMContentLoaded", () => {
  const burger = document.querySelector(".header__burger");
  const navigation = document.querySelector(".header__nav");

  burger?.addEventListener("click", () => {
    navigation.classList.toggle("header__nav--open");
    burger.setAttribute(
      "aria-expanded",
      navigation.classList.contains("header__nav--open"),
    );
  });

  const calculator = document.querySelector("#repair-calculator");

  calculator?.addEventListener("input", () => {
    const area = Math.max(0, Number(calculator.area.value) || 0);
    const price = Number(calculator.type.value) || 0;
    const rooms = Number(calculator.rooms.value) || 1;
    const total = Math.round(area * price * (1 + (rooms - 1) * 0.025));
    const result = document.querySelector("#calc-total");

    result.textContent = `${total.toLocaleString("ru-RU")} ₽`;
  });

  const locationBlock = document.querySelector("#location-api");

  if (locationBlock) {
    const address = "Москва, улица Строителей, 18";
    const params = new URLSearchParams({
      q: address,
      format: "jsonv2",
      limit: "1",
      countrycodes: "ru",
      "accept-language": "ru",
    });

    fetch(`https://nominatim.openstreetmap.org/search?${params}`)
      .then((response) => {
        if (!response.ok) {
          throw new Error("Сервис геокодирования временно недоступен");
        }

        return response.json();
      })
      .then(([place]) => {
        if (!place) {
          throw new Error("Адрес не найден");
        }

        const latitude = Number(place.lat).toFixed(5);
        const longitude = Number(place.lon).toFixed(5);

        locationBlock.classList.add("location-api--success");
        locationBlock.querySelector("strong").textContent =
          "Адрес найден на карте";
        locationBlock.querySelector("p").textContent =
          `Москва, ул. Строителей, 18 · координаты: ${latitude}, ${longitude}`;
      })
      .catch(() => {
        locationBlock.classList.add("location-api--error");
        locationBlock.querySelector("strong").textContent =
          "Офис на улице Строителей, 18";
        locationBlock.querySelector("p").textContent =
          "Не удалось обновить данные карты. Позвоните нам для уточнения маршрута.";
      });
  }

  document.querySelectorAll("form[data-demo]").forEach((form) => {
    form.addEventListener("submit", (event) => {
      event.preventDefault();

      const message = form.querySelector(".form-card__message");

      if (message) {
        message.hidden = false;
        message.textContent =
          "Данные приняты. Демонстрационная форма работает без отправки на сервер.";
      }
    });
  });
});
