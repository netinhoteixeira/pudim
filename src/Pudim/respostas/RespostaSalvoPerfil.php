<?php

namespace Pudim\Respostas;

/**
 * Classe RespostaSalvoPerfil.
 */
class RespostaSalvoPerfil implements \JsonSerializable
{

    private $_salvo = true;
    private $_mensagem = null;
    private $_foto = null;
    private $_renovarAcesso = false;

    /**
     * Obtém se foi salvo ou não.
     * 
     * @return boolean
     */
    public function getSalvo()
    {
        return $this->_salvo;
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
     * Obtém a foto em Base64.
     * 
     * @return string
     */
    public function getFoto()
    {
        return $this->_foto;
    }

    /**
     * Obtém se é para renovar o acesso ou não.
     * 
     * @return boolean
     */
    public function getRenovarAcesso()
    {
        return $this->_renovarAcesso;
    }

    /**
     * Define se foi salvo ou não.
     * 
     * @param boolean $salvo Salvo
     */
    public function setSalvo($salvo)
    {
        $this->_salvo = $salvo;
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
     * Define a foto.
     * 
     * @param string $foto Foto
     */
    public function setFoto($foto)
    {
        $this->_foto = $foto;
    }

    /**
     * Define se é para renovar o acesso ou não.
     * 
     * @param boolean $renovarAcesso Renovar acesso
     */
    public function setRenovarAcesso($renovarAcesso)
    {
        $this->_renovarAcesso = $renovarAcesso;
    }

    /**
     * Chamado quando executado o json_encode().
     * 
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'salvo' => $this->_salvo,
            'mensagem' => $this->_mensagem,
            'foto' => $this->_foto,
            'renovarAcesso' => $this->_renovarAcesso
        );
    }

}
