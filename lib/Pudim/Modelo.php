<?php

namespace Pudim;

use \Pudim\Excecoes\ArquivoNaoEncontradoExcecao;

/**
 * Classe Modelo.
 */
class Modelo
{

    private $_title;
    private $_content;
    private $_style;
    private $_values;

    /**
     *
     * @param type $nome
     * @param type $estilo
     * @throws FileNotFoundException
     */
    function __construct($nome, $estilo = null)
    {
        $file = __DIR__ . '/../../../../../templates/' . $nome . '.html';

        if (!file_exists($file)) {
            throw new ArquivoNaoEncontradoExcecao('Modelo ' . $nome . ' não encontrado.');
        }

        $this->_content = file_get_contents($file);

        $match = null;
        preg_match("/<title>(.*?)<\\/title>/si", $this->_content, $match);
        $titulo = explode('-', $match[1]);
        $this->_title = trim($titulo[0]);

        $this->_values = array();

        if (!is_null($estilo)) {
            $styleFile = __DIR__ . '/../../../../../templates/' . $estilo . '.css';

            if (!file_exists($styleFile)) {
                throw new ArquivoNaoEncontradoExcecao('Modelo ' . $styleFile . ' não encontrado.');
            }

            $this->_style = file_get_contents($styleFile);
        }
    }

    /**
     *
     * @param type $values
     */
    function setValores($values)
    {
        $this->_values = $values;
    }

    /**
     *
     * @return array
     */
    function getValores()
    {
        return $this->_values;
    }

    /**
     * Define a value for the key.
     *
     * @param string $key
     * @param type $value
     */
    function setValor($key, $value)
    {
        $this->_values[$key] = $value;
    }

    /**
     * Get the value for key.
     *
     * @param string $key
     * @return string|null
     */
    function getValor($key)
    {
        if (isset($this->_values[$key])) {
            return $this->_values[$key];
        } else {
            return null;
        }
    }

    /**
     * Get title.
     *
     * @return string
     */
    function getTitulo()
    {
        return $this->_title;
    }

    /**
     * Parse values.
     *
     * @return string
     */
    function processar()
    {
        $parsedData = $this->_content;

        foreach ($this->_values as $key => $value) {
            $parsedData = str_replace('{' . $key . '}', $value, $parsedData);
        }

        if (!is_null($this->_style)) {
            require_once(__DIR__ . '/auxiliar/CssToInlineStylesOverride.php');

            $cssParser = new \CssToInlineStylesOverride($parsedData, $this->_style);

            return $cssParser->convert();
        } else {
            return $parsedData;
        }
    }

}
