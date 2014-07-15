<?php

namespace Pudim\Respostas;

/**
 * Classe RespostaEncontrado.
 */
class RespostaEncontrado implements \JsonSerializable
{

    private $_encontrou = true;
    private $_mensagem;

    public function getEncontrou()
    {
        return $this->_encontrou;
    }

    public function getMensagem()
    {
        return $this->_mensagem;
    }

    public function setEncontrou($encontrou)
    {
        $this->_encontrou = $encontrou;
    }

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
            'encontrou' => $this->_encontrou,
            'mensagem' => $this->_mensagem
        );
    }

}
