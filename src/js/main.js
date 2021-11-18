//console.log('localStorage.prices_token:', localStorage.prices_token)
//console.log('sessionStorage:', sessionStorage)

// header
let html = `<a href="index.html"><img src="src/img/logo62x40.png" alt="Logotipo"></a>
        <h3>Checador de precios</h3>
        <nav class="navMain">
            <a href="index.html" id="btIndex">Inicio</a>`
if (!sessionStorage.prices_id)
    html +=`<a href="input.html" id="btInput">Ingresar</a>`
//var id = sessionStorage.getItem('id')
if (sessionStorage.prices_id)
    html +=`<a href="dashboard.html" id="btDashboard">Precios</a><a href="mydata.html" id="btMyData">Mis datos</a>` // en linea porque genera espacio si se pasa al sig renglon
if (sessionStorage.prices_id == '1') // super-admin
    html +=`<a href="users.html" id="btUsers">Usuarios</a>`
if (sessionStorage.prices_id)
    html +=`<a href="exit.html" id="btExit">Salir</a>`            
html +=`</nav>
        <i class="fa fa-bars btn-menu" id="btnMenu"></i>`           

document.getElementsByTagName('header')[0].innerHTML = html

// menu emergente al final para quedar mas arriba en la pila o z
html = `<aside class="menu-aside">
<i class="fa fa-times-circle fa-lg btn-menu-close" id="btnMenuClose"></i>
                <ul class="menu-list">
                    <li><a href="index.html" id="btIndexList">Inicio</a></li>`
if (!sessionStorage.prices_id)
    html +=        `<li><a href="input.html" id="btInputList">Ingresar</a></li>`
if (sessionStorage.prices_id)
    html +=        `<li><a href="dashboard.html" id="btDashboardList">Precios</a></li>
                    <li><a href="mydata.html" id="btMyDataList">Mis datos</a></li>`
if (sessionStorage.prices_id == '1') // super-admin
    html +=        `<li><a href="users.html" id="btUsersList">Usuarios</a></li>`
if (sessionStorage.prices_id)
    html +=        `<li><a href="exit.html" id="btExitList">Salir</a></li>`
    html +=    `</ul>
            </aside>`

const menuHidden = document.getElementById('menuHidden')
menuHidden.innerHTML = html
menuHidden.onclick = (eve) => {
    if (eve.target == menuHidden) {
        menuHidden.style.display = 'none'
        menuHidden.firstChild.style.marginRight = '-160px'        
    }
}

// footer
html = `<section>
            <div>
                <nav>
                    <a href="index.html" id="btIndexFoot">Inicio</a>`
if (!sessionStorage.prices_id)
    html +=        `<a href="input.html" id="btInputFoot">Ingresar</a>`
if (sessionStorage.prices_id)
    html +=        `<a href="dashboard.html" id="btDashboardFoot">Precios</a>
                    <a href="mydata.html" id="btMyDataFoot">Mis datos</a>`
if (sessionStorage.prices_id == 1)
    html +=        `<a href="users.html" id="btUsersFoot">Usuarios</a>` 
if (sessionStorage.prices_id)
    html +=        `<a href="exit.html">Salir</a>`       
html +=        `</nav>
                <div>
                    <h4>Tel. 33-1463-1774</h4>
                </div>
            </div>
            <div>
                <div>
                    <a class="contact-social" href="https://api.whatsapp.com/send?phone=523314631774&text=Hola%20vi%20su%20sitio%20web%20y%20deseo%20un%20servicio%20con%20ustedes" target="_blank" style="color: white;"><i class="fab fa-whatsapp fa-2x"></i></a>
                    <a class="contact-social" href="http://facebook.com/PlanetSistemas" target="_blank" style="color: white;"><i class="fab fa-facebook fa-2x"></i></a>
                </div>
                <div>
                    <a href="mailto:planetsistemasmx@gmail.com">planetsistemasmx@gmail.com</a>
                </div>
            </div>
        </section>
        <p>Derechos y recursos reservados Planet Sistemas&reg; <span id="year">2000</span>.</p>`

document.getElementsByTagName('footer')[0].innerHTML = html

document.getElementById('year').innerText = new Date().getFullYear()

async function executeGet(url, isText=false) {
    let echo = isText ? 'error' : {success: false}
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
    let echo = isText ? 'error' : {success: false}
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

btnMenuClose.onclick = () => {
    //console.log(menuHidden)
    menuHidden.style.display = 'none'
    menuHidden.firstChild.style.marginRight = '-160px'
}

btnMenu.onclick = () => {
    //console.log(menuHidden.firstChild) // menu-aside
    menuHidden.style.display = 'block'
    setTimeout(() => menuHidden.firstChild.style.marginRight = '0px', 1)    
}
