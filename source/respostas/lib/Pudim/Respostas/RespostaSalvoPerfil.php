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
 * Classe RespostaSalvoPerfil.
 */
class RespostaSalvoPerfil implements \JsonSerializable
{

    private $_salvo = true;
    private $_mensagem = null;
    private $_imagem = null;
    private $_renovarAcesso = false;

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
     * Obtém a mensagem.
     * 
     * @return string
     */
    public function getMensagem()
    {
        return $this->_mensagem;
    }

    /**
     * Obtém a imagem em Base64.
     * 
     * @return string
     */
    public function getImagem()
    {
        return $this->_imagem;
    }

    /**
     * Obtém se é para renovar o acesso ou não.
     * 
     * @return boolean
     */
    public function getRenovarAcesso()
    {
        return $this->_renovarAcesso;
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
     * Define a mensagem.
     * 
     * @param string $mensagem Mensagem
     */
    public function setMensagem($mensagem)
    {
        $this->_mensagem = $mensagem;
    }

    /**
     * Define a imagem.
     * 
     * @param string $imagem Imagem
     */
    public function setImagem($imagem)
    {
        $this->_imagem = $imagem;
    }

    /**
     * Define se é para renovar o acesso ou não.
     * 
     * @param boolean $renovarAcesso Renovar acesso
     */
    public function setRenovarAcesso($renovarAcesso)
    {
        $this->_renovarAcesso = $renovarAcesso;
    }

    /**
     * Chamado quando executado o json_encode().
     * 
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'salvo' => $this->_salvo,
            'mensagem' => $this->_mensagem,
            'imagem' => $this->_imagem,
            'renovarAcesso' => $this->_renovarAcesso
        ];
    }

}
