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

namespace Pudim\Respostas;

use Pudim\CorreiosServicos;
use Pudim\CorreiosPaises;
use Pudim\Excecoes\CorreiosEncomendaInvalidaExcecao;

/**
 * Classe RespostaCorreiosEncomenda.
 */
class RespostaCorreiosEncomenda implements \JsonSerializable
{

    private $_codigo;
    private $_servico;
    private $_origem;
    private $_historico = array();

    public function __construct($codigo)
    {
        $this->_codigo = $codigo;

        // valida o código passado
        $matches = null;
        $regex = '/(([A-Z]{2})[0-9]{9}([A-Z]{2}))/';
        if (!preg_match($regex, $codigo, $matches)) {
            throw new CorreiosEncomendaInvalidaExcecao('Código inválido!');
        }

        $servico = $matches[2];
        $origem = $matches[3];

        // valida o serviço, e a origem do código
        // O serviço é representado pelas duas primeiras letras do código.
        // A origem é reepresentada pelas duas últimas letras do código.
        if (!array_key_exists($servico, CorreiosServicos::$lista)) {
            throw new CorreiosEncomendaInvalidaExcecao('Código inválido! Serviço "' . $servico . '" inexistente.');
        } elseif (!array_key_exists($origem, CorreiosPaises::$lista)) {
            throw new CorreiosEncomendaInvalidaExcecao('Código inválido! Origem "' . $origem . '" inexistente.');
        }

        $this->_servico = $servico;
        $this->_origem = $origem;
    }

    public function getCodigo()
    {
        return $this->_codigo;
    }

    public function getServico()
    {
        return $this->_servico;
    }

    public function getOrigem()
    {
        return $this->_origem;
    }

    public function getHistorico()
    {
        return array_reverse($this->_historico);
    }

    public function setServico($servico)
    {
        $this->_servico = $servico;
    }

    public function setOrigem($origem)
    {
        $this->_origem = $origem;
    }

    public function adicionarHistorico($historico)
    {
        $this->_historico[] = $historico;
    }

    /**
     * Chamado quando executado o json_encode().
     * 
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'codigo' => $this->_codigo,
            'servico' => $this->_servico,
            'origem' => $this->_origem,
            'historico' => array_reverse($this->_historico)
        );
    }

}
