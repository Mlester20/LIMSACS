function togglePassword(button){
    if (!button) return;

    const inputGroup = button.closest('.input-group');
    const passwordInput = inputGroup?.querySelector('input');
    const icon = button.querySelector('i');

    if (!passwordInput || !icon) return;

    const show = passwordInput.type === 'password';
    passwordInput.type = show ? 'text' : 'password';

    icon.classList.toggle('bx-hide', !show);
    icon.classList.toggle('bx-show', show);
}
