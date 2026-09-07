const backendScriptUrl = document.currentScript?.src;

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll('input[type="tel"]').forEach((input) => {
    input.addEventListener("input", () => {
      let digits = input.value.replace(/\D/g, "");
      if (digits.startsWith("8") || digits.startsWith("7")) digits = digits.slice(1);
      digits = digits.slice(0, 10);
      let value = "+7";
      if (digits.length) value += ` (${digits.slice(0, 3)}`;
      if (digits.length >= 3) value += ")";
      if (digits.length > 3) value += ` ${digits.slice(3, 6)}`;
      if (digits.length > 6) value += `-${digits.slice(6, 8)}`;
      if (digits.length > 8) value += `-${digits.slice(8, 10)}`;
      input.value = value;
    });
  });

  document.querySelectorAll("form[data-validate]").forEach((form) => {
    form.addEventListener("submit", (event) => {
      form.querySelectorAll(".field-error[data-client]").forEach((node) => node.remove());
      form.querySelectorAll(".is-invalid").forEach((node) => node.classList.remove("is-invalid"));
      let valid = true;

      form.querySelectorAll("[required]").forEach((field) => {
        let message = "";
        if (!field.value.trim()) message = "Поле обязательно для заполнения";
        else if (field.type === "email" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) message = "Введите корректный email";
        else if (field.type === "tel" && !/^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/.test(field.value)) message = "Введите телефон полностью";
        else if (field.name === "password" && field.value.length < 6) message = "Минимум 6 символов";

        if (message) {
          valid = false;
          field.classList.add("is-invalid");
          const error = document.createElement("small");
          error.className = "field-error";
          error.dataset.client = "true";
          error.textContent = message;
          field.after(error);
        }
      });

      if (!valid) event.preventDefault();
    });
  });

  if (backendScriptUrl) {
    const siteRoot = new URL("../", backendScriptUrl);
    fetch(new URL("api/session.php", siteRoot), { credentials: "same-origin" })
      .then((response) => response.json())
      .then((session) => {
        if (!session.authenticated) return;
        const loginButton = document.querySelector(".header__inner > .button");
        if (!loginButton) return;
        loginButton.textContent = session.admin ? "Админ-панель" : "Кабинет";
        loginButton.href = new URL(session.admin ? "admin/index.php" : "account.php", siteRoot);
      })
      .catch(() => {});
  }
});
