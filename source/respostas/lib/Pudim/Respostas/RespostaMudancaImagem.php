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
 * Classe RespostaImagemMudanca.
 */
class RespostaMudancaImagem implements \JsonSerializable
{

    private $_mudou = false;
    private $_arquivoTemporario = null;

    /**
     * Obtém se mudou ou não.
     * 
     * @return boolean
     */
    public function getMudou()
    {
        return $this->_mudou;
    }

    /**
     * Obtém o arquivo temporário.
     * 
     * @return string
     */
    public function getArquivoTemporario()
    {
        return $this->_arquivoTemporario;
    }

    /**
     * Define se mudou ou não.
     * 
     * @param boolean $mudou Mudou
     */
    public function setMudou($mudou)
    {
        $this->_mudou = $mudou;
    }

    /**
     * Define o arquivo temporário.
     * 
     * @param string $arquivoTemporario Arquivo Temporário
     */
    public function setArquivoTemporario($arquivoTemporario)
    {
        $this->_arquivoTemporario = $arquivoTemporario;
    }

    /**
     * Chamado quando executado o json_encode().
     * 
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'mudou' => $this->_mudou,
            'arquivoTemporario' => $this->_arquivoTemporario
        ];
    }

}
