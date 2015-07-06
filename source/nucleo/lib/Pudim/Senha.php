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
 * Classe Senha.
 */
class Senha
{

    /**
     * Cria uma senha forte de tamanho N contendo no mínimo uma letra minúscula,
     * uma letra maiúscula, um número e um caractere especial. Os caracteres
     * restante da senha são escolhidas de uma de quatro tipos.
     *
     * Os caracteres disponíveis em casa tipo são amigáveis para o usuário - não
     * há caracteres ambíguos como i, l, 1, o, O, etc. Estes, em conjunto da
     * opção $adicionarHifens, são muito fáceis para os usuários digitarem ou
     * falarem suas senhas.
     * 
     * Grupos de caracteres disponíveis:
     * - l: Letras minúsculas
     * - u: Letras maiúsculas
     * - d: Números
     * - s: Caracteres especiais
     *
     * Nota: a opção $adicionarHifens irá aumentar o tamanho da senha por floor(sqrt(N))
     * caracteres.
     *
     * @param int $tamanho
     * @param boolean $adicionarHifens
     * @param string $grupoCaracteres
     * @return string Senha gerada
     */
    public static function gerar($tamanho = 9, $adicionarHifens = false, $grupoCaracteres = 'luds')
    {
        $senhaGerada = self::gerarSenha($tamanho, $grupoCaracteres);

        return (!$adicionarHifens) ? $senhaGerada :
                self::adicionarHifens($senhaGerada, $tamanho);
    }

    /**
     * Cria uma senha forte de tamanho N contendo no mínimo uma letra minúscula,
     * uma letra maiúscula, um número e um caractere especial. Os caracteres
     * restante da senha são escolhidas de uma de quatro tipos.
     *
     * Os caracteres disponíveis em casa tipo são amigáveis para o usuário - não
     * há caracteres ambíguos como i, l, 1, o, O, etc. Estes, em conjunto da
     * opção $adicionarHifens, são muito fáceis para os usuários digitarem ou
     * falarem suas senhas.
     * 
     * Grupos de caracteres disponíveis:
     * - l: Letras minúsculas
     * - u: Letras maiúsculas
     * - d: Números
     * - s: Caracteres especiais
     *
     * Nota: a opção $adicionarHifens irá aumentar o tamanho da senha por floor(sqrt(N))
     * caracteres.
     *
     * @param int $tamanho
     * @param boolean $adicionarHifens
     * @param string $grupoCaracteres
     * @return string Senha gerada
     */
    public static function gerarCriptografada($tamanho = 9, $adicionarHifens = false, $grupoCaracteres = 'luds')
    {
        return sha1(Senha::gerar($tamanho, $adicionarHifens, $grupoCaracteres));
    }

    /**
     * Detecta os grupos de caracteres disponíveis:
     * - l: Letras minúsculas
     * - u: Letras maiúsculas
     * - d: Números
     * - s: Caracteres especiais
     * 
     * @param string $grupoCaracteres Grupos de caracteres permitidos
     * @return array Grupos disponíveis
     */
    private static function detectarGruposCaracteres($grupoCaracteres)
    {
        $grupos = [];

        if (strpos($grupoCaracteres, 'l') !== false) {
            $grupos[] = 'abcdefghjkmnpqrstuvwxyz';
        }

        if (strpos($grupoCaracteres, 'u') !== false) {
            $grupos[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }

        if (strpos($grupoCaracteres, 'd') !== false) {
            $grupos[] = '23456789';
        }

        if (strpos($grupoCaracteres, 's') !== false) {
            $grupos[] = '!@#$%&*?';
        }

        return $grupos;
    }

    /**
     * Gera a senha.
     * 
     * @param int $tamanho Tamanho da Senha
     * @param string $grupoCaracteres Grupos de caracteres permitidos
     * @return string Senha gerada
     */
    private static function gerarSenha($tamanho, $grupoCaracteres)
    {
        $caracteres = self::detectarGruposCaracteres($grupoCaracteres);

        $todos = '';
        $senha = '';
        foreach ($caracteres as $caracter) {
            $senha .= $caracter[array_rand(str_split($caracter))];
            $todos .= $caracter;
        }

        $todosEmVetor = str_split($todos);
        for ($i = 0; $i < $tamanho - count($caracteres); $i++) {
            $senha .= $todosEmVetor[array_rand($todosEmVetor)];
        }

        return str_shuffle($senha);
    }

    /**
     * Adiciona hífens à senha.
     * 
     * @param string $senha
     * @param int $tamanho
     * @return string Senha com hífen
     */
    private static function adicionarHifens($senha, $tamanho)
    {
        $tamanhoComHifen = floor(sqrt($tamanho));
        $senhaComHifen = '';

        while (strlen($senha) > $tamanhoComHifen) {
            $senhaComHifen .= substr($senha, 0, $tamanhoComHifen) . '-';
            $senha = substr($senha, $tamanhoComHifen);
        }

        $senhaComHifen .= $senha;

        return $senhaComHifen;
    }

}
