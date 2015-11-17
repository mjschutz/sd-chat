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

        echo "Nova conexao! ({$conexao->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $de, $msg) {
        $numRecv = count($this->clientes) - 1;
        echo sprintf('Conexao %d enviando mensagem "%s" para %d outros cliente%s' . "\n"
            , $de->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

		$msg_json = json_decode($msg);
		$cliente = $this->clientePorConexao($de);

        foreach ($this->clientes as $cliente) {
			switch (strtolower($msg_json->tipo)) {
				case 'mensagem':
					if (!isset($msg_json->para)) {
						$msg_json->para = 'todos';
					}
					
					$this->enviarMensagem($cliente, $msg_json->para, $msg_json->mensagem);
				break;
				
				case 'info':				
					if (isset($msg_json->nome)) {
						$cliente->atrNome($msg_json->nome);
					}
				break;
            }
        }
    }

    public function onClose(ConnectionInterface $conexao) {
		foreach ($this->clientes as $cliente) {
            if ($cliente->ehEstaConexao($conexao)) {
				$this->clientes->detach($cliente);
			}
		}

        echo "Conexão {$conexao->resourceId} foi desconectada\n";
    }

    public function onError(ConnectionInterface $conexao, \Exception $erro) {
        echo "Um erro ocorreu:: {$erro->getMessage()}\n";
		$this->onClose($conexao);
    }
	
	private function clientePorConexao($conexao) {
		foreach ($this->clientes as $cliente) {
			if ($cliente->ehEstaConexao($de)) {
				return $cliente;
			}
		}
		
		return FALSE;
	}
	
	public function enviarMensagem($cliente_de, $para, $mensagem) {
		$para = strtolower($msg_json->para);
		$todos = $para == 'todos';
		
		foreach ($this->clientes as $cliente) {
			if (($todos || $para === $cliente->atrNome()) && $cliente !== $cliente_de) {
				$cliente->enviarMensagem($cliente_de->atrNome(), $mensagem);
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
}
	
?>