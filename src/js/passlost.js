//const URL_PASS_RENEW = 'http://192.168.0.27/web-prices' // PC
const URL_PASS_RENEW = 'http://192.168.0.7/web-prices' // laptop
//const URL_PASS_RENEW = 'http://gucitex.com.mx/05' // gucitex

function closeStatus() {
    statusArea.style.display = 'none'
}

form.onsubmit = async (eve) => {
    eve.preventDefault()
    //console.log('submit')

    divForm.style.display = 'none'
    // verify if email exists, id=0 without owner
    const response = await executeGet(URL_SERVER + '?fn=isEmailExists&email=' + form.email.value + '&id=0') // script.js
    //console.log(response) // {success: false|true, exception:<string>|null, result:null|false|true}
    if (response.success && response.result) {
        // generar un nuevo token
        const response = await executeGet(URL_SERVER + '?fn=getNewTokenUser&email=' + form.email.value) // script.js
        //console.log(response) // {success:false|true, exception:<string>|null, result:null|<8-digits>}
        if (response.success) {
            statusArea.style.backgroundColor = 'silver'
            statusText.innerText = 'Enviando correo...'
            statusArea.style.display = 'block'
            const uri = URL_PASS_RENEW + '/passrenew.html?email=' + form.email.value + '&token=' + response.result 
                Email.send({
                    Host : 'smtp.gmail.com',
                    Username : 'puntoplanet',
                    Password : 'Jalisco01',
                    To : form.email.value,
                    From : 'puntoplanet@gmail.com',
                    Subject : 'Renovar contraseña en checador de precios',
                    Body : 'Para renovar tu contraseña da un click en el siguiente boton...<br><a href="' + uri + '"><button>Renovar contraseña</button></a>'
                })
                .then(message => {
                        if (message == 'OK') {
                            showStatus('El correo ha sido enviado a <b>' + form.email.value + '</b> para renovación de tu contraseña.', false)
                            form.email.value = ''
                        }
                        else {
                            showStatus('Error al enviar correo.')
                            divForm.style.display = 'block'
                        }
                    }    
                );
        }
        else {
            showStatus()
            divForm.style.display = 'block'
        }
    }
    else {
        showStatus('El correo <b>' + form.email.value + '</b> no se encuentra registrado.') // form.js
        divForm.style.display = 'block'
    }
}

window.onload = () => {
    
}