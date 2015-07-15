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

namespace Pudim\Service;

use Pudim\Model\DataTransferObject\ConfiguracaoPropriedade;

class ConfiguracaoService
{

    public static function obterPropriedadeDaLinha($linha, $secao)
    {
        $propriedade = new ConfiguracaoPropriedade();
        
        $posicao = strpos($linha, '=');
        
        $propriedade->setSecao($secao);
        $propriedade->setNome(trim(substr($linha, 0, $posicao)));
        $propriedade->setValor(trim(substr($linha, $posicao + 1)));

        if (in_array(strtolower($propriedade->getValor()), ['true', 'false'])) {
            $propriedade->setValor((strtolower($propriedade->getValor()) === 'true'));
        } else if (is_numeric($propriedade->getValor())) {
            $propriedade->setValor($propriedade->getValor() + 0);
        }
        
        return $propriedade;
    }

}
