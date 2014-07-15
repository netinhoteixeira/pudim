<?php

namespace Pudim\Respostas;

/**
 * Classe RespostaAcesso.
 */
class RespostaAcesso implements JsonSerializable
{

    private $_acessou = true;
    private $_identificao;

    public function getAcessou()
    {
        return $this->_acessou;
    }

    public function getIdentificao()
    {
        return $this->_identificao;
    }

    public function setAcessou($acessou)
    {
        $this->_acessou = $acessou;
    }

    public function setIdentificao($identificao)
    {
        $this->_identificao = $identificao;
    }

    /**
     * Chamado quando executado o json_encode().
     * 
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'acessou' => $this->_acessou,
            'acessoid' => $this->_identificao
        );
    }

}
