window.onload = () => {
    var token = localStorage.getItem('token')
    var id = sessionStorage.getItem('id')
    console.log('token:', token)
    console.log('id: ', id) 
    sessionStorage.clear()
    window.location.replace('input.html')
}  
