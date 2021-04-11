btStatusClose.onclick = () => {
    statusArea.style.display = 'none'
}

function updateSession(user, isChangeToken=false) {
    console.log('user: ', user)
    console.log('localStorage.token antes de actualizar: ', localStorage.token)
    console.log('sessionStorage.id antes de actualizar: ', sessionStorage.id)
    if (isChangeToken)
        localStorage.setItem('token', user.token)
    sessionStorage.id = user.id
    sessionStorage.username = user.username
    console.log('localStorage.token despues de actualizar: ', localStorage.token)
    console.log('sessionStorage.id despues de actualizar: ', sessionStorage.id)
    window.location.replace('dashboard.html')
}

async function loadSession() {
    console.log('token antes del request: ', localStorage.token)
    const response = await executeGet('src/php/db.php?fn=getUserData&token=' + localStorage.token) // {id: <int>}
    console.log('response: ', response)
    if (response.success) {
        console.log('id dueña de token:', response.user.id)
        updateSession(response)        
    }
    // si no existe id asociado al token, elimnar token y que vuelva a inicar sesion
    else {
        console.log('ningun id es dueño del token, eliminar token')
        localStorage.removeItem('token')
        console.log('despues de remover localStorage.token: ', localStorage.token)
    }
}

form.onsubmit = async (eve) => {
    eve.preventDefault()
    //console.log('submit')
    let formData = new FormData(form)
    formData.append('fn', 'isUserExists')
    const response = await executePost('src/php/db.php', formData)
    //console.log('response: ', response) // {success: true|false, user:null|{id:<int>, username:<string>, token:<8-digits>}}
    if (response.success) {
        // save data
        console.log('contraseña correcta, dar de alta token e id')
        updateSession(response.user, true) 
        showStatus('Bienvenido', false)
    }
    else {
        form.pass.value = ''
        showStatus('Usuario o contraseña incorrecta.') 
    }
}

window.onload = () => {
    btInput.classList.add('active')    
    // si ya existe token, ir por el usuario a la base de datos
    console.log('localStorage.token: ', localStorage.token)
    console.log('sessionStorage.id: ', sessionStorage.id)    
    if (localStorage.token) { 
        if (sessionStorage.id) {
            console.log('se encuentra en sesion, redireccionarlo')
            window.location.replace('dashboard.html')
        }
        else
            loadSession();
    }
}