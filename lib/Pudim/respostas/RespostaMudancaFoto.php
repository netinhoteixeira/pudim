<?php

namespace Pudim\Respostas;

/**
 * Classe RespostaFotoMudanca.
 */
class RespostaMudancaFoto implements JsonSerializable
{

    private $_mudou = false;
    private $_arquivoTemporario = null;

    /**
     * Obtém se mudou ou não.
     * 
     * @return boolean
     */
    public function getMudou()
    {
        return $this->_mudou;
    }

    /**
     * Obtém o arquivo temporário.
     * 
     * @return string
     */
    public function getArquivoTemporario()
    {
        return $this->_arquivoTemporario;
    }

    /**
     * Define se mudou ou não.
     * 
     * @param boolean $mudou Mudou
     */
    public function setMudou($mudou)
    {
        $this->_mudou = $mudou;
    }

    /**
     * Define o arquivo temporário.
     * 
     * @param string $arquivoTemporario Arquivo Temporário
     */
    public function setArquivoTemporario($arquivoTemporario)
    {
        $this->_arquivoTemporario = $arquivoTemporario;
    }

    /**
     * Chamado quando executado o json_encode().
     * 
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'mudou' => $this->_mudou,
            'arquivoTemporario' => $this->_arquivoTemporario
        );
    }

}
