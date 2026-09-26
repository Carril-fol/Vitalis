import './stimulus_bootstrap.js';
import './styles/app.css';
import * as Turbo from '@hotwired/turbo';

Turbo.config.forms.confirm = (message) => new Promise((resolve) => {
    const element = document.getElementById('confirm-modal');
    const modal = window.bootstrap.Modal.getOrCreateInstance(element);
    let accepted = false;

    element.querySelector('[data-confirm-message]').textContent = message;
    element.querySelector('[data-confirm-accept]').onclick = () => {
        accepted = true;
        modal.hide();
    };
    element.addEventListener('hidden.bs.modal', () => resolve(accepted), { once: true });
    modal.show();
});

document.addEventListener('submit', (event) => {
    const button = event.submitter;
    const label = button?.querySelector('.label');
    if (event.defaultPrevented || !event.target.matches('[data-vitalis-loading]') || !label || !button.dataset.loadingLabel) {
        return;
    }
    label.dataset.original = label.textContent;
    label.textContent = button.dataset.loadingLabel;
    button.querySelector('.spinner-border')?.classList.remove('d-none');
    button.disabled = true;
});

document.addEventListener('turbo:submit-end', (event) => {
    const button = event.detail.formSubmission.submitter;
    const label = button?.querySelector('.label');
    if (!label?.dataset.original) {
        return;
    }
    label.textContent = label.dataset.original;
    button.querySelector('.spinner-border')?.classList.add('d-none');
    button.disabled = false;
});

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-vitalis-toggle]');
    const input = button && document.querySelector(button.dataset.vitalisToggle);
    if (!input) {
        return;
    }
    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    button.setAttribute('aria-pressed', String(show));
    button.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
    button.querySelector('i').className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
});

document.addEventListener('click', (event) => {
    if (event.target.closest('#sidebar a[href]')) {
        window.bootstrap?.Offcanvas.getInstance(document.getElementById('sidebar'))?.hide();
    }
});

const capsLock = (event) => {
    const input = event.target instanceof Element && event.target.closest('[data-vitalis-capslock]');
    if (input) {
        document.querySelector(input.dataset.vitalisCapslock)
            ?.classList.toggle('d-none', !event.getModifierState('CapsLock'));
    }
};

document.addEventListener('keydown', capsLock);
document.addEventListener('keyup', capsLock);

document.addEventListener('turbo:load', () => {
    document.querySelectorAll('[data-bs-ride="carousel"]')
        .forEach((el) => window.bootstrap?.Carousel.getOrCreateInstance(el).cycle());
});
