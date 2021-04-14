form.onsubmit = async (eve) => {
    eve.preventDefault()
    // vefiry pass equals
    if (form.pass.value == form.confirm.value) {
        let formData = new FormData()
        formData.append('fn', 'updatePass')
        formData.append('id', form.id.value)
        formData.append('pass', form.pass.value)
        const response = await executePost(URL_SERVER, formData)
        //console.log(response) // {success: false|true}
        if (response.success) {
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
            //console.log(response) // {success: false|true, id: null|<int>}
            if (response.success) {
                // guardar el id de usuario
                form.id.value = response.id
                form.pass.focus()
            }
            else {
                divForm.style.display = 'none' // hidden form
                showStatus('La liga no es válida para renovar tu contraseña, solicita un <b>nuevo correo</b> aqui abajo en "<b>Olvidé mi contraseña</b>".')
            }
        }  
        else
            window.location.href = 'input.html'
    }
}