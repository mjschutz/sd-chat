<?php

namespace Servidor;

class Cliente {
	protected $conexao;
	protected $chat_instancia;
	protected $nome = '';
	
	public function __construct($chat_instancia, $conexao, $nome = '') {
        $this->conexao = $conexao;
		$this->chat_instancia = $chat_instancia;
		$this->nome = $nome;
		$this->chat_instancia->entrou($this);
    }
	
	public function __destruct() {
		$this->chat_instancia->saiu($this);
		$this->conexao->close();
	}
	
	public function ehEstaConexao($conexao) {
		return $this->conexao === $conexao;
	}
	
	public function enviarMensagem($de, $mensagem) {
		$this->conexao->send(json_encode(array('tipo' => 'mensagem', 'de' => $de, 'mensagem' => $mensagem)));
	}
	
	public function enviarNome($nome) {
		$this->conexao->send(json_encode(array('tipo' => 'nome', 'nome' => $nome)));
	}
	
	public function removerNome($nome) {
		$this->conexao->send(json_encode(array('tipo' => 'rem-nome', 'nome' => $nome)));
	}
	
	public function atrNome($nome = '') {
		if (!empty($nome)){
			$this->chat_instancia->saiu($this); // mudar para renomear?
			$this->nome = $nome;
			$this->chat_instancia->entrou($this);
		}

		return $this->nome;
	}
}

?>