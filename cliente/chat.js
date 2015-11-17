function Usuario(instancia_chat, nome_usuario)
{
	var tag_usuario = $('<a href="javascript: void(0);">' + nome_usuario + '</a>');
	
	var focarChat = function()
	{
		var elem = document.getElementById('tela-chat');
		elem.scrollTop = elem.scrollHeight;
	}
	
	var iniciar = function()
	{
		$("#tela-usuarios").append(tag_usuario);
	}
	
	this.sair = function() {
		instancia_chat.removerUsuario(nome_usuario);
		tag_usuario.remove();
		$("#tela-chat").append($('<span><strong>"' + nome_usuario + '" saiu</strong></span>'));
	}
	
	this.mensagemPara = function(usuario, mensagem)
	{
		$("#tela-chat").append($('<span><strong>' + nome_usuario + ' => ' + usuario + ':</strong> ' + mensagem.replace('\n', '<br>') + '</span>'));
	}
	
	this.mensagemParaTodos = function(mensagem)
	{
		$("#tela-chat").append($('<span><strong>' + nome_usuario + ':</strong> ' + mensagem.replace('\n', '<br>') + '</span>'));
	}
	
	iniciar();
}

function Chat () {
	var usuarios = {};
	
	this.novoUsuario = function(nome)
	{
		return usuarios[nome] = new Usuario(this, nome);
	}
	
	this.removerUsuario = function(nome)
	{
		delete usuarios[nome];
	}
}

var chat = new Chat();
var usuario = chat.novoUsuario('Eu aqui');
usuario.mensagemPara('Usuário 1', 'E ae');
usuario.mensagemParaTodos('E ae galera');
usuario.sair();