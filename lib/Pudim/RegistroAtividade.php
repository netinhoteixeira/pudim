<?php

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

        return $aplicativo->getDocumentos()->createQueryBuilder('usuarioatividade')
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

        if (!is_null($this->_constante)) {

            $usuarioAtividade = new \usuarioatividade();

            if (!is_null($this->_usuario)) {
                $usuarioAtividade->setUsuario($this->_usuario);

                $usuarioAtividade->setTipo($this->_constante);

                if (!is_null($this->_descricao)) {
                    $usuarioAtividade->setDescricao($this->_descricao);
                }

                if (!is_null($this->_documento)) {
                    $usuarioAtividade->setDocumento($this->_documento);
                }

                if ($aplicativo->getExists('acessoid')) {
                    $atividadeRelacionada = RegistroAtividade::obter($aplicativo->get('acessoid'));
                    if (!is_null($atividadeRelacionada)) {
                        $usuarioAtividade->setAtividadeRelacionada($atividadeRelacionada);
                    }
                }

                if (!is_null($this->_documento)) {
                    $usuarioAtividade->setDocumento($this->_documento);
                }

                if (!is_null($this->_token)) {
                    $usuarioAtividade->setToken($this->_token);
                }

                if (!is_null($this->_usado)) {
                    $usuarioAtividade->setUsado($this->_usado);
                }

                switch ($this->_constante) {

                    case 'ACESSAR':
                        $usuarioAtividade->setIp($_SERVER['REMOTE_ADDR']);
                        $usuarioAtividade->setNavegador($_SERVER['HTTP_USER_AGENT']);

                        if ($aplicativo->postExists('position')) {
                            $posicao = $aplicativo->post('position');

                            $coordenadas = new coordenadas();
                            $coordenadas->setX($posicao['longitude']);
                            $coordenadas->setY($posicao['latitude']);
                            $coordenadas->setAccuracy($posicao['accuracy']);

                            $usuarioAtividade->setCoordenadas($coordenadas);
                        }
                        break;
                }

                $aplicativo->getDocumentos()->persist($usuarioAtividade);
                $aplicativo->getDocumentos()->flush();

                return $usuarioAtividade;
            }
        }

        return null;
    }

    public static function gravarSimples($nome, $constante = null)
    {
        $registroAtividade = new RegistroAtividade($nome, $constante);
        $registroAtividade->gravar();
    }

}
