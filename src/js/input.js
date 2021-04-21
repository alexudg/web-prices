btStatusClose.onclick = () => {
    statusArea.style.display = 'none'
}

function updateSession(user, isChangeToken=false) {
    //console.log('user: ', user) // {id:<int>, username:<string>, email:<string>}
    //console.log('sessionStorage antes de actualizar: ', sessionStorage)
    if (isChangeToken) {
        //console.log('localStorage.token antes de actualizar: ', localStorage.token)
        localStorage.token = user.token
        //console.log('localStorage.token despues de actualizar: ', localStorage.token)
    }
    sessionStorage.clear()
    sessionStorage.id = user.id
    sessionStorage.username = user.username
    sessionStorage.email = user.email
    
    //console.log('sessionStorage despues de actualizar: ', sessionStorage)
    //console.log('re-direccionar')
    window.location.replace('dashboard.html')
}

async function getUserData() {
    //console.log('token antes del request: ', localStorage.token)
    const response = await executeGet('src/php/db.php?fn=getUserData&token=' + localStorage.token) // {id: <int>}
    //console.log('response: ', response) // {success: false|true, user: null|{id:<int>, username:<string>, email: <string>}  }
    if (response.success) {
        updateSession(response.user)        
    }
    // si no existe id asociado al token, elimnar token y que vuelva a inicar sesion
    else {
        //console.log('ningun id es dueño del token, eliminar token')
        localStorage.removeItem('token')
        //console.log('despues de remover localStorage.token: ', localStorage.token)
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
        //console.log('contraseña correcta, dar de alta token e id')
        showStatus('Bienvenido', false)
        updateSession(response.user, true) // true=actualizar token 
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
    btInputList.classList.add('active') 
    btInputFoot.classList.add('active')
    // si ya existe token, ir por el usuario a la base de datos
    form.username.focus()    
    if (localStorage.token) { 
        if (sessionStorage.id) {
            //console.log('se encuentra en sesion, redireccionarlo')
            //window.location.replace('dashboard.html')
        }
        else
            getUserData();
    }
}