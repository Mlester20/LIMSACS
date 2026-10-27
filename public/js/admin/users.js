function updateUser(id, full_name, email, role){
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_full_name').value = full_name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value = role;

}

function resetPasswordModal(id, full_name){
    document.getElementById('reset_user_id').value = id;
    document.getElementById('reset_user_name').textContent = full_name;
}

document.addEventListener('DOMContentLoaded', function(){
    const resetForm = document.getElementById('resetPasswordForm');
    const confirmInput = document.getElementById('confirm_password');

    if(resetForm){
        resetForm.addEventListener('submit', function(e){
            const newPassword = document.getElementById('new_password').value;
            confirmInput.setCustomValidity(
                newPassword !== confirmInput.value ? 'Passwords do not match.' : ''
            );

            if(!resetForm.checkValidity()){
                e.preventDefault();
                resetForm.classList.add('was-validated');
            }
        });

        confirmInput.addEventListener('input', function(){
            confirmInput.setCustomValidity('');
        });
    }

    document.getElementById('resetPasswordModal')?.addEventListener('hidden.bs.modal', function(){
        resetForm.reset();
        resetForm.classList.remove('was-validated');
        confirmInput.setCustomValidity('');
    });
});