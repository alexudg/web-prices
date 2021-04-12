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

function showStatusFamily(txt='Ocurrió un error de comunicación', isError=true) {
    const color = isError ? 'salmon' : 'lightgreen'
    statusFamilyText.innerHTML = txt
    statusFamilyArea.style.backgroundColor = color
    statusFamilyArea.style.display = 'block'
}

function showStatusFamilyDel(txt='Ocurrió un error de comunicación', isError=true) {
    const color = isError ? 'salmon' : 'lightgreen'
    statusFamilyDelText.innerHTML = txt
    statusFamilyDelArea.style.backgroundColor = color
    statusFamilyDelArea.style.display = 'block'
}