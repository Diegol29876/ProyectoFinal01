const cedulaInput = document.querySelector("#cedula");
const passwordInput = document.querySelector("#password");

cedulaInput.addEventListener("input", function() {
    let texto = cedulaInput.value;
    let soloNumeros = "";

    for (let i = 0; i < texto.length; i++) {
        let letra = texto[i];
        
        if (letra >= '0' && letra <= '9') {
            soloNumeros += letra;
        }
    }

    if (soloNumeros.length > 8) {
        soloNumeros = soloNumeros.slice(0, 8);
    }

    cedulaInput.value = soloNumeros;
});