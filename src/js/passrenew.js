form.onsubmit = async (eve) => {
    eve.preventDefault()
    // vefiry pass equals
    if (form.pass.value == form.confirm.value) {
        let formData = new FormData()
        formData.append('fn', 'passRenew')
        formData.append('id', form.id.value)
        formData.append('pass', form.pass.value)
        const response = await executePost(URL_SERVER, formData) // renovar contraseña y eliminar token
        //console.log(response) // {success: false|true, exception:<string>|null, result:null|false|true}
        if (response.success && response.result) {
            divForm.style.display = 'none'
            link.setAttribute('href', 'input.html')
            link.innerText = 'Ingresar'
            showStatus('La contraseña ha sido renovada, ahora puedes ingresar con ella aqui abajo en "<b>Ingresar</b>"', false)
        }
        else
            showStatus()
    }
    else {
        form.pass.value = ''
        form.confirm.value = ''
        showStatus('La nueva contraseña y su confirmación son diferentes.')
        form.pass.focus()
    }
}

divForm.style.display = 'none' // iniciar con formulario oculto

window.onload = async () => {
    //console.log('passrenew.js')
    // parametros: ?email=alexudg@gmail.com&token=4aeb31e3
    if (!window.location.search)
        window.location.href = 'input.html'
    else {
        //console.log(window.location.search)
        // tomar parametros
        const params = new URLSearchParams(window.location.search);
        // existen email and token?
        if (params.get('email') && params.get('token')) {
            // vefiricar si ambos corresponden (enviar la misma cadena de parametros con la funcion)
            const response = await executeGet(URL_SERVER + window.location.search + '&fn=isEmailTokenExists') // script.js
            //console.log(response) // {success:false|true, exception:<string>|null, result:null|{id:<id>}}
            if (response.success && response.result.id) {
                // guardar el id de usuario
                form.id.value = response.result.id
                form.pass.focus()
                divForm.style.display = 'block' // show form
            }
            else {
                showStatus('La liga no es válida para renovar tu contraseña, solicita un <b>nuevo correo</b> aqui abajo en "<b>Olvidé mi contraseña</b>".')
            }
        }  
        else
            window.location.href = 'input.html'
    }
}