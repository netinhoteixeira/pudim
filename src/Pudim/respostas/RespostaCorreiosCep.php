<?php

namespace Pudim\Respostas;

if (!defined('RESPOSTA_CORREIOS_CEP_LOGRADOURO')) {
    define('RESPOSTA_CORREIOS_CEP_LOGRADOURO', 0);
}

if (!defined('RESPOSTA_CORREIOS_CEP_BAIRRO')) {
    define('RESPOSTA_CORREIOS_CEP_BAIRRO', 1);
}

if (!defined('RESPOSTA_CORREIOS_CEP_LOCALIDADE')) {
    define('RESPOSTA_CORREIOS_CEP_LOCALIDADE', 2);
}

if (!defined('RESPOSTA_CORREIOS_CEP_UF')) {
    define('RESPOSTA_CORREIOS_CEP_UF', 3);
}

if (!defined('RESPOSTA_CORREIOS_CEP')) {
    define('RESPOSTA_CORREIOS_CEP', 4);
}

/**
 * Classe RespostaCorreiosCep.
 */
class RespostaCorreiosCep implements \JsonSerializable
{

    private $_encontrou = false;
    private $_logradouro;
    private $_bairro;
    private $_localidade;
    private $_uf;
    private $_cep;

    public function getEncontrou()
    {
        return $this->_encontrou;
    }

    public function getLogradouro()
    {
        return $this->_logradouro;
    }

    public function getBairro()
    {
        return $this->_bairro;
    }

    public function getLocalidade()
    {
        return $this->_localidade;
    }

    public function getUf()
    {
        return $this->_uf;
    }

    public function getCep()
    {
        return $this->_cep;
    }

    public function setEncontrou($encontrou)
    {
        $this->_encontrou = $encontrou;
    }

    public function setLogradouro($logradouro)
    {
        $this->_logradouro = $logradouro;
    }

    public function setBairro($bairro)
    {
        $this->_bairro = $bairro;
    }

    public function setLocalidade($localidade)
    {
        $this->_localidade = $localidade;
    }

    public function setUf($uf)
    {
        $this->_uf = $uf;
    }

    public function setCep($cep)
    {
        $this->_cep = $cep;
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
            'logradouro' => $this->_logradouro,
            'bairro' => $this->_bairro,
            'localidade' => $this->_localidade,
            'uf' => $this->_uf,
            'cep' => $this->_cep
        );
    }

}
