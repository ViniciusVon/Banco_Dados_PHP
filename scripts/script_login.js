// Obtém o formulário de login pelo seu ID
let loginForm = document.getElementById("loginForm");

loginForm.addEventListener("submit", (e) => {
  e.preventDefault(); // Evita o envio padrão do formulário

  let username = document.getElementById("username").value;
  let password = document.getElementById("password").value;
  let rememberMe = document.getElementById("rememberMe");

  if (username === "" || password === "") {
    alert("Usuário e/ou Senha em branco"); // Exibe um alerta se algum campo estiver em branco
  } else {
    let payload = { 'username': username, 'password': password };
    let jsonData = JSON.stringify(payload); // Converte o payload para JSON

    let xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
      if (this.readyState === 4 && this.status === 200) {
        let resposta = this.responseText; // Obtém a resposta do servidor
        if (rememberMe.checked) {
          localStorage.setItem("token", resposta);
          localStorage.setItem("username", username);
        } else {
          sessionStorage.setItem("token", resposta);
          sessionStorage.setItem("username", username);
        }
        window.location.href = "./pages/home.html";
      } else if (this.status === 406) {

        let resposta_erro = document.querySelector('#resposta_erro');
        resposta_erro.style.display = 'flex'; // Torna visível o elemento de resposta de erro
      }
    };
    xhr.open("POST", "./backend/LDAP.php", true);
    xhr.setRequestHeader("Content-Type", "application/json"); // Define o tipo de conteúdo da requisição como JSON
    xhr.send(jsonData); // Envia a requisição com os dados JSON
  }
});