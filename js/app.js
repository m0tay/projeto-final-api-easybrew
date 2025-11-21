/*****************************************************************************
 *  A P P
 *  
 *  Código para a aplicação web
 *****************************************************************************/
$(document).ready(function() {



  /*****************************************************************************
   *  H O M E P A G E
   *****************************************************************************/
  $(document).on('click', '#home', function() {
    showHomePage();
    clearResponse();
  });

  // Apresentar página principal
  function showHomePage() {

    // Verificar se existe JWT guardado e validar
    var jwt = getCookie('jwt');
    $.post("api/users/validate_token.php", JSON.stringify({ jwt: jwt })).done(function(result) {

      // Se for válido apresentar HOME
      var html = `
                            <div class="card">
                                <div class="card-header">Bem-vindo!</div>
                                <div class="card-body">
                                    <h5 class="card-title">Esta área é restrita.</h5>
                                    <p class="card-text">Apenas um utilizador validado pode aceder às opções Home e Account.</p>
                                </div>
                            </div>
                            `;

      addContent(html);
      showLoggedInMenu();
    })

      // Se for inválido mostrar Login
      .fail(function(result) {
        showLoginPage();
        addResponse("<div class='alert alert-danger'>Faça login para aceder à Área Restrita.</div>");
      });
  }



  /*****************************************************************************
   *  M E N U
   *****************************************************************************/
  // Menu com sessão iniciada
  function showLoggedInMenu() {
    // Esconder login e sign up & mostrar logout
    $("#login, #sign_up").hide();
    $("#logout").show();
  }

  // Menu sem sessão iniciada
  function showLoggedOutMenu() {
    // mostra login e sign up & esconde logout
    $("#login, #sign_up").show();
    $("#logout").hide();
  }



  /*****************************************************************************
   *  L O G I N
   *****************************************************************************/
  // Clicar no menu Login
  $(document).on('click', '#login', function() {
    showLoginPage();
  });

  // Apresentar formulário de Login
  function showLoginPage() {

    // Remover JWT
    setCookie("jwt", "", 1);

    // Criar formulário
    var html = `
        <h2>Login</h2>
        <form id='login_form' method="post">
            <div class='form-group'>
                <label for='email'>Email</label>
                <input type='email' class='form-control' id='email' name='email' placeholder='Email'>
            </div>
 
            <div class='form-group'>
                <label for='password'>Password</label>
                <input type='password' class='form-control' id='password' name='password' placeholder='Password'>
            </div>
 
            <button type='submit' class='btn btn-primary'>Login</button>
        </form>`;

    // Apresentar Formulário e limpar Resposta
    addContent(html);
    clearResponse();
    showLoggedOutMenu();
  }

  // Ao fazer submit no formulário Login
  $(document).on('submit', '#login_form', function() {

    // obter dados do formulário e codificar como JSON
    var login_form = $(this);
    var form_data = JSON.stringify(login_form.serializeObject());

    // enviar os dados para a api
    $.ajax({
      url: "api/users/login.php",
      type: "POST",
      contentType: 'application/json',
      data: form_data,
      success: function(result) {

        // guardar JWT da resposta num cookie
        setCookie("jwt", result.jwt, 1);

        // Apresentar Home e Resposta
        showHomePage();
        addResponse("<div class='alert alert-success'>Utilizador validado.</div>");
      },
      error: function(xhr, resp, text) {

        // Se ocorrer erro, indicar que o login falhou e esvaziar os inputs (apresenta mensagem da API)
        addResponse("<div class='alert alert-danger'>Erro ao efetuar login. Email ou password incorrectos.<br>" + resp + " - " + text + "</div>");
        login_form.find('input').val('');
      }
    });

    return false;
  });


  /*****************************************************************************
   *  L O G O U T
   *****************************************************************************/
  // Clicar no menu Logout
  $(document).on('click', '#logout', function() {

    // Remover JWT
    setCookie("jwt", "", 1);

    // Mostrar Login e Resposta
    showLoginPage();
    addResponse("<div class='alert alert-info'>Sessão terminada.</div>");
  });


  /*****************************************************************************
   *  S I G N U P
   *****************************************************************************/
  // Clicar no menu Sign Up
  $(document).on('click', '#sign_up', function() {

    // Criar formulário
    var html = `
            <h2>Registar</h2>
            <form id='sign_up_form' method="post">
                <div class="form-group">
                    <label for="username">Nome</label>
                    <input type="text" class="form-control" name="username" id="username" required />
                </div>
 
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" name="email" id="email" required />
                </div>
 
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" name="password" id="password" required />
                </div>
 
                <button type='submit' class='btn btn-primary'>Registar</button>
            </form>
            `;

    // Apresentar formulário e limpar Resposta
    addContent(html);
    clearResponse();
  });

  // Ao fazer submit no formulário Sign Up
  $(document).on('submit', '#sign_up_form', function() {

    // Obter dados do formulário e codificar como JSON
    var sign_up_form = $(this);
    var form_data = JSON.stringify(sign_up_form.serializeObject());

    // Enviar os dados para a api
    $.ajax({
      url: "api/users/create.php",
      type: "POST",
      contentType: 'application/json',
      data: form_data,
      success: function(result) {

        // Se success, indicar registo com sucesso e limpar formulário
        showLoginPage();
        addResponse("<div class='alert alert-success'>Utilizador registado. Pode fazer login.</div>");
        sign_up_form.find('input').val('');
      },
      error: function(xhr, resp, text) {

        // Se error, apresentar mensagem de erro
        addResponse("<div class='alert alert-danger'>Não foi possível registar o utilizador. Por favor contacte o administrador.<br>" + resp + " - " + text + "</div>");
      }
    });

    return false;
  });





  /*****************************************************************************
   *  U P D A T E     A C C O U N T
   *****************************************************************************/
  // Clicar no menu Account
  $(document).on('click', '#update_account', function() {
    showUpdateAccountForm();
  });


  // Apresentar página de conta
  function showUpdateAccountForm() {

    // Obter e validar JWT guardado
    var jwt = getCookie('jwt');
    $.post("api/users/validate_token.php", JSON.stringify({ jwt: jwt })).done(function(result) {

      // Se for válido apresentar formulário
      var html = `
                                <h2>Atualizar Dados</h2>
                                <form id='update_account_form'>
                                    <div class="form-group">
                                        <label for="username">Nome</label>
                                        <input type="text" class="form-control" name="username" id="username" required value="` + result.data.username + `" />
                                    </div>

                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" name="email" id="email" required value="` + result.data.email + `" />
                                    </div>
                                    <button type='submit' class='btn btn-primary'>
                                        Guardar
                                    </button>
                                </form>
                            `;

      clearResponse();
      addContent(html);
    })

      // ao falhar indicar que é necessário efetuar login
      .fail(function(result) {
        showLoginPage();
        addResponse("<div class='alert alert-danger'>Faça login para aceder à Área Restrita.</div>");
      });
  }


  // Ao faz<er submit no formulário de atualização
  $(document).on('submit', '#update_account_form', function() {

    // Obter dados do formulário e adicionar à resposta
    var update_account_form = $(this);
    var update_account_form_obj = update_account_form.serializeObject()

    // Obter JWT e adicionar à resposta
    var jwt = getCookie('jwt');
    update_account_form_obj.jwt = jwt;

    // Converter para json
    var form_data = JSON.stringify(update_account_form_obj);

    // Enviar dados para a api
    $.ajax({
      url: "api/users/update.php",
      type: "POST",
      contentType: 'application/json',
      data: form_data,
      success: function(result) {

        // Apresentar sucesso e guardar novo token
        addResponse("<div class='alert alert-success'>Conta atualizada com sucesso.</div>");
        setCookie("jwt", result.jwt, 1);
      },

      // Apresentar erro
      error: function(xhr, resp, text) {
        addResponse("<div class='alert alert-success'>Erro " + xhr + " - " + resp + " - " + text + "</div>");

      }
    });

    return false;
  });

  /*****************************************************************************
   *  A U X I L I A R E S
   *****************************************************************************/
  // Limpar div de resposta
  function clearResponse() {
    $('#response').html('');
  }

  // Alterar resposta
  function addResponse(html) {
    $('#response').html(html);
  }

  // Alterar conteúdo
  function addContent(html) {
    $('#content').html(html);
  }


  // Definir o cookie
  function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
  }

  // Ler o cookie
  function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for (var i = 0; i < ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) === ' ') {
        c = c.substring(1);
      }

      if (c.indexOf(name) === 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  }


  // Converter elemento do form em array
  $.fn.serializeObject = function() {

    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
      if (o[this.name] !== undefined) {
        if (!o[this.name].push) {
          o[this.name] = [o[this.name]];
        }
        o[this.name].push(this.value || '');
      } else {
        o[this.name] = this.value || '';
      }
    });
    return o;
  };
});
