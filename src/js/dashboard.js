function addArticle() {
    formTitle.innerText = 'Agregar artículo'
    form.id.value = '0' // 0=agregar
    modal.style.display = 'flex'
    form.code.focus()
}

async function editArticle(id) {
    // solicitud de datos del articulo
    const article = await executeGet('src/php/db.php?fn=getArticle&id=' + id)
    //console.log(article)
    formTitle.innerText = 'Modificar artículo'
    form.id.value = id
    form.code.value = article.code
    form.description.value = article.description
    form.price.value = article.price
    modal.style.display = 'flex'
    form.code.focus()
}

function delArticle(id) {
    console.log('del-' + id)
}

async function loadArticles() {
    let txt = (searchArticle.value.trim())
    if (txt == '*')
        txt = ''
    const articles = await executeGet('src/php/db.php?fn=getArticles&txt=' + txt) // script.js
    console.log('getArticles: ', articles)
    tbody.innerHTML = ''
    articles.forEach(element => {
        //console.log(element)
        tbody.innerHTML += `<tr>
                                <td>${element.code}</td>
                                <td>${element.description}</td>
                                <td class="price">${element.price.toFixed(2)}</td>
                                <td>${element.family}</td>
                                <td>
                                    <button class="bt-edit" onclick="editArticle(${element.id})">Modificar</button>
                                    <button class="bt-del" onclick="delArticle(${element.id})">Eliminar</button>
                                </td>
                            </tr>`
    });
    countArticles.innerHTML = articles.length + ' artículo'
    if (articles.length !== 1)
        countArticles.innerHTML += 's'
    countArticles.innerHTML += ' encontrados con el texto <b>' + searchArticle.value.trim() + '</b>'
    searchArticle.value = ''
}

btCancel.onclick = () => {
    modal.click() // emula click y param eve sera de el mismo
}

modal.onclick = (eve) => {
    if (eve.target == modal) {
        modal.style.display = 'none'
        // limpiar form
        form.reset()
        statusArea.style.display = 'none'
    }
}

btCloseStatus.onclick = () => {
    //console.log('closeStatus')
    statusArea.style.display = 'none'
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
            showStatus('El código ya existe...<br><b>' + response.description + '</b>.') // form.js
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
            const txt = form.id.value == '0' 
                ? 'El nuevo artículo se ha agregado.'
                : 'El artículo ha sido modificado.' 
            showStatus(txt, false)
            setTimeout(() => btCancel.click(), 2500)                   
        }
    }
}

window.onload = async () => {
    console.log('localStorage.token:', localStorage.token)
    console.log('sessionStorage.id: ', sessionStorage.id) 
    if (!sessionStorage.id) {
        window.location.href = 'input.html'
    }
    btDashboard.classList.add('active') 
    searchArticle.focus()    
} 