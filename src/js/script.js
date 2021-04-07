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

async function getHtml(element) {
    const path = 'src/html/' + element + '.html'
    const html = await executeGet(path, true)
    //console.log(html)
    document.getElementsByTagName(element)[0].innerHTML = html
    //console.log(window.location.pathname.split('/').reverse()[0])
    switch (window.location.pathname.split('/').reverse()[0]) {
        case 'index.html':
            btIndex.classList.add('active')
            break;

        case 'input.html':
            btInput.classList.add('active')
            break;
    }
}

window.onload = () => {
    getHtml('header')
    getHtml('footer')
}