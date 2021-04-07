btStatusClose.onclick = () => {
    statusArea.style.display = 'none'
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
    }
    else {
        form.pass.value = ''
        showStatus('Usuario o contraseña incorrecta.') 
    }
}

window.onload = () => {
    //console.log(localStorage.getItem('token'))

    // si ya existe token, ir por el usuario a la base de datos
    if (localStorage.getItem('token')) { 
        console.log('existe token')
    }
}