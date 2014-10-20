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

    private $_id;
    private $_nome;
    private $_constante;
    private $_usuario;
    private $_descricao;
    private $_documento;
    private $_token;
    private $_usado;

    function __construct($nome, $constante = null)
    {
        $aplicativo = Aplicativo::getInstance();

        $this->_nome = $nome;
        $this->_constante = $constante;
        $this->_usuario = $aplicativo->getUsuarioSessao();
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getNome()
    {
        return $this->_nome;
    }

    public function getConstante()
    {
        return $this->_constante;
    }

    public function getUsuario()
    {
        return $this->_usuario;
    }

    public function getDescricao()
    {
        return $this->_descricao;
    }

    public function getDocumento()
    {
        return $this->_documento;
    }

    public function getToken()
    {
        return $this->_token;
    }

    public function getUsado()
    {
        return $this->_usado;
    }

    public function setId($id)
    {
        $this->_id = $id;
    }

    public function setNome($nome)
    {
        $this->_nome = $nome;
    }

    public function setConstante($constante)
    {
        $this->_constante = $constante;
    }

    public function setUsuario($usuario)
    {
        $this->_usuario = $usuario;
    }

    public function setDescricao($descricao)
    {
        $this->_descricao = $descricao;
    }

    public function setDocumento($documento)
    {
        $this->_documento = $documento;
    }

    public function setToken($token)
    {
        $this->_token = $token;
    }

    public function setUsado($usado)
    {
        $this->_usado = $usado;
    }

    public static function obter($id)
    {
        $aplicativo = Aplicativo::getInstance();

        return $aplicativo->getDocumentos()->createQueryBuilder('Domain\Entity\UsuarioAtividade')
                        ->field('_id')->equals($id)
                        ->getQuery()
                        ->getSingleResult();
    }

    public function gravar()
    {
        $aplicativo = Aplicativo::getInstance();

        if (!is_null($aplicativo->getAnaliseTrafego())) {
            $aplicativo->getAnaliseTrafego()->doTrackPageView($this->_nome);
        }

        if ((!is_null($this->_constante)) && (!is_null($this->_usuario))) {
            $atividade = new \Domain\Entity\UsuarioAtividade();

            $atividade->setUsuario($this->_usuario);
            $atividade->setTipo($this->_constante);
            $this->definirDescricao($atividade);
            $this->definirDocumento($atividade);
            $this->definirRelacionada($aplicativo, $atividade);
            $this->definirToken($atividade);
            $this->definirUsado($atividade);
            $this->processarConstante($aplicativo, $atividade);

            $aplicativo->getDocumentos()->persist($atividade);
            $aplicativo->getDocumentos()->flush();

            return $atividade;
        }

        return null;
    }

    public static function gravarSimples($nome, $constante = null)
    {
        $registroAtividade = new RegistroAtividade($nome, $constante);
        $registroAtividade->gravar();
    }

    private function definirDescricao(&$atividade)
    {
        if (!is_null($this->_descricao)) {
            $atividade->setDescricao($this->_descricao);
        }
    }

    private function definirDocumento(&$atividade)
    {
        if (!is_null($this->_documento)) {
            $atividade->setDocumento($this->_documento);
        }
    }

    private function definirRelacionada($aplicativo, &$atividade)
    {
        if ($aplicativo->getExists('acessoid')) {
            $relacionada = RegistroAtividade::obter($aplicativo->get('acessoid'));
            if (!is_null($relacionada)) {
                $atividade->setRelacionada($relacionada);
            }
        }
    }

    private function definirToken(&$atividade)
    {
        if (!is_null($this->_token)) {
            $atividade->setToken($this->_token);
        }
    }

    private function definirUsado(&$atividade)
    {
        if (!is_null($this->_usado)) {
            $atividade->setUsado($this->_usado);
        }
    }

    private function processarConstante($aplicativo, &$atividade)
    {
        if ($this->_constante === 'ACESSAR') {
            $atividade->setIp(filter_input(INPUT_SERVER, 'REMOTE_ADDR'));
            $atividade->setNavegador(filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'));

            if ($aplicativo->postExists('posicao')) {
                $_posicao = $aplicativo->post('posicaos');

                $posicao = new \Domain\Entity\Posicao();
                $this->__definirPosicaoLatitude($posicao, $_posicao);
                $this->__definirPosicaoLongitude($posicao, $_posicao);
                $this->__definirPosicaoAltitude($posicao, $_posicao);
                $this->__definirPosicaoPrecisao($posicao, $_posicao);
                $this->__definirPosicaoVelocidade($posicao, $_posicao);
                $this->__definirPosicaoRumo($posicao, $_posicao);

                $atividade->setPosicao($posicao);
            }
        }
    }

    private function __definirPosicaoLatitude(&$posicao, $_posicao)
    {
        if (isset($_posicao['latitude'])) {
            $posicao->setLatitude($_posicao['latitude']);
        }
    }

    private function __definirPosicaoLongitude(&$posicao, $_posicao)
    {
        if (isset($_posicao['longitude'])) {
            $posicao->setLongitude($_posicao['longitude']);
        }
    }

    private function __definirPosicaoAltitude(&$posicao, $_posicao)
    {
        if (isset($_posicao['altitude'])) {
            $posicao->setAltitude($_posicao['altitude']);
        }
    }

    private function __definirPosicaoPrecisao(&$posicao, $_posicao)
    {
        if (isset($_posicao['precisao'])) {
            $posicao->setPrecisao($_posicao['precisao']);
        }
    }

    private function __definirPosicaoVelocidade(&$posicao, $_posicao)
    {
        if (isset($_posicao['velocidade'])) {
            $posicao->setVelocidade($_posicao['velocidade']);
        }
    }

    private function __definirPosicaoRumo(&$posicao, $_posicao)
    {
        if (isset($_posicao['rumo'])) {
            $posicao->setRumo($_posicao['rumo']);
        }
    }

}
