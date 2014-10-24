<?php

namespace Domain\Entity;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="caixacrescer.global.atividade")
 */
class Atividade extends DocumentoBase
{

    /**
     * @ODM\ReferenceOne(name="usuario", targetDocument="Usuario", cascade="all")
     */
    private $_usuario;

    /**
     * @ODM\String(name="tipo")
     */
    private $_tipo;

    /**
     * @ODM\String(name="descricao")
     */
    private $_descricao;

    /**
     * @ODM\String(name="conteudo")
     */
    private $_conteudo;

    /**
     * @ODM\ReferenceOne(name="documento", cascade="all")
     */
    private $_documento;

    /**
     * @ODM\String(name="token")
     */
    private $_token;

    /**
     * @ODM\Date(name="usado")
     */
    private $_usado;

    /**
     * @ODM\String(name="ip")
     */
    private $_ip;

    /**
     * @ODM\String(name="navegador")
     */
    private $_navegador;

    /**
     * @ODM\EmbedOne(name="posicao", targetDocument="Posicao")
     */
    private $_posicao;

    /**
     * @ODM\EmbedOne(name="dispositivo", targetDocument="DispositivoSituacao")
     */
    private $_dispositivo;

    /**
     * @ODM\ReferenceOne(name="atividade", targetDocument="Atividade", cascade="all")
     */
    private $_atividade;

    /**
     * Construtor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    function getUsuario()
    {
        return $this->_usuario;
    }

    function getTipo()
    {
        return $this->_tipo;
    }

    function getDescricao()
    {
        return $this->_descricao;
    }

    function getConteudo()
    {
        return $this->_conteudo;
    }

    function getDocumento()
    {
        return $this->_documento;
    }

    function getToken()
    {
        return $this->_token;
    }

    function getUsado()
    {
        return $this->_usado;
    }

    function getIp()
    {
        return $this->_ip;
    }

    function getNavegador()
    {
        return $this->_navegador;
    }

    function getPosicao()
    {
        return $this->_posicao;
    }

    function getDispositivo()
    {
        return $this->_dispositivo;
    }

    function getAtividade()
    {
        return $this->_atividade;
    }

    function setUsuario($usuario)
    {
        $this->_usuario = $usuario;
    }

    function setTipo($tipo)
    {
        $this->_tipo = $tipo;
    }

    function setDescricao($descricao)
    {
        $this->_descricao = $descricao;
    }

    function setConteudo($conteudo)
    {
        $this->_conteudo = $conteudo;
    }

    function setDocumento($documento)
    {
        $this->_documento = $documento;
    }

    function setToken($token)
    {
        $this->_token = $token;
    }

    function setUsado($usado)
    {
        $this->_usado = $usado;
    }

    function setIp($ip)
    {
        $this->_ip = $ip;
    }

    function setNavegador($navegador)
    {
        $this->_navegador = $navegador;
    }

    function setPosicao($posicao)
    {
        $this->_posicao = $posicao;
    }

    function setDispositivo($dispositivo)
    {
        $this->_dispositivo = $dispositivo;
    }

    function setAtividade($atividade)
    {
        $this->_atividade = $atividade;
    }

}
