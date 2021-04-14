let searchText = ''
let idUserSelected = sessionStorage.id

function addArticle() {
    formTitle.innerText = 'Agregar artículo'
    form.id.value = '0' // 0=agregar
    loadSelectFamilies()
    modal.style.display = 'flex'
    form.code.focus()
}

async function editArticle(id) {
    // solicitud de datos del articulo
    const response = await executeGet('src/php/db.php?fn=getArticle&idUser=' + idUserSelected + '&id=' + id)
    //console.log(response) // {success: false|true, article: null|{}}
    if (response.success) {
        formTitle.innerText = 'Modificar artículo'
        form.id.value = id
        form.code.value = response.article.code
        form.description.value = response.article.description
        form.price.value = response.article.price
        await loadSelectFamilies()

        // seleccionar su familia
        for (const option of form.family.options) {
            //console.log('option: ', option.innerText, ', family: ', response.article.family);
            if (option.innerText === response.article.family) {
                //console.log(option.value)
                form.family.selectedIndex = option.index;
                break;
            }
        }

        modal.style.display = 'flex'
        form.code.focus()
    }
}

function delArticle(id, description) {
    //console.log(id, description)
    idToDel.value = id
    descriptionToDel.innerText = description
    modalDel.style.display = 'flex'
}

// security by post
async function loadUsersSelect() {
    let formData = new FormData()
    formData.append('fn', 'getUsers')
    formData.append('idUser', idUserSelected)
    const response = await executePost('src/php/db.php', formData)
    //console.log(response) // { success: false|true, users: null | [{}] | [{},{},...] }

    // fill select
    selUsers.innerHTML = ''
    response.users.forEach(user => {
        //console.log(user)
        selUsers.innerHTML +=  `<option value="${user.id}">${user.username}</option>`        
    })
    // if super-admin, select admin
    if (sessionStorage.id == '1') {
        for (const option of selUsers.options) {
            if (option.value == '1') {
                selUsers.selectedIndex = option.index
                break;
            }
        }
    }    
    else
        selUsers.style.visibility = 'hidden'    
}

function selUsersChange() {
    //console.log('selUserChange')
    idUserSelected = selUsers.options[selUsers.selectedIndex].value
    searchArticle.value = searchText == '' ? '.' : searchText
    loadArticles()
}

async function loadArticles() {
    searchText = (searchArticle.value.trim())
    if (searchText == '.')
        searchText = ''
    const response = await executeGet('src/php/db.php?fn=getArticles&idUser=' + idUserSelected + '&txt=' + searchText) // script.js
    //console.log('getArticles: ', response) // {success: true | false, articles: [] | [{},{},...] | null}
    tbody.innerHTML = ''
    if (response.success) {
        response.articles.forEach(article => {
            //console.log(article)
            tbody.innerHTML += `<tr>
                                    <td>${article.description}</td>
                                    <td class="price">$${article.price.toFixed(2)}</td>
                                    <td>${article.code}</td>
                                    <td>${article.family}</td>
                                    <td>
                                        <button class="bt-edit" onclick="editArticle(${article.id})"><img src="src/img/edit24px.png" alt="Editar" title="Editar"></button>
                                        <button class="bt-del" onclick="delArticle(${article.id}, '${article.description}')"><img src="src/img/trash24px.png" alt="Eliminar" title="Eliminar"></button>
                                    </td>
                                </tr>`
        });
        countArticles.innerHTML = response.articles.length + ' artículo'
        if (response.articles.length !== 1)
            countArticles.innerHTML += 's'
        countArticles.innerHTML += ' encontrado'
        if (response.articles.length !== 1)
            countArticles.innerHTML += 's' 
        countArticles.innerHTML += ' con el texto <b>' + searchArticle.value.trim() + '</b>'
    }
    searchArticle.value = ''
}

function addFamily(event) {
    event.preventDefault()
    //console.log('addFamily')    
    formFamilyTitle.innerText = 'Agregar familia'
    formFamily.id.value = '0' // 0=add
    formFamily.description.value = ''
    modalAddEditFamily.style.display = 'flex'
    formFamily.description.focus()
}

function editFamily(event) {
    event.preventDefault()
    //console.log('editFamily')
    formFamilyTitle.innerText = 'Editar familia'
    formFamily.id.value = form.family.options[form.family.selectedIndex].value // id a modificar
    formFamily.description.value = form.family.options[form.family.selectedIndex].innerText
    modalAddEditFamily.style.display = 'flex'
    formFamily.description.focus()
}

function delFamily(event) {
    event.preventDefault()
    //console.log('delFamily')
    idFamilyDel.value = form.family.options[form.family.selectedIndex].value // id a eliminar
    descriptionFamilyDel.innerText = form.family.options[form.family.selectedIndex].innerText
    modalFamilyDel.style.display = 'flex'   
}

function closeModalFamilies() {
    modalFamilies.click()
}

function closeModalAddEditFamily() {
    modalAddEditFamily.click()
}

async function loadSelectFamilies() {
    // cargar familias en el combo
    const response = await executeGet('src/php/db.php?fn=getFamilies&idUser=' + idUserSelected)
    //console.log(response) // {success: false|true, families: null|[{},{}]}
    if (response.success) {
        const visibility =  response.families.length > 0 ? 'visible' : 'hidden'
        btEditFamily.style.visibility = visibility
        btDelFamily.style.visibility = visibility
        form.family.innerHTML = ''
        response.families.forEach(family => {
            form.family.innerHTML += `<option value="${family.id}">${family.description}</option>`
        })
    }    
}

btCancel.onclick = () => {
    modal.click() // emula click y param eve sera de el mismo
}

btOkDel.onclick = async () => {
    //console.log('btOkDel')
    divDelArticle.style.display = 'none'
    formData = new FormData
    formData.append('fn', 'delArticle')
    formData.append('idUser',idUserSelected)
    formData.append('id', idToDel.value)
    const response = await executePost('src/php/db.php', formData) // script.js
    //console.log(response)
    //return
    if (response.success) {
        showStatusDel('El artículo ha sido eliminado.', false) // form.js
        setTimeout(() => {
            modalDel.click() // se genera el param 'eve'
            divDelArticle.style.display = 'block'
            searchArticle.value = searchText == '' ? '.' : searchText 
            loadArticles()
        }, 2000)
    }
    else 
        showStatusDel() // form.js 
}

btCancelDel.onclick = () => {
    //console.log('btCancelDel')
    modalDel.click()
}

// confirm del family
btOkFamilyDel.onclick = async () => {
    //console.log('btOkDel')
    divDelFamily.style.display = 'none'
    formData = new FormData()
    formData.append('fn', 'delFamily')
    formData.append('idUser', idUserSelected)
    formData.append('id', idFamilyDel.value)
    const response = await executePost('src/php/db.php', formData) // script.js
    //console.log(response)
    if (response.success) {
        showStatusFamilyDel('La familia ha sido eliminada.', false) // form.js
        loadSelectFamilies() // reload families into combo
        setTimeout(() => {
            // cerrar modal
            modalFamilyDel.click() // se genera el param 'eve'
            divDelFamily.style.display = 'block'            
        }, 2000)
    }
    else 
        showStatusFamilyDel() // form.js 
}

btCancelFamilyDel.onclick = () => {
    modalFamilyDel.style.display = 'none'
}

modal.onclick = (eve) => {
    if (eve.target == modal) {
        modal.style.display = 'none'
        // limpiar form
        form.reset()
        statusArea.style.display = 'none'
    }
}

modalDel.onclick = (eve) => {
    if (eve.target == modalDel) {
        modalDel.style.display = 'none'
        // limpiar form
        statusDelArea.style.display = 'none'
    }
}

modalFamilyDel.onclick = (eve) => {
    if (eve.target == modalFamilyDel) {
        modalFamilyDel.style.display = 'none'
        // ocultar status
        statusFamilyDelArea.style.display = 'none'
    }
}

btCloseStatus.onclick = () => {
    //console.log('closeStatus')
    statusArea.style.display = 'none'
}

btCloseStatusDel.onclick = () => {
    //console.log('closeStatus')
    statusDelArea.style.display = 'none'
}

btCloseStatusFamily.onclick = () => {
    statusFamilyArea.style.display = 'none'
}

btCloseStatusFamilyDel.onclick = () => {
    statusFamilyDelArea.style.display = 'none'
}

modalAddEditFamily.onclick = (eve) => {
    if (eve.target == modalAddEditFamily) {
        modalAddEditFamily.style.display = 'none'
        statusFamilyArea.style.display = 'none'
    }
}

// add edit family save
formFamily.onsubmit = async (eve) => {
    eve.preventDefault()
    //console.log('submit')
    
    // verificar si ya existe la family
    const description = formFamily.description.value.trim()
    const response = await executeGet('src/php/db.php?fn=isFamilyExists&idUser=' + idUserSelected + '&description=' + description + '&id=' + formFamily.id.value)
    //console.log(response) // {success: false|true, exists: null|false|true}
    if (response.success) {
        if (!response.exists) {
            formFamily.style.display = 'none'
            let formData = new FormData()
            formData.append('fn', 'addUpdateFamily')
            formData.append('idUser', idUserSelected)
            formData.append('id', formFamily.id.value) // id=0 add, id>0 edit
            formData.append('description', description)
            const response = await executePost('src/php/db.php', formData)
            //console.log(response) // {success: false|true}
            if (response.success) {
                const txt = formFamily.id.value == '0' 
                    ? 'La nueva familia ha sido agregada.'
                    : 'La familia ha sido modificada.'
                showStatusFamily(txt, false) // form.js

                // si fue una modificacion, tomar el id actual
                let idCurrent = '0';
                if (formFamily.id.value != '0') {
                    idCurrent = form.family.options[form.family.selectedIndex].value
                    //console.log('id antes de modificar: ', idCurrent)
                }
                await loadSelectFamilies() // recargar familias en el combo
                
                // si recien agregada, seleccionar la de value mayor
                if (formFamily.id.value == '0') {
                    let idMax = 0
                    let index = 0
                    for (const option of form.family.options) {
                        //console.log(option)
                        if (idMax < parseInt(option.value)) {
                            idMax = parseInt(option.value)
                            index = option.index
                        }    
                    }
                    //console.log(index)
                    form.family.selectedIndex = index
                }
                // seleccionar el mismo value(id) antes de ser modificado
                else {
                    //console.log('id a encontrar despues de modificar: ', idCurrent)
                    for (const option of form.family.options) {
                        if (option.value == idCurrent) {
                            //console.log('option.value=', option.value)
                            form.family.selectedIndex = option.index
                            break; 
                        }    
                    }
                }
                
                setTimeout(() => {
                    modalAddEditFamily.click() // cerrar form addEdit family
                    formFamily.style.display = 'block'
                }, 2000)
            }
            // error en post
            else
                showStatusFamily()
        }
        else
            showStatusFamily('La familia ya existe.')
    }
    // error
    else
        showStatusFamily() // form.js
}

form.onsubmit = async (eve) => {
    eve.preventDefault()
    
    // verificar si que no existe codigo
    let isContinue = false
    let value = form.code.value.trim()
    //console.log(code) 
    if (value.length > 0) {
        let response = await executeGet('src/php/db.php?fn=isCodeExists&idUser=' + idUserSelected + '&code=' + value + '&id=' + form.id.value) // id=0 para no encontrar ningun id igual
        // codigo ya existe
        if (response.success) 
            showStatus('El código ya existe para...<br><b>' + response.description + '</b>.') // form.js
        else
            isContinue = true
    }
    else
        isContinue = true
    
    // codigo vacio o no existe, description es obligatorio
    if (isContinue) {
        isContinue = false
        value = form.description.value.trim()
        //console.log(value)
        response = await executeGet('src/php/db.php?fn=isDescriptionExists&idUser=' + idUserSelected + '&description=' + value + '&id=' + form.id.value) // id=0 para no encontrar ningun id igual
        //console.log(response)
        // codigo ya existe
        if (response.success) 
            showStatus('La descripción ya existe.') // form.js
        else
            isContinue = true        
    }

    // descripcion no existe o es del mismo articulo a modificar
    if (isContinue) {
        form.style.display = 'none'
        let formData = new FormData(form)
        formData.append('fn', 'addUpdateArticle')
        formData.append('idUser', idUserSelected)
        response = await executePost('src/php/db.php', formData) // script.js
        //console.log(response) // {success: true}
        if (response.success) {
            const statusTxt = form.id.value == '0' 
                ? 'El nuevo artículo se ha agregado.'
                : 'El artículo ha sido modificado.' 
            showStatus(statusTxt, false)
            setTimeout(() => {
                btCancel.click()
                form.style.display = 'block'
                // load articles
                searchArticle.value = searchText == '' ? '.' : searchText
                loadArticles()
            }, 2000)                   
        }
    }
}

window.onload = async () => {    
    if (!sessionStorage.id) {
        window.location.href = 'input.html'
    }
    else {
        btDashboard.classList.add('active')
        btDashboardList.classList.add('active')
        searchArticle.focus()
        await loadUsersSelect() 
        idUserSelected = selUsers.options[selUsers.selectedIndex].value     
        // searchArticle.value = '*'
        // loadArticles()  
    }    
} 