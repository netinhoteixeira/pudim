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

use Pudim\Correios;
use Pudim\CorreiosSituacoesEncomenda;

/**
 * Classe RespostaCorreiosEncomendaItem.
 */
class RespostaCorreiosEncomendaItem implements \JsonSerializable
{

    private $_data;
    private $_local;
    private $_situacao;
    private $_detalhe;
    private $_cor;

    public function getData()
    {
        return $this->_data;
    }

    public function getLocal()
    {
        return $this->_local;
    }

    public function getSituacao()
    {
        return $this->_situacao;
    }

    public function getDetalhe()
    {
        return $this->_detalhe;
    }

    public function getCor()
    {
        return $this->_cor;
    }

    public function setData($data)
    {
        $this->_data = $data;
    }

    public function setLocal($local)
    {
        $this->_local = $local;
    }

    public function setSituacao($situacao)
    {
        $this->_situacao = $situacao;

        $this->_cor = null;
        $situacao = strtoupper($situacao);
        foreach (CorreiosSituacoesEncomenda::$lista as $chave => $valor) {
            if (strpos($situacao, $chave) !== false) {
                $this->_cor = $valor;
                break;
            }
        }

        if (!isset($this->_cor)) {
            $this->_cor = CorreiosSituacoesEncomenda::$lista['DESCONHECIDO'];
        }
    }

    public function setDetalhe($detalhe)
    {
        $this->_detalhe = $detalhe;
    }

    /**
     * Chamado quando executado o json_encode().
     * 
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'data' => $this->_data,
            'local' => $this->_local,
            'situacao' => $this->_situacao,
            'detalhe' => $this->_detalhe,
            'cor' => $this->_cor
        ];
    }

}
