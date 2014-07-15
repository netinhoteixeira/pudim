<?php

namespace Pudim\Respostas;

/**
 * Classe RespostaRemovido.
 */
class RespostaRemovido implements JsonSerializable
{

    private $_removido = true;
    private $_mensagem = null;

    /**
     * Obtém se foi removido ou não.
     * 
     * @return boolean
     */
    public function getRemovido()
    {
        return $this->_removido;
    }

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
     * Define se foi removido ou não.
     * 
     * @param boolean $removido Removido
     */
    public function setRemovido($removido)
    {
        $this->_removido = $removido;
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
            'removido' => $this->_removido,
            'mensagem' => $this->_mensagem
        );
    }

}
