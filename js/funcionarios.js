const tabla = document.getElementById('lista-funcionarios');
const formulario = document.getElementById('form-editar');
const mensaje = document.getElementById('mensaje');
let csrf = '';

async function enviar(datos) {
    const respuesta = await fetch('../php/funcionarios.php', datos ? {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ ...datos, csrf_token: csrf })
    } : {});
    const resultado = await respuesta.json();

    if (!respuesta.ok || !resultado.status) {
        throw new Error(resultado.mensaje || 'No se pudo completar la operación.');
    }
    return resultado;
}

async function cargarFuncionarios() {
    try {
        const resultado = await enviar();
        csrf = resultado.csrf_token;
        tabla.replaceChildren();

        resultado.funcionarios.forEach((f) => {
            const fila = tabla.insertRow();
            [f.id, f.nombre, f.correo, f.direccion, f.nacimiento, f.cedula,
                f.telefono, f.ingreso, f.estado].forEach((dato) => {
                fila.insertCell().textContent = dato;
            });

            const acciones = fila.insertCell();
            const editar = document.createElement('button');
            editar.textContent = 'Editar';
            editar.type = 'button';
            editar.onclick = () => {
                const datos = {
                    id: f.id,
                    n_usuario: f.nombre,
                    c_electronico: f.correo,
                    direccion: f.direccion,
                    f_nacimiento: f.nacimiento,
                    cedula: f.cedula,
                    n_telefono: f.telefono,
                    f_ingreso: f.ingreso,
                    estado: f.estado.toLowerCase() === 'activo' ? 'Activo' : 'Inactivo'
                };
                Object.entries(datos).forEach(([campo, valor]) => {
                    formulario.elements[campo].value = valor;
                });
                formulario.hidden = false;
                formulario.scrollIntoView();
            };

            const eliminar = document.createElement('button');
            eliminar.textContent = 'Eliminar';
            eliminar.type = 'button';
            eliminar.onclick = async () => {
                if (!confirm(`¿Querés eliminar al funcionario ${f.nombre}?`)) return;
                try {
                    mensaje.textContent = (await enviar({ action: 'delete', id: f.id })).mensaje;
                    await cargarFuncionarios();
                } catch (error) {
                    mensaje.textContent = error.message;
                }
            };
            acciones.append(editar, eliminar);
        });
    } catch (error) {
        mensaje.textContent = error.message;
    }
}

formulario.onsubmit = async (evento) => {
    evento.preventDefault();
    try {
        const datos = Object.fromEntries(new FormData(formulario));
        mensaje.textContent = (await enviar({ ...datos, action: 'update' })).mensaje;
        formulario.hidden = true;
        await cargarFuncionarios();
    } catch (error) {
        mensaje.textContent = error.message;
    }
};

document.getElementById('cancelar-edicion').onclick = () => {
    formulario.hidden = true;
    formulario.reset();
};

cargarFuncionarios();
