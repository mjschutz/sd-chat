<?php

namespace Servidor;

class Cliente {
	protected $conexao;
	protected $chat_instancia;
	
	public function __construct($chat_instancia, $conexao) {
        $this->conexao = $conexao;
    }
	
	public function __destruct()
	{
		$this->conexao->close();
	}
	
	public function ehEstaConexao($conexao)
	{
		return $this->conexao === $conexao;
	}
	
	public function enviarMensagem($mensagem)
	{
		$this->conexao->send($mensagem);
	}
}

?>