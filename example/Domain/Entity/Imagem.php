<?php

namespace Domain\Entity;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document(collection="caixacrescer.global.imagem")
 */
class Imagem extends DocumentoBase
{

    /**
     * @ODM\File(name="file")
     */
    private $_file;

    /**
     * @ODM\String(name="filename")
     */
    private $_filename;

    /**
     * @ODM\String(name="mimeType")
     */
    private $_mimeType;

    /**
     * @ODM\Date(name="uploadDate")
     */
    private $_uploadDate;

    /**
     * @ODM\Int(name="length")
     */
    private $_length;

    /**
     * @ODM\Int(name="chunkSize")
     */
    private $_chunckSize;

    /**
     * @ODM\String(name="md5")
     */
    private $_md5;

    /**
     * Construtor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    function getFile()
    {
        return $this->_file;
    }

    function getFilename()
    {
        return $this->_filename;
    }

    function getMimeType()
    {
        return $this->_mimeType;
    }

    function getUploadDate()
    {
        return $this->_uploadDate;
    }

    function getLength()
    {
        return $this->_length;
    }

    function getChunckSize()
    {
        return $this->_chunckSize;
    }

    function getMd5()
    {
        return $this->_md5;
    }

    function setFile($file)
    {
        $this->_file = $file;
    }

    function setFilename($filename)
    {
        $this->_filename = $filename;
    }

    function setMimeType($mimeType)
    {
        $this->_mimeType = $mimeType;
    }

    function setUploadDate($uploadDate)
    {
        $this->_uploadDate = $uploadDate;
    }

    function setLength($length)
    {
        $this->_length = $length;
    }

    function setChunckSize($chunckSize)
    {
        $this->_chunckSize = $chunckSize;
    }

    function setMd5($md5)
    {
        $this->_md5 = $md5;
    }

}
