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

let html = `<img src="src/img/logo300x243px.png" alt="">
            <nav>
                <a href="index.html" id="btIndex">Inicio</a>
                <a href="input.html" id="btInput">Ingresar</a>`
var id = sessionStorage.getItem('id')
if (id)
    html +=    '<a href="exit.html" id="btExit">Salir</a>'            
html +=    `</nav>`            

document.getElementsByTagName('header')[0].innerHTML = html

document.getElementsByTagName('footer')[0].innerHTML =
    `<p>footer.html</p>`