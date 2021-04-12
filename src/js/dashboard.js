let txt = ''

function addArticle() {
    formTitle.innerText = 'Agregar artículo'
    form.id.value = '0' // 0=agregar
    modal.style.display = 'flex'
    form.code.focus()
}

async function editArticle(id) {
    // solicitud de datos del articulo
    const response = await executeGet('src/php/db.php?fn=getArticle&id=' + id)
    //console.log(response) // {success: false|true, article: null|{}}
    if (response.success) {
        formTitle.innerText = 'Modificar artículo'
        form.id.value = id
        form.code.value = response.article.code
        form.description.value = response.article.description
        form.price.value = response.article.price
        modal.style.display = 'flex'
        form.code.focus()
    }
}

function delArticle(id, description) {
    //console.log(id, description)
    idToDel.value = id
    descriptionToDel.innerText = description + '?'
    modalDel.style.display = 'flex'
}

async function loadArticles() {
    txt = (searchArticle.value.trim())
    if (txt == '*')
        txt = ''
    const response = await executeGet('src/php/db.php?fn=getArticles&txt=' + txt) // script.js
    //console.log('getArticles: ', response) // {success: true | false, articles: [] | [{},{},...] | null}
    tbody.innerHTML = ''
    if (response.success) {
        response.articles.forEach(article => {
            //console.log(article)
            tbody.innerHTML += `<tr>
                                    <td>${article.code}</td>
                                    <td>${article.description}</td>
                                    <td class="price">${article.price.toFixed(2)}</td>
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

function addFamily() {
    //console.log('addFamily')
    formFamilyTitle.innerText = 'Agregar familia'
    formFamily.id.value = '0'
    formFamily.description.value = ''
    modalAddEditFamily.style.display = 'flex'
    formFamily.description.focus()
}

function editFamily(id, description) {
    //console.log('editFamily', id, description)
    formFamilyTitle.innerText = 'Editar familia'
    formFamily.id.value = id
    formFamily.description.value = description
    modalAddEditFamily.style.display = 'flex'
    formFamily.description.focus()
}

function delFamily(id, description) {
    console.log('delFamily', id, description)
}

function closeModalFamilies() {
    modalFamilies.click()
}

function closeModalAddEditFamily() {
    modalAddEditFamily.click()
}

btCancel.onclick = () => {
    modal.click() // emula click y param eve sera de el mismo
}

btOkDel.onclick = async () => {
    //console.log('btOkDel')
    formData = new FormData
    formData.append('fn', 'delArticle')
    formData.append('id', idToDel.value)
    const response = await executePost('src/php/db.php', formData) // script.js
    //console.log(response)
    if (response.success) {
        showStatusDel('El artículo ha sido eliminado.', false) // form.js
        setTimeout(() => {
            modalDel.click() // se genera el param 'eve'
            if (txt == '')
                txt = '*'
            searchArticle.value = txt   
            loadArticles()
        }, 2500)
    }
    else 
        showStatusDel() // form.js 
}

btCancelDel.onclick = () => {
    //console.log('btCancelDel')
    modalDel.click()
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


btCloseStatus.onclick = () => {
    //console.log('closeStatus')
    statusArea.style.display = 'none'
}

btCloseStatusDel.onclick = () => {
    //console.log('closeStatus')
    statusDelArea.style.display = 'none'
}

btEditFamilies.onclick = async (eve) => {
    eve.preventDefault()
    //console.log('click')

    // cargar familias en la tabla de edicion
    const response = await executeGet('src/php/db.php?fn=getFamilies')
    //console.log(response) {success: false|true, families: null|[{},{}]}
    if (response.success) {
        tbodyFamilies.innerHTML = ''
        response.families.forEach(family => {
            tbodyFamilies.innerHTML += `<tr>
                                            <td>${family.description}</td>
                                            <td>
                                                <button class="bt-edit" onclick="editFamily(${family.id}, '${family.description}')"><img src="src/img/edit24px.png" alt="Editar" title="Editar"></button>
                                                <button class="bt-del" onclick="delFamily(${family.id}, '${family.description}')"><img src="src/img/trash24px.png" alt="Eliminar" title="Eliminar"></button>
                                            </td>
                                        </tr>`
        })
        footTextFamilies.innerText = response.families.length
        footTextFamilies.innerText += ' familia' 
        if (response.families.length != 1)
            footTextFamilies.innerText += 's'
        footTextFamilies.innerText += ' encontradas.'
        modalFamilies.style.display = 'flex'
    }
}

modalFamilies.onclick = (eve) => {
    if (eve.target == modalFamilies) {
        modalFamilies.style.display = 'none'
    }
}

modalAddEditFamily.onclick = (eve) => {
    if (eve.target == modalAddEditFamily) {
        modalAddEditFamily.style.display = 'none'
    }
}

formFamily.onsubmit = (eve) => {
    eve.preventDefault()
    //console.log('submit')
    
    // verificar si ya existe la description
    const response = await executeGet('src/php/db.php?fn=isFamilyExists&description=' + formFamily.description.value + '&id=' + formFamily.id.value)
    console.log(response)
}

form.onsubmit = async (eve) => {
    eve.preventDefault()
    
    // verificar si que no existe codigo
    let isContinue = false
    let value = form.code.value.trim()
    //console.log(code) 
    if (value.length > 0) {
        let response = await executeGet('src/php/db.php?fn=isCodeExists&code=' + value + '&id=' + form.id.value) // id=0 para no encontrar ningun id igual
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
        response = await executeGet('src/php/db.php?fn=isDescriptionExists&description=' + value + '&id=' + form.id.value) // id=0 para no encontrar ningun id igual
        //console.log(response)
        // codigo ya existe
        if (response.success) 
            showStatus('La descripción ya existe.') // form.js
        else
            isContinue = true        
    }

    // descripcion no existe o es del mismo articulo a modificar
    if (isContinue) {
        let formData = new FormData(form)
        formData.append('fn', 'addUpdateArticle')
        response = await executePost('src/php/db.php', formData) // script.js
        //console.log(response) // {success: true}
        if (response.success) {
            const statusTxt = form.id.value == '0' 
                ? 'El nuevo artículo se ha agregado.'
                : 'El artículo ha sido modificado.' 
            showStatus(statusTxt, false)
            setTimeout(() => {
                btCancel.click()
                if (txt == '')
                    txt = '*'
                searchArticle.value = txt
                loadArticles()
            }, 2500)                   
        }
    }
}

window.onload = async () => {
    console.log('localStorage.token:', localStorage.token)
    console.log('sessionStorage.id: ', sessionStorage.id) 
    console.log('sessionStorage.username: ', sessionStorage.username)
    if (!sessionStorage.id) {
        window.location.href = 'input.html'
    }
    btDashboard.classList.add('active')
    
    // cargar familias
    const response = await executeGet('src/php/db.php?fn=getFamilies')
    //console.log(response) // {success: false|true, families: null|[{},{}]}
    if (response.success) {
        form.family.innerHTML = ''
        response.families.forEach(family => {
            form.family.innerHTML += `<option value="${family.id}">${family.description}</option>`
        })
    }    
    searchArticle.focus()
    //searchArticle.value = '*'
    //loadArticles()    
} 