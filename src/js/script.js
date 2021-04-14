// menu emergente al final para quedar mas arriba en la pila o z
let html = `<aside class="menu-aside">
                <img class="menu-close" src="src/img/close24px.png" onclick="menuClose()">
                <ul class="menu-list">
                    <li><a href="index.html" id="btIndexList">Inicio</a></li>`
if (!sessionStorage.id)
    html +=        `<li><a href="input.html" id="btInputList">Ingresar</a></li>`
if (sessionStorage.id)
    html +=        `<li><a href="dashboard.html" id="btDashboardList">Precios</a></li>
                    <li><a href="mydata.html" id="btMyDataList">Mis datos</a></li>`
if (sessionStorage.id == '1') // super-admin
    html +=        `<li><a href="users.html" id="btUsersList">Usuarios</a></li>`
    html +=        `<li><a href="exit.html" id="btExitList">Salir</a></li>`
    html +=    `</ul>
            </aside>`

const menuHidden = document.getElementById('menuHidden')
menuHidden.innerHTML = html
menuHidden.onclick = (eve) => {
    if (eve.target == menuHidden) {
        menuHidden.style.display = 'none'
        menuHidden.firstChild.style.width = '0px'
    }
}

// header
html = `<img src="src/img/logo300x185px.png" alt="Logotipo">
            <span>Checador de precios</span>
            <nav class="navMain">
                <a href="index.html" id="btIndex">Inicio</a>`
if (!sessionStorage.id)
    html +=    `<a href="input.html" id="btInput">Ingresar</a>`
//var id = sessionStorage.getItem('id')
if (sessionStorage.id)
    html +=    `<a href="dashboard.html" id="btDashboard">Precios</a>
                <a href="mydata.html" id="btMyData">Mis datos</a>`
if (sessionStorage.id == '1') // super-admin
    html +=    `<a href="users.html" id="btUsers">Usuarios</a>`
if (sessionStorage.id)
    html +=    `<a href="exit.html" id="btExit">Salir</a>`            
html +=    `</nav>
            <img class="btmenu" src="src/img/menu32px.png" onclick="menuOpen()">`           

document.getElementsByTagName('header')[0].innerHTML = html

// footer
document.getElementsByTagName('footer')[0].innerHTML = `<p>footer.html</p>`

async function executeGet(url, isText=false) {
    let echo = isText ? '' : []
    await fetch(url)
    .then(response => {
        return isText ? response.text() : response.json()
    })
    .then(data => {
        //console.log(data)
        echo = data
    })
    .catch(error => {
        console.error(error)
    })
    return echo
}

async function executePost(url, data, isText=false) {
    let echo = isText ? '' : []
    await fetch(url, {
        method: 'post',
        body: data, 
    })
    .then(response => {
        return isText ? response.text() : response.json()
    })
    .then(data => {
        //console.log(data)
        echo = data
    })
    .catch(error => {
        console.error(error)
    })
    return echo
}

function menuClose() {
    //console.log(menuHidden)
    menuHidden.style.display = 'none'
    menuHidden.firstChild.style.width = '0px'
}

function menuOpen() {
    //console.log(menuHidden.firstChild) // menu-aside
    menuHidden.style.display = 'block'
    setTimeout(() => menuHidden.firstChild.style.width = '160px', 1)    
}