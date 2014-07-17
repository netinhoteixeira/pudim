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

namespace Pudim;

/**
 * Classe Senha.
 */
class Senha
{

    /**
     * Cria uma senha forte de tamanho N contendo no mínimo uma letra minúscula,
     * uma letra maiúscula, um número e um caractere especial. Os caracteres
     * restante da senha são escolhidas de uma de quatro tipos.
     *
     * Os caracteres disponíveis em casa tipo são amigáveis para o usuário - não há
     * caracteres ambíguos como i, l, 1, o, O, etc. Estes, em conjunto da opção
     * $add_dashes, são muito fáceis para os usuários digitarem ou falarem suas
     * senhas.
     *
     * Nota: a opção $add_dashes irá aumentar o tamanho da senha por floor(sqrt(N))
     * caracteres.
     *
     * @param type $tamanho
     * @param type $addDashes
     * @param type $availableSets
     * @return type
     */
    public static function gerar($tamanho = 9, $addDashes = false, $availableSets = 'luds')
    {
        $sets = array();
        if (strpos($availableSets, 'l') !== false) {
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        }

        if (strpos($availableSets, 'u') !== false) {
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }

        if (strpos($availableSets, 'd') !== false) {
            $sets[] = '23456789';
        }

        if (strpos($availableSets, 's') !== false) {
            $sets[] = '!@#$%&*?';
        }

        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }

        $all = str_split($all);
        for ($i = 0; $i < $tamanho - count($sets); $i++) {
            $password .= $all[array_rand($all)];
        }

        $password = str_shuffle($password);

        if (!$addDashes) {
            return $password;
        }

        $dashLength = floor(sqrt($tamanho));
        $dashString = '';
        while (strlen($password) > $dashLength) {
            $dashString .= substr($password, 0, $dashLength) . '-';
            $password = substr($password, $dashLength);
        }
        $dashString .= $password;
        return $dashString;
    }

    public static function gerarCriptografada($tamanho = 9, $addDashes = false, $availableSets = 'luds')
    {
        return sha1(Senha::gerar($tamanho, $addDashes, $availableSets));
    }

}
