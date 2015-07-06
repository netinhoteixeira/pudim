<?php

/**
 * Pudim - Framework para desenvolvimento rápido em PHP.
 * Copyright (C) 2014  Francisco Ernesto Teixeira <fco.ernesto@gmail.com>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Pudim;

use Pudim\Arquivo;
use Pudim\Excecoes\ArquivoNaoEncontradoExcecao;

/**
 * Classe ModeloSmarty.
 */
class ModeloSmarty
{

    private $_smarty;
    private $_nome;

    /**
     *
     * @param type $nome
     * @throws FileNotFoundException
     */
    function __construct($nome)
    {
        $this->_nome = $nome;

        $file = implode(DIRECTORY_SEPARATOR, array(__APPDIR__, 'app', 'views', $nome . '.tpl'));

        if (!file_exists($file)) {
            throw new ArquivoNaoEncontradoExcecao('Modelo ' . $nome . ' não encontrado.');
        }

        $this->_smarty = new \Smarty();

        $this->_smarty->setTemplateDir(implode(DIRECTORY_SEPARATOR, array(__APPDIR__, 'app', 'views')));
        Arquivo::criarDiretorio(implode(DIRECTORY_SEPARATOR, array(__APPDIR__, 'tmp', 'views', 'compiled')));
        $this->_smarty->setCompileDir(implode(DIRECTORY_SEPARATOR, array(__APPDIR__, 'tmp', 'views', 'compiled')));
        $this->_smarty->setConfigDir(implode(DIRECTORY_SEPARATOR, array(__APPDIR__, 'config')));
        Arquivo::criarDiretorio(implode(DIRECTORY_SEPARATOR, array(__APPDIR__, 'tmp', 'views', 'cache')));
        $this->_smarty->setCacheDir(implode(DIRECTORY_SEPARATOR, array(__APPDIR__, 'tmp', 'views', 'cache')));

        $this->_values = array();
    }

    /**
     * 
     * @return Smarty
     */
    function getSmarty()
    {
        return $this->_smarty;
    }

    /**
     * 
     * @return string
     */
    function getNome()
    {
        return $this->_nome;
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
     * Parse values.
     *
     * @return string
     */
    function processar()
    {
        foreach ($this->_values as $key => $value) {
            $this->_smarty->assign($key, $value);
        }

        $this->_smarty->display($this->_nome . '.tpl');
    }

}
