<?php

namespace Servidor;

class Cliente {
	protected $conexao;
	protected $chat_instancia;
	protected $nome = '';
	protected $mensagem_anterior = '';
	protected $contagem_mensagem = 0;
	
	public function __construct(&$chat_instancia, &$conexao, $nome = '') {
        $this->conexao = &$conexao;
		$this->chat_instancia = &$chat_instancia;
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
	
	public function checarRepeticao($msg, $total = 5) {
		if ($this->mensagem_anterior === $mensagem) {
			$this->mensagem_contagem++;
			
			if ($this->mensagem_contagem > $total) {
				$this->chat_instancia->onClose($this->conexao);
				return true;
			}
		} else {
			$this->mensagem_contagem = 0;
			$this->mensagem_anterior = $mensagem;
		}
		
		return false;
	}
	
	public function enviarMensagem($de, $mensagem, $privada = false) {
		$this->conexao->send(json_encode(array('tipo' => 'mensagem', 'de' => $de, 'mensagem' => $mensagem, 'privada' => $privada)));
	}
	
	public function entrou($nome) {
		$this->conexao->send(json_encode(array('tipo' => 'entrou', 'nome' => $nome)));
	}
	
	public function saiu($nome) {
		$this->conexao->send(json_encode(array('tipo' => 'saiu', 'nome' => $nome)));
	}
	
	public function mudou($nome_velho, $nome_novo) {
		$this->conexao->send(json_encode(array('tipo' => 'mudou', 'nome_velho' => $nome_velho, 'nome_novo' => $nome_novo)));
	}
	
	public function listaNomes($nomes) {
		$this->conexao->send(json_encode(array('tipo' => 'lista', 'nomes' => $nomes, 'eu' => $this->nome)));
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
