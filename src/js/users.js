function addEditUser(id, username=null, email=null) {
    //console.log(id)
    formTitle.innerText = id == 0 ? 'Agregar usuario' : 'Modificar usuario'
    form.id.value = id
    if (id != 0) {
        form.username.value = username
        form.email.value = email        
        ckPass.style.display = 'inline'
        lbPass.style.display = 'inline'        
    }
    // add, pass obligatorio
    else {
        ckPass.checked = true
        ckPassChange(true) // forzar a mostrar contraseñas
        ckPass.style.display = 'none'
        lbPass.style.display = 'none'
    }
    modalAddEdit.style.display = 'flex'
    form.username.focus()
}

function delUser(id, username) {
    //console.log(id, username)
    idDel.value = id
    descriptionDel.innerText = username
    modalDel.style.display = 'flex'
}

modalDel.onclick = (eve) => {
    if (eve.target == modalDel) {
        modalDel.style.display = 'none'
        statusDelArea.style.display = 'none'
    }
}

modalAddEdit.onclick = (eve) => {
    if (eve.target == modalAddEdit) {
        modalAddEdit.style.display = 'none'
        statusArea.style.display = 'none'
        form.reset()
        ckPassChange(false) // ya esta desmarcado
    }
}

btCloseStatus.onclick = () => {
    statusArea.style.display = 'none'
}

btCloseStatusDel.onclick = () => {
    statusDelArea.style.display = 'none'
}


function cancelAddEdit() {
    modalAddEdit.click() // se crea param eve
}

async function okDel() {
    //console.log(idDel.value)
    if (idDel.value != '1') {
        divDel.style.display = 'none'
        let formData = new FormData()
        formData.append('fn', 'delUser')
        formData.append('id', idDel.value)
        const response = await executePost(URL_SERVER, formData)
        //console.log(response)
        if (response.success) {
            showStatusDel('El usuario ha sido eliminado.', false)
            setTimeout(() => {
                modalDel.click()
                divDel.style.display = 'block'
                loadUsers()
            }, 2000)
        }
        else {
            showStatusDel()
            divDel.style.display = 'block'
        }
    }
    else 
        showStatusDel('El usuario no puede ser elimnado porque es el <b>super-admin</b>')
}

function cancelDel() {
    modalDel.click() // se crea param eve
}

function passClear() {
    form.pass.value = ''
    form.confirm.value = ''
}

function ckPassChange(isChecked) {
    //console.log('ckPass.checked', isChecked)
    sectionPass.style.display = isChecked ? 'block' : 'none'
    if (!isChecked) {
        passClear()
        form.pass.removeAttribute('required')
        form.confirm.removeAttribute('required')
    }
    else {
        form.pass.setAttribute('required', '')
        form.confirm.setAttribute('required', '')
    }
}

form.onsubmit = async (eve) => {
    eve.preventDefault()
    //console.log('submit')
    
    // username existe?
    const response = await executeGet(URL_SERVER + '?fn=isUsernameExists&username=' + form.username.value + '&id=' + form.id.value)
    //console.log(response) // {success: false|true}
    let isContinue = false
    if (response.success) {
        showStatus('El nombre de usuario ya existe.') // form.js
        form.username.focus()        
    }
    else
        isContinue = true
    // email existe?
    if (isContinue) {
        isContinue = false
        const response = await executeGet(URL_SERVER + '?fn=isEmailExists&email=' + form.email.value + '&id=' + form.id.value)
        //console.log(response) // {success: false|true}
        if (response.success) {
            showStatus('El correo ya existe.') // form.js
            form.email.focus()
        }
        else
            isContinue = true
    }
    // password
    if (isContinue) {
        isContinue = false
        if (ckPass.checked) {
            if (form.pass.value == form.confirm.value)
                isContinue = true
            else {
                showStatus('La contraseña y su confirmación son diferentes.')
                passClear()
                form.pass.focus()
            }
        }
        else
            isContinue = true
    }

    // guardar
    if (isContinue) {
        divAddEdit.style.display = 'none'
        let formData = new FormData()
        formData.append('fn', 'addUpdateUserData') // id decide si add o update
        formData.append('id', form.id.value) // 0 = add, >0 = update
        formData.append('username', form.username.value)
        formData.append('email', form.email.value)
        formData.append('pass', form.pass.value) // ''=no cambiar, sino cambiarla
        const response = await executePost(URL_SERVER, formData)
        //console.log(response)
        if (response.success) {            
            const txt = form.id.value == '0' 
                ? 'El nuevo usuario ha sido agregado.'
                : 'El usuario ha sido modificado.'
            showStatus(txt, false)
            setTimeout(() => {
                modalAddEdit.click() // cerrar modal y limpiar formulario
                divAddEdit.style.display = 'block'
                loadUsers()
            }, 2000)
        }
        else {
            showStatus()
            divAddEdit.style.display = 'block'
        }
    }
}

async function loadUsers() {
    const response = await executeGet(URL_SERVER + '?fn=getUsersData') // script.js
    //console.log(response) // {success: false|true, users: null | [{},...] }
    if (response.success) {
        tbody.innerHTML = ''
        response.users.forEach(users => {
            //console.log(users)
            tbody.innerHTML += `<tr>
                                    <td>${users.id}</td>
                                    <td>${users.username}</td>
                                    <td>${users.email}</td>
                                    <td>
                                        <button class="bt-edit" onclick="addEditUser(${users.id}, '${users.username}', '${users.email}')"><img src="src/img/edit24px.png" alt="Editar" title="Editar"></button>
                                        <button class="bt-del" onclick="delUser(${users.id}, '${users.username}')"><img src="src/img/trash24px.png" alt="Eliminar" title="Eliminar"></button>
                                    </td>
                                </tr>`
        });
        countUsers.innerHTML = response.users.length + ' usuario'
        if (response.users.length !== 1)
            countUsers.innerHTML += 's'
        countUsers.innerHTML += ' encontrado'
        if (response.users.length !== 1)
            countUsers.innerHTML += 's'
    }
}

window.onload = () => {
    if (!sessionStorage.prices_id || sessionStorage.prices_id != '1')
        window.location.href = 'input.html'
    else {
        btUsers.classList.add('active')
        btUsersList.classList.add('active')
        btUsersFoot.classList.add('active')
        loadUsers()
    }    
}