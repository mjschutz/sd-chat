<?php

namespace Servidor;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Servidor\Cliente;

class Chat implements MessageComponentInterface {
    protected $clientes;

    public function __construct() {
        $this->clientes = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conexao) {
        $this->clientes->attach(new Cliente($this, $conexao, 'Usuário ' . $conexao->resourceId));
    }

    public function onMessage(ConnectionInterface $de, $msg) {
		$msg_json = json_decode($msg);
		$cliente = $this->clientePorConexao($de);

		switch (strtolower($msg_json->tipo)) {
			case 'mensagem':
				if (!isset($msg_json->para)) {
					$msg_json->para = 'todos';
				}
				
				$this->enviarMensagem($cliente, $msg_json->para, strip_tags($msg_json->mensagem));
			break;
			
			case 'info':				
				if (isset($msg_json->nome)) {
					$cliente->atrNome(strip_tags($msg_json->nome));
				}
			break;
			
			case 'lista':
				$this->listarClientes($cliente);
			break;
		}
    }

    public function onClose(ConnectionInterface $conexao) {
		foreach ($this->clientes as $cliente) {
            if ($cliente->ehEstaConexao($conexao)) {
				$this->clientes->detach($cliente);
			}
		}
    }

    public function onError(ConnectionInterface $conexao, \Exception $erro) {
        echo "Um erro ocorreu: {$erro->getMessage()}\n"; // Deixar esse debug em caso de dúvida
		$this->onClose($conexao);
    }
	
	private function clientePorConexao($conexao) {
		foreach ($this->clientes as $cliente) {
			if ($cliente->ehEstaConexao($conexao)) {
				return $cliente;
			}
		}
		
		return FALSE;
	}
	
	public function enviarMensagem($cliente_de, $para, $mensagem) {
		$para = strtolower($para);
		$todos = $para == 'todos';
		
                if ($cliente_de->checarRepeticao(sha1($mensagem))) {
                        $cliente_de->enviarMensagem('Chat', 'abuso no envio das mensagens', !$todos);
                        $cliente_de->saiu($cliente_de->atrNome());
                        return;
                }
		
		foreach ($this->clientes as $cliente) {
			if (($todos || $para === strtolower($cliente->atrNome())) && $cliente !== $cliente_de) {
				$cliente->enviarMensagem($cliente_de->atrNome(), strip_tags($mensagem), !$todos);
			}
		}
	}
	
	public function entrou($cliente_entrou) {	
		foreach ($this->clientes as $cliente) {
			if ($cliente !== $cliente_entrou) {
				$cliente->entrou($cliente_entrou->atrNome());
			}
		}
	}
	
	public function saiu($cliente_saiu) {
		foreach ($this->clientes as $cliente) {
			if ($cliente !== $cliente_saiu) {
				$cliente->saiu($cliente_saiu->atrNome());
			}
		}
	}
	
	public function mudou($cliente_mudou, $nome_novo) {
		foreach ($this->clientes as $cliente) {
			if ($cliente !== $cliente_mudou) {
				$cliente->mudou($cliente_mudou->atrNome(), $nome_novo);
			}
		}
	}
	
	public function listarClientes($cliente_para)
	{
		$nomes = array();
		foreach ($this->clientes as $cliente) {
			if ($cliente !== $cliente_para)
				$nomes[] = $cliente->atrNome();
		}
		
		$cliente_para->listaNomes($nomes);
	}
}
	
?>
