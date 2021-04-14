window.onload = () => {
    localStorage.removeItem('token')
    sessionStorage.clear()
    window.location.replace('input.html')
}  
