function togglePassword(){
    const pw = document.getElementById("password");
    const toggleBtn = document.getElementById("togglePassword");
    pw.type = pw.type === "password" ? "text" : "password";

    //toggle the eye icon
    toggleBtn.classList.toggle("fa-eye-slash");

}