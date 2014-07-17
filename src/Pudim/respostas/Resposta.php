<?php

namespace Pudim\Respostas;

/**
 * Classe Resposta.
 */
class Resposta implements \JsonSerializable
{

    private $_mensagem = null;

    /**
     * Obtém a mensagem.
     * 
     * @return string
     */
    public function getMensagem()
    {
        return $this->_mensagem;
    }

    /**
     * Define a mensagem.
     * 
     * @param string $mensagem Mensagem
     */
    public function setMensagem($mensagem)
    {
        $this->_mensagem = $mensagem;
    }

    /**
     * Chamado quando executado o json_encode().
     * 
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'mensagem' => mensagem
        );
    }

}
