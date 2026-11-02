document.querySelectorAll('[data-password-target]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordTarget);
        if (!(input instanceof HTMLInputElement)) {
            return;
        }

        const showPassword = input.type === 'password';
        input.type = showPassword ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(showPassword));
        button.setAttribute('aria-label', showPassword ? 'Sembunyikan kata sandi' : 'Lihat kata sandi');
        button.setAttribute('title', showPassword ? 'Sembunyikan kata sandi' : 'Lihat kata sandi');
        button.classList.toggle('is-visible', showPassword);
    });
});
