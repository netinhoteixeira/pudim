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

/**
 * Classe ConfiguracaoPropriedade.
 */
class ConfiguracaoPropriedade
{

    private $nome;
    private $valor;

    public function __construct($linha)
    {
        $posicao = strpos($linha, '=');
        $this->nome = trim(substr($linha, 0, $posicao));
        $this->valor = trim(substr($linha, $posicao + 1));

        if (in_array(strtolower($this->valor), ['true', 'false'])) {
            $this->valor = (strtolower($this->valor) === 'true');
        } else if (is_numeric($this->valor)) {
            $this->valor += 0;
        }
    }

    function getNome()
    {
        return $this->nome;
    }

    function getValor()
    {
        return $this->valor;
    }

}
