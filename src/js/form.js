function showStatus(txt='Ocurrió un error de comunicación', isError=true) {
    const color = isError ? 'salmon' : 'lightgreen'
    statusText.innerHTML = txt
    statusArea.style.backgroundColor = color
    statusArea.style.display = 'block'
}

function showStatusDel(txt='Ocurrió un error de comunicación', isError=true) {
    const color = isError ? 'salmon' : 'lightgreen'
    statusDelText.innerHTML = txt
    statusDelArea.style.backgroundColor = color
    statusDelArea.style.display = 'block'
}