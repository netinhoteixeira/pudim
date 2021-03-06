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

namespace Pudim\Model\DataTransferObject;

/**
 * Classe ConfiguracaoPropriedade.
 */
class ConfiguracaoPropriedade
{

    private $secao;
    private $nome;
    private $valor;

    function getSecao()
    {
        return $this->secao;
    }

    function getNome()
    {
        return $this->nome;
    }

    function getValor()
    {
        return $this->valor;
    }

    function setSecao($secao)
    {
        $this->secao = $secao;
    }

    function setNome($nome)
    {
        $this->nome = $nome;
    }

    function setValor($valor)
    {
        $this->valor = $valor;
    }

}
