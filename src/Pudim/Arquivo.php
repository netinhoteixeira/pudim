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
 * Classe Arquivo.
 */
class Arquivo
{

    /**
     * Remove o arquivo fornecido.
     * 
     * @param string $arquivo Caminho do arquivo
     */
    public static function remover($arquivo)
    {
        try {
            if ((isset($arquivo)) && (!is_null($arquivo)) && (file_exists($arquivo))) {
                unlink($arquivo);
            }
        } catch (Exception $ex) {
            // ignora
        }
    }

    /**
     * Localiza e requisita todos os arquivos de um determinado diretório.
     *
     * @param string $path Caminho que será requisitado os arquivos
     */
    public static function requererDiretorio($path)
    {
        foreach (glob($path . '*.php') as $filename) {
            require_once $filename;
        }
    }

}
