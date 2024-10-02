// Adiciona um evento de clique ao botão de logout
document.getElementById('logoutButton').addEventListener('click', function() {
    logout(); // Chama a função de logout quando o botão é clicado
});

function logout() {
    const token = localStorage.getItem('jwtToken');
    
    localStorage.removeItem('jwtToken'); // Remove o token do localStorage para efetuar o logout local

    if (token) {
        fetch('/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json', // Tipo de conteúdo da requisição
                'Authorization': `Bearer ${token}` // Adiciona o token JWT no cabeçalho Authorization
            }
        }).then(response => {
            if (response.ok) {
                window.location.href = '/login';
            } else {
                console.error('Logout failed'); // Adiciona log de erro para falha na requisição de logout
            }
        });
    } else {
        window.location.href = '/login';
    }
}