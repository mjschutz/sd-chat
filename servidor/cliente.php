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
	
	public function entrou($nome) {
		$this->conexao->send(json_encode(array('tipo' => 'entrou', 'nome' => $nome)));
	}
	
	public function saiu($nome) {
		$this->conexao->send(json_encode(array('tipo' => 'saiu', 'nome' => $nome)));
	}
	
	public function mudou($nome_velho, $nome_novo) {
		$this->conexao->send(json_encode(array('tipo' => 'mudou', 'nome-velho' => $nome_velho, 'nome-novo' => $nome_novo)));
	}
	
	public function atrNome($nome = '') {
		if (!empty($nome)){
			$this->chat_instancia->mudou($this, $nome);
			$this->nome = $nome;
		}

		return $this->nome;
	}
}

?>