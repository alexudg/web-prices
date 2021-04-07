btStatusClose.onclick = () => {
    statusArea.style.display = 'none'
}

async function loadSession(token) {
    const result = await executeGet('src/php/db.php?fn=getUserId&token=' + token) // {id: <int>}
    console.log('result: ', result)
    sessionStorage.setItem('id', result.id)
    var id = sessionStorage.getItem('id');
    console.log('id: ', id)
    // if (id != null)
    //     window.location.replace('dashboard.html')
}

form.onsubmit = async (eve) => {
    eve.preventDefault()
    //console.log('submit')
    let formData = new FormData(form)
    formData.append('fn', 'isNamePass')
    const result = await executePost('src/php/db.php', formData)
    console.log(result)
    if (result.result) {
        // save data
        console.log(localStorage.getItem('token'))
        showStatus('Bienvenido', false) 
        localStorage.setItem('token', result.data.token)
        console.log(localStorage.getItem('token'))
    }
    else {
        form.pass.value = ''
        showStatus('Usuario o contraseña incorrecta.') 
    }
}

window.onload = () => {
    btInput.classList.add('active')    
    // si ya existe token, ir por el usuario a la base de datos
    var token = localStorage.getItem('token')
    var id = sessionStorage.getItem('id')
    console.log('token:', token)
    console.log('id: ', id)    
    if (token) { 
        if (id) {
            console.log('!= null: ', id)
            //window.location.replace('dashboard.html')
        }
        else
            loadSession(token);
    }
}