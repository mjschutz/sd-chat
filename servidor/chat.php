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
        $this->clientes->attach(new Cliente($this, $conexao));

        echo "New connection! ({$conexao->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $de, $msg) {
        $numRecv = count($this->clientes) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $de->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($this->clientes as $cliente) {
            if (!$cliente->ehEstaConexao($de)) {
                $cliente->enviarMensagem($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conexao) {
		foreach ($this->clientes as $cliente) {
            if ($cliente->ehEstaConexao($conexao)) {
				$this->clientes->detach($cliente);
			}
		}

        echo "Connection {$conexao->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conexao, \Exception $erro) {
        echo "An error has occurred: {$erro->getMessage()}\n";
		$this->onClose($conexao);
    }
}
	
?>