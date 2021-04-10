function showStatus(txt='Ocurrió un error de comunicación', isError=true) {
    const color = isError ? 'salmon' : 'lightgreen'
    statusText.innerHTML = txt
    statusArea.style.backgroundColor = color
    statusArea.style.display = 'block'
}