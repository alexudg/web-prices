function btCancelClick() {
    window.location.href = 'dashboard.html'
}

function btStatusCloseClick() {
    statusArea.style.display = 'none'
}

function passClear(clearOld=true) {
    if (clearOld)
        form.passOld.value = ''
    form.pass.value = ''
    form.confirm.value = ''
}

function ckPassChange(isChecked) {
    //console.log('ckPass.checked', isChecked)
    sectionPass.style.display = isChecked ? 'block' : 'none'
    if (!isChecked) {
        passClear()
        form.passOld.removeAttribute('required')
        form.pass.removeAttribute('required')
        form.confirm.removeAttribute('required')
    }
    else {
        form.passOld.setAttribute('required', '')
        form.pass.setAttribute('required', '')
        form.confirm.setAttribute('required', '')
    }
}

form.onsubmit = async (eve) => {
    eve.preventDefault()

    let isContinue = false
    let formData = new FormData()
    // verificar contraseña actual si el check esta activado
    if (ckPass.checked) {
        formData.append('fn', 'isUserPass')
        formData.append('id', sessionStorage.prices_id)
        formData.append('pass', form.passOld.value)
        const response = await executePost('src/php/db.php', formData) // script.js
        //console.log(response) // {success: false|true, exception: <string>|null, result:null|false|true}
        if (response.success && response.result)
            isContinue = true
        else {
            passClear()
            showStatus('La contraseña actual es incorrecta.') // form.js
            form.passOld.focus()
        }
    }
    else
        isContinue = true

    // sin o con contraseña correcta
    if (isContinue) {
        isContinue = false
        if (ckPass.checked)
            if (form.pass.value == form.confirm.value)
                isContinue = true
            else {
                passClear(false) // no limpiar contraseña actual
                showStatus('La nueva contraseña y su confirmación son diferentes.') // form.js 
                form.pass.focus()  
            }
        else
            isContinue = true
    }

    // enviar datos para actualizar
    if (isContinue) {
        formData = new FormData()
        formData.append('fn', 'addUpdateUserData')
        formData.append('id', sessionStorage.prices_id)
        formData.append('username', form.username.value)
        formData.append('email', form.email.value)
        formData.append('pass', form.pass.value) // ''=no cambiar
        const response = await executePost('src/php/db.php', formData)
        //console.log(response) // {success: false|true, exception:<string>|null, result: false|true}
        if (response.success && response.result) {
            sectionForm.style.display = 'none'
            sessionStorage.prices_username = form.username.value
            sessionStorage.prices_email = form.email.value
            form.reset()
            showStatus('Tus datos han sido actualizados.', false)            
            setTimeout(() => {
                window.location.href = 'dashboard.html'
                sectionForm.style.display = 'block'
            }, 2000)
        }
        else
            showStatus()
    }
}

window.onload = () => {
    if (!sessionStorage.prices_id) {
        window.location.href = 'input.html'
    }
    else {
        btMyData.classList.add('active')  
        btMyDataList.classList.add('active') 
        btMyDataFoot.classList.add('active') 
        form.username.value = sessionStorage.prices_username
        form.email.value = sessionStorage.prices_email    
    }
}