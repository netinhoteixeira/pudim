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

/**
 * Classe RespostaSalvo.
 */
class RespostaSalvo implements \JsonSerializable
{

    private $_salvo = true;
    private $_id = null;
    private $_mensagem = null;

    /**
     * Obtém se foi salvo ou não.
     * 
     * @return boolean
     */
    public function getSalvo()
    {
        return $this->_salvo;
    }
    
    /**
     * Obtém a identificação (se houver).
     * 
     * @return Object
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Obtém a mensagem.
     * 
     * @return string
     */
    public function getMensagem()
    {
        return $this->_mensagem;
    }

    /**
     * Define se foi salvo ou não.
     * 
     * @param boolean $salvo Salvo
     */
    public function setSalvo($salvo)
    {
        $this->_salvo = $salvo;
    }
    
    /**
     * Define a identificação (se houver).
     * 
     * @param Object $id Identificação
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    /**
     * Define a mensagem.
     * 
     * @param string $mensagem Mensagem
     */
    public function setMensagem($mensagem)
    {
        $this->_mensagem = $mensagem;
    }

    /**
     * Chamado quando executado o json_encode().
     * 
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'salvo' => $this->_salvo,
            'id' => $this->_id,
            'mensagem' => $this->_mensagem
        );
    }

}
