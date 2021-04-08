window.onload = () => {
    sessionStorage.clear()
    localStorage.removeItem('token')
    window.location.replace('input.html')
}  
