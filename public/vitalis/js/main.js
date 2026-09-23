(() => {
  'use strict';

  // --- Validación del formulario con la API nativa + estilos de Bootstrap ---
  const form = document.getElementById('form-cita');
  const ok = document.getElementById('form-ok');

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    ok.classList.add('d-none');

    if (!form.checkValidity()) {
      form.classList.add('was-validated');        // muestra los .invalid-feedback
      form.querySelector(':invalid').focus();     // lleva el foco al primer error
      return;
    }

    // ponytail: no hay backend todavía; conectar con fetch() a un endpoint de turnos cuando se integre con Symfony
    ok.classList.remove('d-none');
    form.reset();
    form.classList.remove('was-validated');
  });

  // --- En mobile, cierra el menú hamburguesa al tocar un enlace ---
  const menu = document.getElementById('menu');
  menu.querySelectorAll('a').forEach((link) =>
    link.addEventListener('click', () => bootstrap.Collapse.getInstance(menu)?.hide())
  );
})();
