let searchText = ''
let idUserSelected = sessionStorage.prices_id

async function addArticle() {
    let isContinue = false;

    /// verify if user is 'pruebas'
    if (sessionStorage.prices_id == '0') {
        /// 10 articles max 
        const res = await executeGet(`src/php/db.php?fn=getCountArticles&idUser=${sessionStorage.prices_id}`)
        //console.log(res) // {success: true, exception: '', result: 1}
        if (res.success) {
            if (res.result < 10)
                isContinue = true
            else
                alert('Usuario de pruebas solo 10 articulos maximo')
        }
    }
    else
        isContinue = true;
    if (isContinue) {
        formTitle.innerText = 'Agregar artículo'
        form.id.value = '0' // 0=agregar
        loadSelectFamilies()
        modal.style.display = 'flex'
        form.code.focus()
    }
}

async function editArticle(id, copy = false) {
    // solicitud de datos del articulo
    const response = await executeGet(`src/php/db.php?fn=getArticle&idUser=${sessionStorage.prices_id}&id=${id}`)
    //console.log(response) // success:false|true, exception:<string>|null, result:null|false|{object}
    if (response.success && response.result) {
        formTitle.innerText = copy ? 'Agregar artículo' : 'Modificar artículo'
        form.id.value = copy ? '0' : id // si es clon, id sera cero para agregar
        form.code.value = response.result.code
        form.description.value = response.result.description
        form.price.value = response.result.price
        form.cost.value = response.result.cost
        await loadSelectFamilies()

        // seleccionar su familia
        for (const option of form.idFamily.options) {
            
            //console.log('option: ', option.innerText, ', family: ', response.result.family);
            if (option.value == response.result.idFamily) {
                //console.log(option.value)
                form.idFamily.selectedIndex = option.index;
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

    // solo si es super-admin pedir lista de usuarios id-username
    if (sessionStorage.prices_id === '1') {
        let formData = new FormData()
        formData.append('fn', 'getUsersMinimal')
        const response = await executePost('src/php/db.php', formData)
        //console.log(response) // { success: false|true, exception:<string>|null, result:null | [{},{},...] }
        // fill select
        selUsers.innerHTML = '' // limpiar combo
        response.result.forEach(user => {
            //console.log(user)
            selUsers.innerHTML += `<option value="${user.id}">${user.username}</option>`
        })

        // select super-admin    
        for (const option of selUsers.options) {
            if (option.value == '1') {
                selUsers.selectedIndex = option.index
                break;
            }
        }
    }
    // solo el usuario loggeado
    else {
        selUsers.innerHTML = `<option value="${sessionStorage.prices_id}">${sessionStorage.prices_username}</option>`
        selUsers.style.visibility = 'hidden'
    }
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
    //console.log(response) // success: false|true, exception: <string>|null, result: null|[]|[{},{},...]

    tbody.innerHTML = ''
    if (response.success) {
        response.result.forEach(article => {
            //console.log(article)
            const cost = article.cost == null ? '' : '$' + article.cost.toFixed(2)
            tbody.innerHTML += `<tr>
                                    <td>${article.description}</td>
                                    <td class="price">$${article.price.toFixed(2)}</td>
                                    <td class="cost">${cost}</td>
                                    <td>${article.code}</td>
                                    <td>${article.family}</td>
                                    <td>
                                        <button class="bt-edit" onclick="editArticle(${article.id})"><i class="fa fa-edit fa-lg"></i></button>
                                        <button class="bt-copy" onclick="editArticle(${article.id}, true)"><i class="fa fa-copy fa-lg"></i></button>
                                        <button class="bt-del" onclick="delArticle(${article.id}, '${article.description}')"><i class="fa fa-trash fa-lg"></i></button>
                                    </td>
                                </tr>`
        });
        countArticles.innerHTML = response.result.length + ' artículo'
        if (response.result.length !== 1)
            countArticles.innerHTML += 's'
        countArticles.innerHTML += ' encontrado'
        if (response.result.length !== 1)
            countArticles.innerHTML += 's'
        countArticles.innerHTML += ' con "<b>' + searchArticle.value.trim() + '</b>"'
    }
    searchArticle.value = ''
    // si el body es mayor al port-view vertical, mostrar flecha arriba
    arrowUp.style.display = document.body.clientHeight > window.innerHeight ? 'block' : 'none'
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
    formFamily.id.value = form.idFamily.options[form.idFamily.selectedIndex].value // id a modificar
    formFamily.description.value = form.idFamily.options[form.idFamily.selectedIndex].innerText
    modalAddEditFamily.style.display = 'flex'
    formFamily.description.focus()
}

function delFamily(event) {
    event.preventDefault()
    //console.log('delFamily')
    idFamilyDel.value = form.idFamily.options[form.idFamily.selectedIndex].value // id a eliminar
    descriptionFamilyDel.innerText = form.idFamily.options[form.idFamily.selectedIndex].innerText
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
    //console.log(response) // {success: false|true, exception: <string>|null, result: null|[]|[{},{}]}
    if (response.success) {
        const visibility = response.result.length > 0 ? 'visible' : 'hidden'
        btEditFamily.style.visibility = visibility
        btDelFamily.style.visibility = visibility
        form.idFamily.innerHTML = ''
        response.result.forEach(family => {
            form.idFamily.innerHTML += `<option value="${family.id}">${family.description}</option>`
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
    formData.append('idUser', sessionStorage.prices_id)
    formData.append('id', idToDel.value)
    const response = await executePost('src/php/db.php', formData) // script.js
    //console.log(response) // success:false|true, exception:<string>|null, result:null|true
    //return
    if (response.success && response.result) {
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
    formData.append('idUser', sessionStorage.prices_id)
    formData.append('id', idFamilyDel.value)
    const response = await executePost('src/php/db.php', formData) // script.js
    //console.log(response) // # success:false|true, exception:<string>|null, result:null|true
    if (response.success && response.result) {
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
    //console.log(response) // {success: false|true, exception: <string>|null, result: null|false|true}
    if (response.success) {
        if (!response.result) {
            formFamily.style.display = 'none'
            let formData = new FormData()
            formData.append('fn', 'addUpdateFamily')
            formData.append('idUser', idUserSelected)
            formData.append('id', formFamily.id.value) // id=0 add, id>0 edit
            formData.append('description', description)
            const response = await executePost('src/php/db.php', formData)
            //console.log(response) // {success:false|true, exception:<string>|null, result:null|true}
            if (response.success && response.result) {
                const txt = formFamily.id.value == '0'
                    ? 'La nueva familia ha sido agregada.'
                    : 'La familia ha sido modificada.'
                showStatusFamily(txt, false) // form.js

                // si fue una modificacion, tomar el id actual
                let idCurrent = '0';
                if (formFamily.id.value != '0') {
                    idCurrent = form.idFamily.options[form.idFamily.selectedIndex].value
                    //console.log('id antes de modificar: ', idCurrent)
                }
                await loadSelectFamilies() // recargar familias en el combo

                // si recien agregada, seleccionar la de value mayor
                if (formFamily.id.value == '0') {
                    let idMax = 0
                    let index = 0
                    for (const option of form.idFamily.options) {
                        //console.log(option)
                        if (idMax < parseInt(option.value)) {
                            idMax = parseInt(option.value)
                            index = option.index
                        }
                    }
                    //console.log(index)
                    form.idFamily.selectedIndex = index
                }
                // seleccionar el mismo value(id) antes de ser modificado
                else {
                    //console.log('id a encontrar despues de modificar: ', idCurrent)
                    for (const option of form.idFamily.options) {
                        if (option.value == idCurrent) {
                            //console.log('option.value=', option.value)
                            form.idFamily.selectedIndex = option.index
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

// form add-edit article
form.onsubmit = async (eve) => {
    eve.preventDefault()

    // verificar si que no existe codigo
    let isContinue = false
    let value = form.code.value.trim()
    //console.log(code) 
    if (value.length > 0) {
        let response = await executeGet('src/php/db.php?fn=isCodeExists&idUser=' + idUserSelected + '&code=' + value + '&id=' + form.id.value) // id=0 para no encontrar ningun id igual
        //console.log(response); // success:false|true, exception:<string>|null, result:null|false|{description:<value>} 
        if (response.success) {
            // codigo ya existe
            if (response.result)
                showStatus('El código ya existe para...<br><b>' + response.result.description + '</b>.') // form.js
            else
                isContinue = true
        }
        // error
        else
            showStatus()
    }
    else
        isContinue = true

    // codigo vacio o no existe, description es obligatorio
    if (isContinue) {
        isContinue = false
        value = form.description.value.trim()
        //console.log(value)
        response = await executeGet('src/php/db.php?fn=isDescriptionExists&idUser=' + idUserSelected + '&description=' + value + '&id=' + form.id.value) // id=0 para no encontrar ningun id igual
        //console.log(response) // success:false|true, exception:<string>|null, result:null|false|true
        if (response.success) {
            // codigo ya existe
            if (response.result)
                showStatus('La descripción ya existe.') // form.js
            else
                isContinue = true
        }
        // error
        else
            showStatus()
    }

    // descripcion no existe o es del mismo articulo a modificar
    if (isContinue) {
        form.style.display = 'none'
        let formData = new FormData(form)
        formData.append('fn', 'addUpdateArticle')
        formData.append('idUser', idUserSelected)
        response = await executePost('src/php/db.php', formData) // script.js
        //console.log('antes de guardar:', response) // success:false|true, exception:<string>|null, result:null|true
        if (response.success && response.result) {
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
    if (!sessionStorage.prices_id) {
        window.location.href = 'input.html'
    }
    else {
        btDashboard.classList.add('active')
        btDashboardList.classList.add('active')
        btDashboardFoot.classList.add('active')
        username.innerText = 'Usuario: ' + sessionStorage.prices_username
        searchArticle.focus()
        await loadUsersSelect()
        idUserSelected = selUsers.options[selUsers.selectedIndex].value
        // searchArticle.value = '*'
        // loadArticles()                
    }
}