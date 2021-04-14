window.onload = () => {
    if (!sessionStorage.id)
        window.location.href = 'input.html'
    else {
        btUsers.classList.add('active')
        btUsersList.classList.add('active')
    }    
}