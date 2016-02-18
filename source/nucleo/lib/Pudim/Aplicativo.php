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

use Doctrine\Common\ClassLoader;
use Pudim\Excecoes\FuncaoNaoEncontradaExcecao;
use Pudim\Arquivo;

/**
 * Classe Aplicativo.
 */
class Aplicativo {

    private $_servidor;
    private $_configuracao;
    private $_conexao;
    private $_slimApp;
    private $_nome;
    private $_icone;
    private $_versao;
    private $_enderecoBase;
    private $_email;
    private $_analiseTrafego;

    /**
     * Construtor.
     */
    public function __construct($appdir = null) {
        if (!defined('__APPDIR__')) {
            define('__APPDIR__', is_null($appdir) ? implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', '..', '..', '..', '..', '..']) : $appdir);

            $this->obterServidor();
            $this->_configuracao = new Configuracao(implode(DIRECTORY_SEPARATOR, [__APPDIR__, 'config', 'configuracao.ini']));

            define('PROJECT_STAGE', $this->_configuracao->get($this->_servidor . '.producao'));

            $this->inicializarVariaveis();
            $this->corrigirRequisicaoVariaveisPostagem();
            $this->_analiseTrafego = $this->iniciarAnaliseTrafego();
            $this->verificarPrimeiroAcesso();
            $this->configurarSessaoNoRedis();
            $this->iniciarSessao();
            $this->definirRegiaoFusoHorario();
            $this->definirControleDeAcessoDaOrigemDaRequisicao();
            $this->carregarControladores();
            $this->carregarUtilitarios();
        }
    }

    /**
     * Obtém a instância do Aplicativo.
     * 
     * @param String $appdir
     * @global Aplicativo $aplicativo
     * @return \Pudim\Aplicativo
     */
    public static function getInstance($appdir = null) {
        global $aplicativo;

        if (is_null($aplicativo)) {
            $aplicativo = new Aplicativo($appdir);
        }

        return $aplicativo;
    }

    private function inicializarVariaveis() {
        $this->criarDiretorioTemporario();
        $this->criarDiretorioLog();
        $this->iniciarSlimApp();
        $this->_conexao = $this->estabelecerConexao();

        $this->_nome = $this->_configuracao->get('aplicativo.nome');
        $this->_versao = $this->_configuracao->get('aplicativo.versao');
        $this->_icone = $this->_configuracao->get('aplicativo.icone');
        if (isset($_SERVER['SERVER_NAME'])) {
            $this->_enderecoBase = sprintf('%s://%s:%s%s', isset($_SERVER['HTTPS']) &&
                    $_SERVER['HTTPS'] != 'off' ? 'https' : 'http', $_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'], $this->_configuracao->get($this->_servidor . '.contexto'));
        }
        $this->_email = $this->_configuracao->get('email.conta');

        // habilita o serviço dos Correios, caso esteja habilitado
        if ($this->_configuracao->get('correios.cep')) {
            $this->definirRotaObtencao('/servico/correios/cep/:cep', '\Pudim\\CorreiosControlador:consultarCep');
        }
    }

    private function obterServidor() {
        if (is_null($this->_servidor)) {
            $servidor = filter_input(INPUT_SERVER, 'SERVER_NAME');
            if (($servidor === '127.0.0.1') || ($servidor === '0.0.0.0')) {
                $servidor = 'localhost';
            } else {
                $servidor = str_replace('.', '_', $servidor);
            }

            $this->_servidor = 'servidor_' . $servidor;
        }

        return $this->_servidor;
    }

    /**
     * Retorna a versão do aplicativo.
     * 
     * @return string
     */
    public function getVersao() {
        return $this->_versao;
    }

    /**
     * Retorna a configuração do aplicativo.
     * 
     * @return \Pudim\Configuracao
     */
    public function getConfiguracao() {
        return $this->_configuracao;
    }

    /**
     * Retorna o acesso à conexão.
     * 
     * @return \Doctrine\ORM\EntityManager
     */
    public function getConexao() {
        return $this->_conexao;
    }

    /**
     * Retorna o acesso aos documentos (alias).
     * 
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     * @deprecated
     */
    public function getDocumentos() {
        return $this->_conexao;
    }

    public function getSlimApp() {
        return $this->_slimApp;
    }

    public function getLog() {
        if (!is_null($this->_slimApp)) {
            return $this->_slimApp->getLog();
        } else {
            return null;
        }
    }

    /**
     * Retorna o nome do aplicativo.
     * 
     * @return string
     */
    public function getNome() {
        return $this->_nome;
    }

    /**
     * Retorna o ícone do aplicativo.
     * 
     * @return string
     */
    public function getIcone() {
        return $this->_icone;
    }

    /**
     * Retorna o endereço base.
     * 
     * @return string
     */
    public function getEnderecoBase() {
        return $this->_enderecoBase;
    }

    /**
     * Retorna o e-mail.
     * 
     * @return string
     */
    public function getEmail() {
        return $this->_email;
    }

    /**
     * Adiciona a segurança nos subdiretórios. Use asterisco (*) para todos.
     *
     * @param string $nome Nome da rota a ser protegida. Ex.: /acessorestrito
     */
    public function protegerRota($nome) {
        $arquivoSeguranca = __APPDIR__ . DIRECTORY_SEPARATOR . 'HttpBasicAuthRouteDatabaseCustom.inc.php';
        if (file_exists($arquivoSeguranca)) {
            require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', '..', '..', 'library', 'slim', 'HttpBasicAuthDatabase.php']);
            require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', '..', '..', 'library', 'slim', 'HttpBasicAuthRouteDatabase.php']);
            require_once $arquivoSeguranca;

            $this->_slimApp->add(new \HttpBasicAuthRouteDatabaseCustom($nome));
        }
    }

    /**
     * Define as rotas utilizadas em um cadastro.
     *
     * @param type $nome Nome da rota. Ex.: pessoa
     */
    function definirRotasParaCadastro($nome) {
        $nomeClasse = ucfirst($nome);

        $this->definirRotaObtencao('/cadastro/' . $nome . '/', '\Controllers\\' . $nomeClasse . ':listar');
        $this->definirRotaObtencao('/cadastro/' . $nome . '/:id', '\Controllers\\' . $nomeClasse . ':obter');
        $this->definirRotaPostagem('/cadastro/' . $nome . '/', '\Controllers\\' . $nomeClasse . ':salvar');
        $this->definirRotaPostagem('/cadastro/' . $nome . '/:id', '\Controllers\\' . $nomeClasse . ':salvar');
        $this->definirRotaSubstituicao('/cadastro/' . $nome . '/:id', '\Controllers\\' . $nomeClasse . ':salvar');
        $this->definirRotaRemocao('/cadastro/' . $nome . '/:id', '\Controllers\\' . $nomeClasse . ':remover');
    }

    /**
     * Define a rota para a chamada GET.
     * 
     * @param string $caminho Caminho da rota
     * @param function $funcao Função a ser chamada
     * @throws FuncaoNaoEncontradaExcecao
     */
    function definirRotaObtencao($caminho, $funcao) {
        $this->_slimApp->get($caminho, $funcao);
    }

    /**
     * Define a rota para a chamada POST.
     * 
     * @param string $caminho Caminho da rota
     * @param function $funcao Função a ser chamada
     * @throws FuncaoNaoEncontradaExcecao
     */
    function definirRotaPostagem($caminho, $funcao) {
        $this->_slimApp->post($caminho, $funcao);
    }

    /**
     * Define a rota para a chamada PUT.
     * 
     * @param string $caminho Caminho da rota
     * @param function $funcao Função a ser chamada
     * @throws FuncaoNaoEncontradaExcecao
     */
    function definirRotaSubstituicao($caminho, $funcao) {
        $this->_slimApp->put($caminho, $funcao);
    }

    /**
     * Define a rota para a chamada DELETE.
     * 
     * @param string $caminho Caminho da rota
     * @param function $funcao Função a ser chamada
     * @throws FuncaoNaoEncontradaExcecao
     */
    function definirRotaRemocao($caminho, $funcao) {
        $this->_slimApp->delete($caminho, $funcao);
    }

    /**
     * Chama a função fornecida caso não encontre a rota (Erro 404).
     * 
     * @param function $funcao Função a ser chamada
     * @throws FuncaoNaoEncontradaExcecao
     */
    function seRotaNaoForEncontrada($funcao) {
        $this->_slimApp->notFound($funcao);
    }

    /**
     *
     * @return type
     */
    private function obterRequisicao() {
        return $this->_slimApp->request();
    }

    /**
     *
     * @param type $variavel
     * @return type
     */
    function requisicaoVariavelObter($variavel) {
        $requisicao = $this->obterRequisicao();
        return $requisicao->get($variavel);
    }

    function existeRequisicaoVariavelObter($variavel) {
        return !is_null($this->requisicaoVariavelObter($variavel));
    }

    function existeRequisicaoVariavelPostagem($variavel) {
        return isset($_POST[$variavel]);
    }

    function existeRequisicaoVariavelPostagemNaoVazia($variavel) {
        return ((isset($_POST[$variavel])) && (!is_null($_POST[$variavel])) && (!empty($_POST[$variavel])));
    }

    function requisicaoVariavelPostagem($variavel, $podeSerVazia = true) {
        if (($this->existeRequisicaoVariavelPostagemNaoVazia($variavel)) && (!$podeSerVazia)) {
            return $_POST[$variavel];
        } elseif ($this->existeRequisicaoVariavelPostagem($variavel)) {
            return $_POST[$variavel];
        } else {
            return null;
        }
    }

    function get($variavel) {
        return $this->requisicaoVariavelObter($variavel);
    }

    function getExists($variavel) {
        return $this->existeRequisicaoVariavelObter($variavel);
    }

    function post($variavel, $podeSerVazia = true) {
        return $this->requisicaoVariavelPostagem($variavel, $podeSerVazia);
    }

    function postExists($variavel, $podeSerVazia = true) {
        if ($podeSerVazia) {
            return $this->existeRequisicaoVariavelPostagem($variavel);
        } else {
            return $this->existeRequisicaoVariavelPostagemNaoVazia($variavel);
        }
    }

    public static function existeVariavelSessao($variavel) {
        return isset($_SESSION[$variavel]);
    }

    public static function obterVariavelSessao($variavel) {
        return $_SESSION[$variavel];
    }

    public static function definirVariavelSessao($variavel, $valor) {
        $_SESSION[$variavel] = $valor;
    }

    public static function removerVariavelSessao($variavel) {
        if (self::existeVariavelSessao($variavel)) {
            unset($_SESSION[$variavel]);
        }
    }

    /**
     * Obtém o usuário da sessão.
     */
    function getUsuarioSessao() {
        if (self::existeVariavelSessao('userid')) {
            return $this->_conexao->createQueryBuilder('Domain\Entity\Usuario')
                            ->field('_id')
                            ->equals(self::obterVariavelSessao('userid'))
                            ->getQuery()
                            ->getSingleResult();
        } else {
            return null;
        }
    }

    /**
     * JSONfica e exibe a codificação do Objeto.
     * 
     * @param object $jsonfy Objeto a ser codificado em JSON
     * @param integer $statusCode Código da situação da resposta
     */
    function saida($jsonfy, $statusCode = 200) {
        $this->_slimApp->response->setStatus($statusCode);
        $this->_slimApp->response->headers->set('Content-Type', 'application/json; charset=utf-8');
        $this->_slimApp->response->setBody(json_encode($jsonfy));
    }

    private function iniciarSlimApp() {
        $config = [];

        $config['log.enabled'] = true;

        if (class_exists('\Amenadiel\SlimPHPConsole\PHPConsoleWriter')) {
            $config['log.level'] = \Slim\Log::DEBUG;
            $config['log.writer'] = new \Amenadiel\SlimPHPConsole\PHPConsoleWriter(true);
        } else {
            $logfile = $this->_configuracao->get('aplicativo.log');
            if (!empty($logfile)) {
                $logfile .= '-slim.log';
            } else {
                $logfile = 'slim.log';
            }
            $log = new \Slim\LogWriter(fopen(LOGDIR . DIRECTORY_SEPARATOR . $logfile, 'a'));
            $config['log.writer'] = $log;

            $config['debug'] = ($this->_configuracao->get('aplicativo.debug') === TRUE);
            if ($config['debug']) {
                $config['log.level'] = \Slim\Log::DEBUG;
            } else {
                $config['log.level'] = \Slim\Log::WARN;
            }
        }

        $this->_slimApp = new \Slim\Slim($config);

        $this->_slimApp->hook('slim.before.router', function() {
            $GLOBALS['usuarioSessao'] = Aplicativo::getUsuarioSessao();
        });
    }

    /**
     * Envia um e-mail do sistema para o endereço fornecido.
     * 
     * @param string $para Endereço de e-mail para qual deve ser enviado
     * @param string $assunto Assunto
     * @param string $mensagem Mensagem
     * @return boolean Se foi enviado ou não
     */
    public function enviarEmail($para, $assunto, $mensagem) {
        // para enviar email com HTML, o cabeçalho Content-type precisa ser
        // definido
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

        // cabeçalhos adicionais
        $headers .= 'To: ' . $para . "\r\n";
        $headers .= 'From: ' . $this->_nome . ' <' .
                $this->_email . '>' . "\r\n";

        // envia-o
        $mail = new \PHPMailer();
        $mail->Priority = 1; // Email priority (1 = High, 3 = Normal, 5 = Low)
        $mail->CharSet = 'utf-8';
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        if ($this->_configuracao->get('email.ssl')) {
            $mail->SMTPSecure = $this->_configuracao->get('email.ssl');
        }
        $mail->Host = $this->_configuracao->get('email.servidor');
        $mail->Port = $this->_configuracao->get('email.porta');
        $mail->Username = $this->_configuracao->get('email.conta');
        $mail->Password = $this->_configuracao->get('email.senha');
        $mail->SetFrom($this->_email, $this->_nome);
        $mail->Subject = $assunto;
        $mail->isHTML();
        $mail->Body = $mensagem;
        $mail->AddAddress($para);

        $emailLogoFilename = implode(DIRECTORY_SEPARATOR, [__DIR__, 'app',
            'views', 'email-logo.png']);
        if (file_exists($emailLogoFilename)) {
            $mail->AddEmbeddedImage($emailLogoFilename, 'logo');
        }

        return $mail->Send();
    }

    /**
     * Tenta estabelecer a conexão ao banco relacional ou de documentos.
     * 
     * @return \Doctrine\ORM\EntityManager | \Doctrine\ODM\MongoDB\DocumentManager
     */
    private function estabelecerConexao() {
        $tipo = explode(':', $this->_configuracao->get($this->_servidor . '.persistencia_uri'));
        $tipo = $tipo[0];

        if (!empty($tipo)) {
            if ($tipo === 'mongodb') {
                \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::registerAnnotationClasses();
            }

            $classLoader = new ClassLoader('Domain\Entity', implode(DIRECTORY_SEPARATOR, [__APPDIR__, 'app', 'models']));
            $classLoader->register();

            $doctrine_models_dir = implode(DIRECTORY_SEPARATOR, [__APPDIR__, 'app', 'models']);
            $doctrine_entities_dir = implode(DIRECTORY_SEPARATOR, [__APPDIR__, 'app', 'models', 'Domain', 'Entity']);
            $doctrine_proxies_dir = implode(DIRECTORY_SEPARATOR, [__APPDIR__, 'tmp', 'models', 'Domain', 'Entity', 'Proxies']);
            $doctrine_hydrators_dir = implode(DIRECTORY_SEPARATOR, [__APPDIR__, 'tmp', 'models', 'Domain', 'Entity', 'Hydrators']);

            // cria os diretórios dos proxys e hydrators, caso não haja (necessários
            // para o Doctrine)
            if (!PROJECT_STAGE) {
                Arquivo::criarDiretorio($doctrine_proxies_dir);
                Arquivo::criarDiretorio($doctrine_hydrators_dir);
            }

            // verifica se não é MongoDB
            if ($tipo !== 'mongodb') {

                // provê algumas informações iniciais do banco de dados
                switch ($tipo) {
                    case 'sqlite':
                        $parametrosConexao = [
                            'driver' => 'pdo_' . $tipo,
                            'path' => $this->_configuracao->get($this->_servidor . '.persistencia_banco')
                        ];
                        break;

                    case 'mysql':
                        $parametrosConexao = [
                            'driver' => 'pdo_' . $tipo,
                            'user' => $this->_configuracao->get($this->_servidor . '.persistencia_usuario'),
                            'password' => $this->_configuracao->get($this->_servidor . '.persistencia_senha'),
                            'host' => $this->_configuracao->get($this->_servidor . '.persistencia_servidor'),
                            'dbname' => $this->_configuracao->get($this->_servidor . '.persistencia_banco'),
                            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'' . $this->_configuracao->get($this->_servidor . '.persistencia_charset') . '\''
                        ];
                        break;

                    // em teste funciona para quase todos os tipos de PDO
                    default:
                        $parametrosConexao = [
                            'driver' => 'pdo_' . $tipo,
                            'user' => $this->_configuracao->get($this->_servidor . '.persistencia_usuario'),
                            'password' => $this->_configuracao->get($this->_servidor . '.persistencia_senha'),
                            'host' => $this->_configuracao->get($this->_servidor . '.persistencia_servidor'),
                            'dbname' => $this->_configuracao->get($this->_servidor . '.persistencia_banco')
                        ];
                        break;
                }

                // cria os mapeamentos das entidades do banco de dados, caso não existam
                if (count(glob($doctrine_entities_dir . '/*.php')) === 0) {
                    $configuracao = new \Doctrine\ORM\Configuration();
                    $configuracao->setMetadataDriverImpl($configuracao->newDefaultAnnotationDriver($doctrine_entities_dir, FALSE));
                    $configuracao->setProxyDir($doctrine_proxies_dir);
                    $configuracao->setProxyNamespace('Proxies');

                    $entityManager = \Doctrine\ORM\EntityManager::create($parametrosConexao, $configuracao);

                    // custom datatypes (not mapped for reverse engineering)
                    $entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('set', 'string');
                    $entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

                    // define namespace
                    $driver = new \Doctrine\ORM\Mapping\Driver\DatabaseDriver(
                            $entityManager->getConnection()->getSchemaManager()
                    );
                    $driver->setNamespace('Domain\\Entity\\');

                    // define driver with namespace
                    $entityManager->getConfiguration()->setMetadataDriverImpl($driver);

                    $disconnectedClassMetadataFactory = new \Doctrine\ORM\Tools\DisconnectedClassMetadataFactory();
                    $disconnectedClassMetadataFactory->setEntityManager($entityManager);

                    $entityGenerator = new \Doctrine\ORM\Tools\EntityGenerator();
                    $entityGenerator->setUpdateEntityIfExists(true);
                    $entityGenerator->setGenerateStubMethods(true);
                    $entityGenerator->setGenerateAnnotations(true);
                    $entityGenerator->generate($disconnectedClassMetadataFactory->getAllMetadata(), $doctrine_models_dir);
                }

                // carrega as entidades
                \Pudim\Arquivo::requererDiretorio($doctrine_entities_dir);

                $configuracao = \Doctrine\ORM\Tools\Setup::createConfiguration(!((boolean) PROJECT_STAGE));
                $driver = new \Doctrine\ORM\Mapping\Driver\AnnotationDriver(new \Doctrine\Common\Annotations\AnnotationReader(), $doctrine_entities_dir);

                // registering noop annotation autoloader - allow all annotations by default
                \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader('class_exists');
                $configuracao->setMetadataDriverImpl($driver);

                $configuracao->setAutoGenerateProxyClasses(!((boolean) PROJECT_STAGE));
                $configuracao->setProxyDir($doctrine_proxies_dir);
                $configuracao->setProxyNamespace('Proxies');

                if (PROJECT_STAGE) {
                    $cache = new \Doctrine\Common\Cache\ArrayCache();
                } else {
                    $cache = new \Doctrine\Common\Cache\ApcCache();
                }
                $configuracao->setMetadataCacheImpl($cache);
                $configuracao->setQueryCacheImpl($cache);

                // obtaining the entity manager (7)
                $eventManager = new \Doctrine\Common\EventManager();
                $conexao = \Doctrine\ORM\EntityManager::create($parametrosConexao, $configuracao, $eventManager);
            } else {

                $configuracao = new \Doctrine\ODM\MongoDB\Configuration();
                $metadata = AnnotationDriver::create($doctrine_entities_dir);
                $configuracao->setMetadataDriverImpl($metadata);

                $configuracao->setAutoGenerateProxyClasses(!((boolean) PROJECT_STAGE));
                $configuracao->setProxyDir($doctrine_proxies_dir);
                $configuracao->setProxyNamespace('Proxies');

                $configuracao->setAutoGenerateHydratorClasses(!((boolean) PROJECT_STAGE));
                $configuracao->setHydratorDir($doctrine_hydrators_dir);
                $configuracao->setHydratorNamespace('Hydrators');

                $configuracao->setDefaultDB($this->_configuracao->get($this->_servidor . '.persistencia_banco'));

                //$configuracao->setLoggerCallable(function (array $log) { print_r($log); });
                $cache_uri = $this->_configuracao->get($this->_servidor . '.cache_uri');
                if ((PROJECT_STAGE) && (class_exists('Redis')) && ($cache_uri)) {
                    // trata o $cache_uri pois somente precisamos do servidor e a porta
                    if (strpos($cache_uri, '//')) {
                        $cache_uri_parts = explode('//', $cache_uri);
                        if (strpos($cache_uri_parts[1], ':')) {
                            list($cache_server,
                                    $cache_port) = explode(':', $cache_uri_parts[1]);
                        } else {
                            $cache_server = $cache_uri_parts[1];
                            $cache_port = '6379';
                        }

                        unset($cache_uri_parts);
                    } else {
                        $cache_server = $cache_uri;
                        $cache_port = '6379';
                    }

                    $redis = new \Redis();
                    $redis->pconnect($cache_server, $cache_port);
                    $metadataCache = new \Doctrine\Common\Cache\RedisCache();
                    $metadataCache->setRedis($redis);
                    $configuracao->setMetadataCacheImpl($metadataCache);

                    unset($cache_server, $cache_port, $redis, $metadataCache);
                }

                $conexao = new \Doctrine\MongoDB\Connection($this->_configuracao->get($this->_servidor . '.persistencia_uri'));
                $conexao = \Doctrine\ODM\MongoDB\DocumentManager::create($conexao, $configuracao);

                // FIX: Muito importante pois força a criação dos índices no aplicativo
                $conexao->getSchemaManager()->ensureIndexes();
            }
        }

        return $conexao;
    }

    /**
     * Inicia a análise de tráfego.
     */
    private function iniciarAnaliseTrafego() {

        if ($this->_configuracao->get($this->_servidor . '.piwik_id')) {
            require_once(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', '..', '..', 'library', 'PiwikTracker.php']));

            $piwikTracker = new \PiwikTracker(
                    $this->_configuracao->get($this->_servidor . '.piwik_id')
                    , $this->_configuracao->get($this->_servidor . '.piwik_url'));

            if ($this->_configuracao->get($this->_servidor . '.piwik_token_auth')) {
                $piwikTracker->setTokenAuth(
                        $this->_configuracao->get(
                                $this->_servidor . '.piwik_token_auth'));
            }

            if (isset($_SERVER['HTTP_REFERER'])) {
                $piwikTracker->setReferrer($_SERVER['HTTP_REFERER']);
            }

            // detecta o endereço da internet do cliente
            if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $piwikTracker->setIp($_SERVER['HTTP_CLIENT_IP']);
            } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $piwikTracker->setIp($_SERVER['HTTP_X_FORWARDED_FOR']);
            } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
                $piwikTracker->setIp($_SERVER['HTTP_X_FORWARDED']);
            } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
                $piwikTracker->setIp($_SERVER['HTTP_FORWARDED_FOR']);
            } else if (isset($_SERVER['HTTP_FORWARDED'])) {
                $piwikTracker->setIp($_SERVER['HTTP_FORWARDED']);
            } else if (isset($_SERVER['REMOTE_ADDR'])) {
                $piwikTracker->setIp($_SERVER['REMOTE_ADDR']);
            } else {
                $piwikTracker->setIp('DESCONHECIDO');
            }

            $piwikTracker->setUserAgent($_SERVER['HTTP_USER_AGENT']);
            if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $idioma = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
                $piwikTracker->setBrowserLanguage($idioma[0]);
                unset($idioma);
            }

            if ($this->postExists('localTime')) {
                $piwikTracker->setLocalTime($this->post('localTime'));
            }

            if (($this->postExists('screenWidth')) &&
                    ($this->postExists('screenHeight'))) {
                $piwikTracker->setResolution(
                        $this->post('screenWidth'), $this->post('screenHeight'));
            }

            if ($this->postExists('position')) {
                $posicao = $this->post('position');

                $piwikTracker->setLongitude($posicao['longitude']);
                $piwikTracker->setLatitude($posicao['latitude']);

                unset($posicao);
            }
        } else {
            $piwikTracker = null;
        }

        return $piwikTracker;
    }

    /**
     * Obtém a análise de tráfego.
     * 
     * @return PiwikTracker
     */
    public function getAnaliseTrafego() {
        return $this->_analiseTrafego;
    }

    /**
     * Inicia o aplicativo.
     */
    public function iniciar() {
        $this->_slimApp->run();
    }

    /**
     * Cria o diretório temporário.
     */
    private function criarDiretorioTemporario() {
        define('TMPDIR', __APPDIR__ . DIRECTORY_SEPARATOR . 'tmp');
        Arquivo::criarDiretorio(TMPDIR);
        ini_set('sys_temp_dir', TMPDIR);
    }

    /**
     * Cria o diretório de log.
     */
    private function criarDiretorioLog() {
        $logfile = $this->_configuracao->get('aplicativo.log');
        if (!empty($logfile)) {
            $logfile .= '-php.log';
        } else {
            $logfile .= 'php.log';
        }

        define('LOGDIR', __APPDIR__ . DIRECTORY_SEPARATOR . 'log');
        Arquivo::criarDiretorio(LOGDIR);
        ini_set('log_errors', 1);
        ini_set('error_log', LOGDIR . DIRECTORY_SEPARATOR . $logfile);

        // cria o arquivo vazio
        touch(LOGDIR . DIRECTORY_SEPARATOR . $logfile);
    }

    /**
     * Verifica se é o primeiro acesso, atualiza as configurações.
     */
    private function verificarPrimeiroAcesso() {
        // caso seja o primeiro acesso ao sistema
        if ($this->_configuracao->get('acesso.primeiro')) {
            try {
                $this->criarPrimeiroUsuario();
                $this->saida('Usuário cadastrado. Recarregue a página.');
            } catch (\MongoCursorException $ex) {
                $this->saida('Usuário já cadastrado. Recarregue a página.');
            }

            // atualiza as informações do arquivo de configuração
            $this->_configuracao->set('acesso.primeiro', false);
            $this->_configuracao->persistir();

            exit();
        }
    }

    /**
     * Cria o primeiro usuário.
     */
    private function criarPrimeiroUsuario() {
        $usuario = new \Domain\Entity\Usuario();
        $usuario->setApelido('Netinho');
        $usuario->setEmail('fco.ernesto@gmail.com');
        # Senha: 123456
        # Criptografia: sha1
        $usuario->setSenha('7c4a8d09ca3762af61e59520943dc26494f8941b');
        $usuario->setNivelAcesso('ADMINISTRADOR');
        $usuario->setAtivo(true);

        $this->_conexao->persist($usuario);
        $this->_conexao->flush();
    }

    /**
     * Captura os argumentos JSON postados e os coloca em $_POST
     * O $http do AngularJS faz envio de postagens JSON (não a forma normal
     * "form encoded"). No Slim Framework é necessário a devida requisição dos
     * parâmetros POST do AngularJS.
     */
    private function corrigirRequisicaoVariaveisPostagem() {
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $contentType = $_SERVER['CONTENT_TYPE'];
        } elseif (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
            $contentType = $_SERVER['HTTP_CONTENT_TYPE'];
        } else {
            $contentType = ';';
        }

        if ((!empty($contentType)) && ($contentType !== ';')) {
            $contentTypeArguments = explode(';', $contentType);
            if ($contentTypeArguments[0] == 'application/json') {
                $_POST = json_decode(file_get_contents('php://input'), true);

                // varre os valores do tipo texto, fazendo um trim
                if (is_array($_POST)) {
                    foreach ($_POST as $key => $value) {
                        if (gettype($value) === 'string') {
                            $_POST[$key] = trim($value);
                        }
                    }
                }
            }
        }
    }

    /**
     * Configura a persistência da sessão no Redis, caso haja.
     */
    private function configurarSessaoNoRedis() {
        $cache_uri = $this->_configuracao->get($this->_servidor . '.cache_uri');
        if ((PROJECT_STAGE) && (class_exists('Redis')) && ($cache_uri)) {
            ini_set('session.save_handler', 'redis');
            ini_set('session.save_path', $cache_uri);
        }
    }

    /**
     * Inicia a sessão.
     */
    private function iniciarSessao() {
        if (!headers_sent()) {
            session_start();
        }
    }

    /**
     * Define a região do fuso horário.
     */
    private function definirRegiaoFusoHorario() {
        // FIX: Corrige o problema de não ser definido a região da data/hora
        $regiao = $this->_configuracao->get('aplicativo.regiao');
        if (empty($regiao) && empty(ini_get('date.timezone'))) {
            date_default_timezone_set('America/Sao_Paulo');
        } else {
            date_default_timezone_set($regiao);
        }
    }

    /**
     * Define o controle de acesso da origem da requisição.
     */
    private function definirControleDeAcessoDaOrigemDaRequisicao() {
        if (!headers_sent()) {
            header('Access-Control-Allow-Origin: *');
        }
    }

    /**
     * Carrega todos os controladores.
     */
    private function carregarControladores() {
        $controladores = implode(DIRECTORY_SEPARATOR, [__APPDIR__, 'app', 'controllers']);
        if (file_exists($controladores)) {
            Arquivo::requererDiretorio($controladores);
        }
    }

    /**
     * Carrega todos os utilitários.
     */
    private function carregarUtilitarios() {
        $utilitarios = implode(DIRECTORY_SEPARATOR, [__APPDIR__, 'app', 'util']);
        if (file_exists($utilitarios)) {
            Arquivo::requererDiretorio($utilitarios);
        }
    }

}
