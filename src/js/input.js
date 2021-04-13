btStatusClose.onclick = () => {
    statusArea.style.display = 'none'
}

function updateSession(user, isChangeToken=false) {
    console.log('user: ', user)
    console.log('sessionStorage.id antes de actualizar: ', sessionStorage.id)
    console.log('sessionStorage.username antes de actualizar: ', sessionStorage.username)
    if (isChangeToken) {
        console.log('localStorage.token antes de actualizar: ', localStorage.token)
        localStorage.token = user.token
        console.log('localStorage.token despues de actualizar: ', localStorage.token)
    }
    sessionStorage.clear()
    sessionStorage.id = user.id
    sessionStorage.username = user.username
    
    console.log('sessionStorage.id despues de actualizar: ', sessionStorage.id)
    console.log('sessionStorage.username despues de actualizar: ', sessionStorage.username)
    console.log('re-direccionar')
    window.location.replace('dashboard.html')
}

async function getUserData() {
    console.log('token antes del request: ', localStorage.token)
    const response = await executeGet('src/php/db.php?fn=getUserData&token=' + localStorage.token) // {id: <int>}
    console.log('response: ', response)
    if (response.success) {
        updateSession(response.user)        
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
        showStatus('Bienvenido', false)
        updateSession(response.user, true) 
    }
    else {
        form.pass.value = ''
        showStatus('Usuario o contraseña incorrecta.') 
    }
}

window.onload = () => {
    if (sessionStorage.id) 
        window.location.replace('dashboard.html')

    btInput.classList.add('active')    
    // si ya existe token, ir por el usuario a la base de datos
    form.username.focus()
    console.log('localStorage.token: ', localStorage.token)
    console.log('sessionStorage.id: ', sessionStorage.id)  
    console.log('sessionStorage.username: ', sessionStorage.username) 
    if (localStorage.token) { 
        if (sessionStorage.id) {
            console.log('se encuentra en sesion, redireccionarlo')
            //window.location.replace('dashboard.html')
        }
        else
            getUserData();
    }
}