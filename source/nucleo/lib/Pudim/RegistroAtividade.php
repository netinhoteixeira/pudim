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
 * Registra as atividades do usuário no sistema.
 *
 * @global array $config Configuração do sistema
 * @global DocumentManager $documentos Conexão aos Documentos (MongoDB)
 * @param string $pagina Nome da Página a ser rastreada
 * @param string $atividade Nome da Atividade (em constante. ex.:
 * NOME_DA_ATIVIDADE)
 * @param string $descricao Descrição da Atividade
 * @param object $documento Registro relacionado com a Atividade (ex.:
 * exclusão, alteração)
 * @return usuarioatividade Atividade do Usuário
 */
class RegistroAtividade
{

    private $_aplicativo;
    private $_atividade;
    private $_nome;

    function __construct($nome, $constante = null)
    {
        $this->_aplicativo = Aplicativo::getInstance();

        $this->_nome = $nome;
        $this->_atividade = new \Domain\Entity\Atividade();
        $this->_atividade->setTipo($constante);
        $this->_atividade->setUsuario($this->_aplicativo->getUsuarioSessao());
    }

    public static function obter($id)
    {
        $aplicativo = Aplicativo::getInstance();

        return $aplicativo->getDocumentos()->createQueryBuilder('Domain\Entity\Atividade')
                        ->field('_id')->equals($id)
                        ->getQuery()
                        ->getSingleResult();
    }

    public function gravar()
    {
        if (!is_null($this->_aplicativo->getAnaliseTrafego())) {
            $this->_aplicativo->getAnaliseTrafego()->doTrackPageView($this->_nome);
        }

        if (!is_null($this->_atividade->getTipo())) {

            if ($this->_aplicativo->getExists('acessoid')) {
                $this->_atividade->setAtividade(RegistroAtividade::obter($this->_aplicativo->get('acessoid')));
            }

            $this->_aplicativo->getDocumentos()->persist($this->_atividade);
            $this->_aplicativo->getDocumentos()->flush();

            return $this->_atividade;
        }

        return null;
    }

}
