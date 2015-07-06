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
 * Classe RespostaRemovido.
 */
class RespostaRemovido implements \JsonSerializable
{

    private $_removido = true;
    private $_mensagem = null;

    /**
     * Obtém se foi removido ou não.
     * 
     * @return boolean
     */
    public function getRemovido()
    {
        return $this->_removido;
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
     * Define se foi removido ou não.
     * 
     * @param boolean $removido Removido
     */
    public function setRemovido($removido)
    {
        $this->_removido = $removido;
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
        return [
            'removido' => $this->_removido,
            'mensagem' => $this->_mensagem
        ];
    }

}
