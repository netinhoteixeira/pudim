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
use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Cache\RedisCache;
use Pudim\Excecoes\FuncaoNaoEncontradaExcecao;
use Pudim\Arquivo;

/**
 * Classe Aplicativo.
 */
class Aplicativo
{

    private $_servidor;
    private $_configuracao;
    private $_documentos;
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
    public function __construct()
    {
        define('__APPDIR__', implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', '..', '..', '..', '..', '..']));

        $this->obterServidor();
        $this->_configuracao = new Configuracao(implode(DIRECTORY_SEPARATOR, [__APPDIR__, 'config', 'configuracao.ini']));

        define('PROJECT_STAGE', $this->_configuracao->get($this->_servidor . '.producao'));

        $this->inicializarVariaveis();
        $this->corrigirRequisicaoVariaveisPostagem();
        $this->_analiseTrafego = $this->iniciarAnaliseTrafego();
        $this->verificarPrimeiroAcesso();
        $this->configurarSessaoNoRedis();
        $this->iniciarSessao();
        $this->definirFusoHorario();
        $this->definirControleDeAcessoDaOrigemDaRequisicao();
        $this->carregarControladores();
    }

    /**
     * Obtém a instância do Aplicativo.
     * 
     * @global Aplicativo $aplicativo
     * @return \Pudim\Aplicativo
     */
    public static function getInstance()
    {
        global $aplicativo;

        if (is_null($aplicativo)) {
            $aplicativo = new Aplicativo();
        }

        return $aplicativo;
    }

    private function inicializarVariaveis()
    {
        $this->criarDiretorioTemporario();
        $this->criarDiretorioLog();
        $this->iniciarSlimApp();
        $this->_documentos = $this->iniciarDocumentos();

        $this->_nome = $this->_configuracao->get('aplicativo.nome');
        $this->_versao = $this->_configuracao->get('aplicativo.versao');
        $this->_icone = $this->_configuracao->get('aplicativo.icone');
        if (isset($_SERVER['SERVER_NAME'])) {
            $this->_enderecoBase = sprintf('%s://%s:%s%s', isset($_SERVER['HTTPS']) &&
                    $_SERVER['HTTPS'] != 'off' ? 'https' : 'http', $_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'], $this->_configuracao->get($this->_servidor . '.contexto'));
        }
        $this->_email = $this->_configuracao->get('email.conta');
    }

    private function obterServidor()
    {
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
    public function getVersao()
    {
        return $this->_versao;
    }

    /**
     * Retorna a configuração do aplicativo.
     * 
     * @return \Pudim\Configuracao
     */
    public function getConfiguracao()
    {
        return $this->_configuracao;
    }

    /**
     * Retorna o acesso aos documentos.
     * 
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    public function getDocumentos()
    {
        return $this->_documentos;
    }

    public function getSlimApp()
    {
        return $this->_slimApp;
    }

    public function getLog()
    {
        return $this->_slimApp->getLog();
    }

    /**
     * Retorna o nome do aplicativo.
     * 
     * @return string
     */
    public function getNome()
    {
        return $this->_nome;
    }

    /**
     * Retorna o ícone do aplicativo.
     * 
     * @return string
     */
    public function getIcone()
    {
        return $this->_icone;
    }

    /**
     * Retorna o endereço base.
     * 
     * @return string
     */
    public function getEnderecoBase()
    {
        return $this->_enderecoBase;
    }

    /**
     * Retorna o e-mail.
     * 
     * @return string
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * Adiciona a segurança nos subdiretórios. Use asterisco (*) para todos.
     *
     * @param string $nome Nome da rota a ser protegida. Ex.: /acessorestrito
     */
    public function protegerRota($nome)
    {
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
    function definirRotasParaCadastro($nome)
    {
        $nomeFuncao = ucfirst($nome);

        $this->definirRotaObtencao('/cadastro/' . $nome . '/', $nomeFuncao . 'Listar');
        $this->definirRotaObtencao('/cadastro/' . $nome . '/:id', $nomeFuncao . 'Obter');
        $this->definirRotaPostagem('/cadastro/' . $nome . '', $nomeFuncao . 'Salvar');
        $this->definirRotaPostagem('/cadastro/' . $nome . '/:id', $nomeFuncao . 'Salvar');
        $this->definirRotaSubstituicao('/cadastro/' . $nome . '/:id', $nomeFuncao . 'Salvar');
        $this->definirRotaRemocao('/cadastro/' . $nome . '/:id', $nomeFuncao . 'Remover');
    }

    /**
     * Define a rota para a chamada GET.
     * 
     * @param string $caminho Caminho da rota
     * @param function $funcao Função a ser chamada
     * @throws FuncaoNaoEncontradaExcecao
     */
    function definirRotaObtencao($caminho, $funcao)
    {
        if (!function_exists($funcao)) {
            throw new FuncaoNaoEncontradaExcecao('A função \'' . $funcao .
            '\' definida para a rota obter \'' . $caminho .
            '\' não existe.');
        }

        $this->_slimApp->get($caminho, $funcao);
    }

    /**
     * Define a rota para a chamada POST.
     * 
     * @param string $caminho Caminho da rota
     * @param function $funcao Função a ser chamada
     * @throws FuncaoNaoEncontradaExcecao
     */
    function definirRotaPostagem($caminho, $funcao)
    {
        if (!function_exists($funcao)) {
            throw new FuncaoNaoEncontradaExcecao('A função \'' . $funcao .
            '\' definida para a rota postagem \'' . $caminho .
            '\' não existe.');
        }

        $this->_slimApp->post($caminho, $funcao);
    }

    /**
     * Define a rota para a chamada PUT.
     * 
     * @param string $caminho Caminho da rota
     * @param function $funcao Função a ser chamada
     * @throws FuncaoNaoEncontradaExcecao
     */
    function definirRotaSubstituicao($caminho, $funcao)
    {
        if (!function_exists($funcao)) {
            throw new FuncaoNaoEncontradaExcecao('A função \'' . $funcao .
            '\' definida para a rota substituição \'' . $caminho .
            '\' não existe.');
        }

        $this->_slimApp->put($caminho, $funcao);
    }

    /**
     * Define a rota para a chamada DELETE.
     * 
     * @param string $caminho Caminho da rota
     * @param function $funcao Função a ser chamada
     * @throws FuncaoNaoEncontradaExcecao
     */
    function definirRotaRemocao($caminho, $funcao)
    {
        if (!function_exists($funcao)) {
            throw new FuncaoNaoEncontradaExcecao('A função \'' . $funcao .
            '\' definida para a rota postagem \'' . $caminho .
            '\' não existe.');
        }

        $this->_slimApp->delete($caminho, $funcao);
    }

    /**
     * Chama a função fornecida caso não encontre a rota (Erro 404).
     * 
     * @param function $funcao Função a ser chamada
     * @throws FuncaoNaoEncontradaExcecao
     */
    function seRotaNaoForEncontrada($funcao)
    {
        if (!function_exists($funcao)) {
            throw new FuncaoNaoEncontradaExcecao('A função \'' .
            $funcao . '\' não existe.');
        }

        $this->_slimApp->notFound($funcao);
    }

    /**
     *
     * @return type
     */
    private function obterRequisicao()
    {
        return $this->_slimApp->request();
    }

    /**
     *
     * @param type $variavel
     * @return type
     */
    function requisicaoVariavelObter($variavel)
    {
        $requisicao = $this->obterRequisicao();
        return $requisicao->get($variavel);
    }

    function existeRequisicaoVariavelObter($variavel)
    {
        return !is_null($this->requisicaoVariavelObter($variavel));
    }

    function existeRequisicaoVariavelPostagem($variavel)
    {
        return isset($_POST[$variavel]);
    }

    function existeRequisicaoVariavelPostagemNaoVazia($variavel)
    {
        return ((isset($_POST[$variavel])) && (!is_null($_POST[$variavel])) && (!empty($_POST[$variavel])));
    }

    function requisicaoVariavelPostagem($variavel, $podeSerVazia = true)
    {
        if (($this->existeRequisicaoVariavelPostagemNaoVazia($variavel)) && (!$podeSerVazia)) {
            return $_POST[$variavel];
        } elseif ($this->existeRequisicaoVariavelPostagem($variavel)) {
            return $_POST[$variavel];
        } else {
            return null;
        }
    }

    function get($variavel)
    {
        return $this->requisicaoVariavelObter($variavel);
    }

    function getExists($variavel)
    {
        return $this->existeRequisicaoVariavelObter($variavel);
    }

    function post($variavel, $podeSerVazia = true)
    {
        return $this->requisicaoVariavelPostagem($variavel, $podeSerVazia);
    }

    function postExists($variavel, $podeSerVazia = true)
    {
        if ($podeSerVazia) {
            return $this->existeRequisicaoVariavelPostagem($variavel);
        } else {
            return $this->existeRequisicaoVariavelPostagemNaoVazia($variavel);
        }
    }

    public static function existeVariavelSessao($variavel)
    {
        return isset($_SESSION[$variavel]);
    }

    public static function obterVariavelSessao($variavel)
    {
        return $_SESSION[$variavel];
    }

    public static function definirVariavelSessao($variavel, $valor)
    {
        $_SESSION[$variavel] = $valor;
    }

    public static function removerVariavelSessao($variavel)
    {
        if (self::existeVariavelSessao($variavel)) {
            unset($_SESSION[$variavel]);
        }
    }

    /**
     * Obtém o usuário da sessão.
     */
    function getUsuarioSessao()
    {
        if (self::existeVariavelSessao('userid')) {
            return $this->_documentos->createQueryBuilder('Domain\Entity\Usuario')
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
     * @param Object $jsonfy Objeto a ser codificado em JSON
     */
    function saida($jsonfy = null)
    {
        if (!is_null($jsonfy)) {
            $this->_slimApp->contentType('application/json; charset=utf-8');

            echo json_encode($jsonfy);
        }
    }

    private function iniciarSlimApp()
    {
        $config = [];

        $logfile = $this->_configuracao->get('aplicativo.log');
        if (!is_null($logfile)) {
            $logfile .= '-slim.log';
            $config['log.enabled'] = true;
            $log = new \Slim\LogWriter(fopen(LOGDIR . DIRECTORY_SEPARATOR . $logfile, 'a'));
            $config['log.writer'] = $log;
        }

        $config['debug'] = ($this->_configuracao->get('aplicativo.debug') === TRUE);
        if ($config['debug']) {
            $config['log.level'] = \Slim\Log::DEBUG;
        } else {
            $config['log.level'] = \Slim\Log::WARN;
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
    public function enviarEmail($para, $assunto, $mensagem)
    {
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
     * Acesso ao banco de documentos.
     * 
     * @return \Doctrine\ODM\MongoDB\DocumentManager
     */
    private function iniciarDocumentos()
    {
        AnnotationDriver::registerAnnotationClasses();

        $classLoader = new ClassLoader('Domain\Entity', implode(DIRECTORY_SEPARATOR, [__APPDIR__, 'app', 'models']));
        $classLoader->register();

        // cria os diretórios dos proxys e hydrators, caso não haja (necessários
        // para o Doctrine MongoDB)
        if (!PROJECT_STAGE) {
            Arquivo::criarDiretorio(implode(DIRECTORY_SEPARATOR, [__APPDIR__, 'tmp', 'models', 'Domain', 'Entity', 'Proxies']));
            Arquivo::criarDiretorio(implode(DIRECTORY_SEPARATOR, [__APPDIR__, 'tmp', 'models', 'Domain', 'Entity', 'Hydrators']));
        }

        $configuracao = new Configuration();
        $metadata = AnnotationDriver::create(implode(DIRECTORY_SEPARATOR, [__APPDIR__, 'app', 'models', 'Domain', 'Entity']));
        $configuracao->setMetadataDriverImpl($metadata);
        $configuracao->setAutoGenerateProxyClasses(!((boolean) PROJECT_STAGE));
        $configuracao->setProxyDir(implode(DIRECTORY_SEPARATOR, [__APPDIR__, 'tmp', 'models', 'Domain', 'Entity', 'Proxies']));
        $configuracao->setProxyNamespace('Proxies');
        $configuracao->setAutoGenerateHydratorClasses(
                !((boolean) PROJECT_STAGE));
        $configuracao->setHydratorDir(implode(DIRECTORY_SEPARATOR, [__APPDIR__, 'tmp', 'models', 'Domain', 'Entity', 'Hydrators']));
        $configuracao->setHydratorNamespace('Hydrators');
        $configuracao->setDefaultDB(
                $this->_configuracao->get(
                        $this->_servidor . '.persistencia_banco'));

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
            $metadataCache = new RedisCache();
            $metadataCache->setRedis($redis);
            $configuracao->setMetadataCacheImpl($metadataCache);

            unset($cache_server, $cache_port, $redis, $metadataCache);
        }

        $conexao = new Connection($this->_configuracao->get(
                        $this->_servidor . '.persistencia_uri'));
        $documentos = DocumentManager::create($conexao, $configuracao);

        // FIX: Muito importante pois força a criação dos índices no aplicativo
        $documentos->getSchemaManager()->ensureIndexes();

        return $documentos;
    }

    /**
     * Inicia a análise de tráfego.
     */
    private function iniciarAnaliseTrafego()
    {

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
    public function getAnaliseTrafego()
    {
        return $this->_analiseTrafego;
    }

    /**
     * Inicia o aplicativo.
     */
    public function iniciar()
    {
        $this->_slimApp->run();
    }

    /**
     * Cria o diretório temporário.
     */
    private function criarDiretorioTemporario()
    {
        define('TMPDIR', __APPDIR__ . DIRECTORY_SEPARATOR . 'tmp');
        Arquivo::criarDiretorio(TMPDIR);
        ini_set('sys_temp_dir', TMPDIR);
    }

    /**
     * Cria o diretório de log.
     */
    private function criarDiretorioLog()
    {
        $logfile = $this->_configuracao->get('aplicativo.log');
        if (!is_null($logfile)) {
            define('LOGDIR', __APPDIR__ . DIRECTORY_SEPARATOR . 'log');
            Arquivo::criarDiretorio(LOGDIR);
            ini_set('log_errors', 1);
            ini_set('error_log', LOGDIR . DIRECTORY_SEPARATOR . $logfile . '-php.log');
        }
    }

    /**
     * Verifica se é o primeiro acesso, atualiza as configurações.
     */
    private function verificarPrimeiroAcesso()
    {
        // caso seja o primeiro acesso ao sistema
        if ($this->_configuracao->get('acesso.primeiro')) {
            try {
                $this->criarPrimeiroUsuario();
                $this->saida('Usuário cadastrado. Recarregue a página.');
            } catch (MongoCursorException $ex) {
                $this->saida('Usuário já cadastrado. Recarregue a página.');
            }

            // atualiza as informações do arquivo de configuração
            $this->_configuracao->set('acesso.primeiro', false);
            $this->_configuracao->persist();

            exit();
        }
    }

    /**
     * Cria o primeiro usuário.
     */
    private function criarPrimeiroUsuario()
    {
        $usuario = new \Domain\Entity\Usuario();
        $usuario->setApelido('Netinho');
        $usuario->setEmail('fco.ernesto@gmail.com');
        # Senha: 123456
        # Criptografia: sha1
        $usuario->setSenha('7c4a8d09ca3762af61e59520943dc26494f8941b');
        $usuario->setNivelAcesso('ADMINISTRADOR');
        $usuario->setAtivo(true);

        $this->_documentos->persist($usuario);
        $this->_documentos->flush();
    }

    /**
     * Captura os argumentos JSON postados e os coloca em $_POST
     * O $http do AngularJS faz envio de postagens JSON (não a forma normal
     * "form encoded"). No Slim Framework é necessário a devida requisição dos
     * parâmetros POST do AngularJS.
     */
    private function corrigirRequisicaoVariaveisPostagem()
    {
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
    private function configurarSessaoNoRedis()
    {
        $cache_uri = $this->_configuracao->get($this->_servidor . '.cache_uri');
        if ((PROJECT_STAGE) && (class_exists('Redis')) && ($cache_uri)) {
            ini_set('session.save_handler', 'redis');
            ini_set('session.save_path', $cache_uri);
        }
    }

    /**
     * Inicia a sessão.
     */
    private function iniciarSessao()
    {
        if (!headers_sent()) {
            session_start();
        }
    }

    /**
     * Define o fuso horário.
     */
    private function definirFusoHorario()
    {
        date_default_timezone_set(
                $this->_configuracao->get('aplicativo.fuso_horario'));
    }

    /**
     * Define o controle de acesso da origem da requisição.
     */
    private function definirControleDeAcessoDaOrigemDaRequisicao()
    {
        if (!headers_sent()) {
            header('Access-Control-Allow-Origin: *');
        }
    }

    /**
     * Carrega todos os controladores.
     */
    private function carregarControladores()
    {
        $controladores = implode(DIRECTORY_SEPARATOR, [__APPDIR__, 'app', 'controllers']);
        if (file_exists($controladores)) {
            Arquivo::requererDiretorio($controladores);
        }
    }

}
