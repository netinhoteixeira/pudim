<?php

namespace Domain\Entity;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\MappedSuperclass
 */
abstract class DocumentoBase
{

    /**
     * @ODM\Id(name="id")
     */
    private $_id;

    /**
     * @ODM\Date(name="cadastro")
     */
    private $_cadastro;

    /**
     * @ODM\Date(name="removido")
     */
    private $_removido;

    /**
     * @ODM\Version
     */
    private $_edicao;

    /**
     * Construtor.
     */
    public function __construct()
    {
        $this->_cadastro = new \DateTime('now');
    }

    function getId()
    {
        return $this->_id;
    }

    function getCadastro()
    {
        return $this->_cadastro;
    }

    function getRemovido()
    {
        return $this->_removido;
    }

    function getEdicao()
    {
        return $this->_edicao;
    }

    function setId($id)
    {
        $this->_id = $id;
    }

    function setCadastro($cadastro)
    {
        $this->_cadastro = $cadastro;
    }

    function setRemovido($removido)
    {
        $this->_removido = $removido;
    }

    function setEdicao($edicao)
    {
        $this->_edicao = $edicao;
    }

}
