function addArticle() {
    console.log('add')
}

function editArticle(id) {
    console.log('edit-' + id)
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
                                    <button onclick="editArticle(${element.id})">Modificar</button>
                                    <button onclick="delArticle(${element.id})">Eliminar</button>
                                </td>
                            </tr>`
    });
    countArticles.innerHTML = articles.length + ' artículo'
    if (articles.length !== 1)
        countArticles.innerHTML += 's'
    countArticles.innerHTML += ' encontrados con el texto <b>' + searchArticle.value.trim() + '</b>'
    searchArticle.value = ''
}

window.onload = async () => {
    console.log('localStorage.token:', localStorage.token)
    console.log('sessionStorage.id: ', sessionStorage.id) 
    if (!sessionStorage.id) {
        window.location.href = 'input.html'
    }
    btDashboard.classList.add('active') 
    
    const article = await executeGet('src/php/db.php?fn=getArticle&id=0') // script.js
    //console.log('articles.length: ', articles.length)
    console.log('article: ', article)  
    
    //loadArticles('')
} 