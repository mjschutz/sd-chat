function Usuario(chat_instancia, nome_usuario) {
	var tag_usuario = $('<a href="javascript: void(0);">' + nome_usuario + '</a>');
	
	var focarChat = function() {
		var elem = document.getElementById('tela-chat');
		elem.scrollTop = elem.scrollHeight;
	}
	
	var iniciar = function() {
		$("#tela-usuarios").append(tag_usuario);
	}
	
	this.entrou = function(){
		$("#tela-chat").append($('<span><strong>"' + nome_usuario + '" entrou</strong></span>'));
		focarChat();
	}
	
	this.saiu = function() {
		chat_instancia.removerUsuario(nome_usuario);
		if (tag_usuario.hasClass('selecionado')) {
			$($('#tela-usuarios a')[0]).addClass("selecionado");
		}
		tag_usuario.remove();
		$("#tela-chat").append($('<span><strong>"' + nome_usuario + '" saiu</strong></span>'));
		focarChat();
	}
	
	this.mudou = function(nome_novo) {
		$("#tela-chat").append($('<span><strong>"' + nome_usuario + '" mudou nome para "' + nome_novo + '"</strong></span>'));
		nome_usuario = nome_novo;
		tag_usuario.text(nome_novo);
		focarChat();
	}
	
	this.mudar = function(nome_novo) {
		this.mudou(nome_novo);
		chat_instancia.enviar(JSON.stringify({
			tipo: 'info',
			nome: nome_novo
		}));
	}
	
	this.eu = function() {
		tag_usuario.addClass('eu');
	}
	
	this.mensagemPara = function(usuario, msg) {
		$("#tela-chat").append($('<span><strong>' + nome_usuario + ' => ' + usuario + ':</strong> ' + msg.replace('\n', '<br>') + '</span>'));
		focarChat();
		chat_instancia.enviar(JSON.stringify({
			tipo: 'mensagem',
			para: usuario,
			mensagem: msg
		}));
	}
	
	this.mensagemParaTodos = function(msg) {
		$("#tela-chat").append($('<span><strong>' + nome_usuario + ':</strong> ' + msg.replace('\n', '<br>') + '</span>'));
		focarChat();
		chat_instancia.enviar(JSON.stringify({
			tipo: 'mensagem',
			para: 'todos',
			mensagem: msg
		}));
	}
	
	this.mostrarMensagem = function(usuario, mensagem, privada) {
		if (!privada) {
			$("#tela-chat").append($('<span><strong>' + usuario + ':</strong> ' + mensagem.replace('\n', '<br>') + '</span>'));
		} else {		
			$("#tela-chat").append($('<span><strong>' + usuario + ' => ' + nome_usuario + ':</strong> ' + mensagem.replace('\n', '<br>') + '</span>'));
		}
		focarChat();
	}
	
	iniciar();
}

var mensagem_para = 'todos';

function Chat (endereco) {
	var usuarios = {};
	var thisChat = this;
	var eu = false;
	
	var ws = $.WebSocket(endereco);

	ws.onerror = function (e) {
		console.log ('Error with WebSocket uid: ' + e.target.uid);                                                                                              
	};

	ws.onopen = function(evt) {
		console.log("Conectado!");
		
		thisChat.enviar(JSON.stringify({
			tipo: 'lista'
		}))
	}
	
	ws.onclose = function(evt) {
		console.log("Disconectado");
	}
	
	ws.onmessage = function(evt) {
		console.log('Resposta: ' + evt.data);
		var dados = JSON.parse(evt.data);
		
		switch (dados.tipo) {
			case 'mensagem':
				eu.mostrarMensagem(dados.de, dados.mensagem, dados.privada);
			break;
			
			case 'entrou':
				thisChat.novoUsuario(dados.nome).entrou();
			break;
			
			case 'saiu':
				usuarios[dados.nome].saiu();
				thisChat.removerUsuario(dados.nome);
			break;
			
			case 'mudou':
				usuarios[dados.nome_velho].mudou(dados.nome_novo);
				usuarios[dados.nome_novo] = usuarios[dados.nome_velho];
			break;
			
			case 'lista':
				$("#tela-usuarios").empty();
				$("#tela-usuarios").append('<a href="javascript:void(0);" class="selecionado">Todos</a>');
				$.each(dados.nomes, function(indice, valor) {
					thisChat.novoUsuario(valor);
				});
				eu = thisChat.novoUsuario(dados.eu);
				eu.eu();
			break;
		}
	}
	
	ws.onerror = function(evt) {
		console.log('Erro: ' + evt.data);
	}
	
	this.enviar = function(mensagem) {
		console.log("Enviar: " + mensagem);
		ws.send(mensagem);
	}
		
	this.novoUsuario = function(nome)
	{
		usuarios[nome] = new Usuario(this, nome);
		
		var usuario_clique = function(e) {
			if ($(this).hasClass('eu'))
			{
				$('#tela-usuarios a').off('click');
				var $this = $(this);
				var nome_texto = $this.text();
				$this.text('');
				var $inputNome = $('<input type="text" value="' + nome_texto + '">');
				$inputNome.keyup(function(e){
					if (e.keyCode == 13)
					{
						$this.text($(this).val());
						eu.mudar($(this).val());
						$('#tela-usuarios a').on('click', usuario_clique);
						return false;
					} else if (e.keyCode == 27)
					{
						$this.text(nome_texto);
						$('#tela-usuarios a').on('click', usuario_clique);
						return false;
					}
					return true;
				});
				$inputNome.focusout(function() {
					$this.text(nome_texto);
					$('#tela-usuarios a').on('click', usuario_clique);
				});
				$this.append($inputNome);
				$inputNome.focus();
			
				return false;
			}
			
			if ($(this).hasClass('selecionado')) {
				return false;
			}
			
			$('#tela-usuarios a.selecionado').removeClass('selecionado');
			$(this).addClass('selecionado');
			mensagem_para = $(this).text();
			return false;
		};
		$('#tela-usuarios a').on('click', usuario_clique);
		
		return usuarios[nome];
	}
	
	this.removerUsuario = function(nome)
	{
		delete usuarios[nome];
	}
	
	this.eu = function() {
		return eu;
	}
}

var chat = new Chat('ws://webserver.local:8080');

$('textarea').on('keydown', function(event) {
    if (event.keyCode == 13 && !event.shiftKey) {
		if (mensagem_para.toLowerCase() == 'todos') {
			chat.eu().mensagemParaTodos($(this).val());
		} else {
			chat.eu().mensagemPara(mensagem_para, $(this).val());
		}
		$(this).val('');
		return false;
	}
	return true;
});
