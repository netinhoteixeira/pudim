<?php

/**
 * Pudim - Framework for agile development in PHP.
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

namespace Pudim\jqGrid;

/**
 * Classe jqGrid.
 */
class jqGridRegistro
{

    private $_id;
    private $_celulas;

    /**
     * Cria um registro para o jqGrid.
     * 
     * @param String $id Identificação do Registro
     */
    function __construct($id)
    {
        $this->_id = $id;
        $this->_celulas = array();
    }

    /**
     * Adicionar uma célula com o valor fornecido ou o vetor com sua chave.
     * 
     * @throws IndexNotFoundException
     */
    function adicionarCelula() //$resultado, $indice, $indice1, ...
    {
        if (func_num_args() === 0) {
            throw new ArgumentsMissingException('Precisa ser fornecido o resultado ou o vetor e chave.');
        } elseif (func_num_args() === 1) {
            array_push($this->_celulas, func_get_arg(0));
        } else {
            $resultado = func_get_arg(0);
            if (is_array($resultado)) {
                $total = func_num_args();
                for ($i = 1; $i <= $total - 1; $i++) {
                    if (isset($resultado[func_get_arg($i)])) {
                        $resultado = $resultado[func_get_arg($i)];
                    } else {
                        throw new IndexNotFoundException('$resultado[\'' . func_get_arg($i) . '\'] é um índice inválido');
                    }
                }

                array_push($this->_celulas, $resultado);
            } else {
                throw new IndexNotFoundException('O primeiro parâmetro precisa ser o resultado em si ou um vetor.');
            }
        }
    }

    function obter()
    {
        return array(
            'id' => $this->_id,
            'cell' => $this->_celulas
        );
    }

}
