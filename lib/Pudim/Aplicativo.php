<?php

namespace Pudim;

use Doctrine\Common\ClassLoader;
use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

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
    private $_enderecoBase;
    private $_email;
    private $_analiseTrafego;

    public function __construct()
    {
        $this->_servidor = 'servidor_' . str_replace('.', '_', $_SERVER['SERVER_NAME']);
        $this->_configuracao = new Configuracao(__DIR__ . '/../../../../../configuracao.ini');

        define('PROJECT_STAGE', $this->_configuracao->get($this->_servidor . '.producao'));

        $this->_documentos = $this->iniciarDocumentos();

        $this->_slimApp = new \Slim\Slim();
        $this->_slimApp->hook('slim.before.router', function() {
            $GLOBALS['usuarioSessao'] = Aplicativo::getUsuarioSessao();
        });

        $this->_nome = $this->_configuracao->get('aplicativo.nome');
        $this->_icone = $this->_configuracao->get('aplicativo.icone');
        $this->_enderecoBase = sprintf('%s://%s:%s%s', isset($_SERVER['HTTPS']) &&
                $_SERVER['HTTPS'] != 'off' ? 'https' : 'http', $_SERVER['SERVER_NAME'], $_SERVER['SERVER_PORT'], $this->_configuracao->get($this->_servidor . '.contexto'));
        $this->_email = $this->_configuracao->get('email.conta');

        $this->corrigirRequisicaoVariaveisPostagem();
        $this->_analiseTrafego = $this->iniciarAnaliseTrafego();

        $this->criarDiretorioTemporario();
        $this->verificarPrimeiroAcesso();
        $this->configurarSessaoNoRedis();
        $this->iniciarSessao();
        $this->definirFusoHorario();
        $this->definirControleDeAcessoDaOrigemDaRequisicao();
        $this->carregarControladores();
    }

    /**
     * 
     * @global Aplicativo $aplicativo
     * @return \Aplicativo
     */
    public static function getInstance()
    {
        global $aplicativo;

        if (is_null($aplicativo)) {
            $aplicativo = new Aplicativo();
        }

        return $aplicativo;
    }

    public function getConfiguracao()
    {
        return $this->_configuracao;
    }

    public function getDocumentos()
    {
        return $this->_documentos;
    }

    public function getSlimApp()
    {
        return $this->_slimApp;
    }

    public function getNome()
    {
        return $this->_nome;
    }

    public function getIcone()
    {
        return $this->_icone;
    }

    public function getEnderecoBase()
    {
        return $this->_enderecoBase;
    }

    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * Adiciona a segurança nos subdiretórios. Use asterisco (*) para todos.
     *
     * @param type $nome
     */
    public function protegerRota($nome)
    {
        $arquivoSeguranca = __DIR__ . '/../../../../../HttpBasicAuthRouteDatabaseCustom.inc.php';
        if (file_exists($arquivoSeguranca)) {
            require_once(__DIR__ . '/auxiliar/slim/HttpBasicAuthDatabase.php');
            require_once(__DIR__ . '/auxiliar/slim/HttpBasicAuthRouteDatabase.php');
            require_once($arquivoSeguranca);

            $this->_slimApp->add(new \HttpBasicAuthRouteDatabaseCustom('/' . $nome));
        }
    }

    /**
     * Define as rotas utilizadas em um cadastro.
     *
     * @param type $nome Nome da Rota
     */
    function definirRotasParaCadastro($nome)
    {
        $this->_slimApp->get('/cadastro/' . $nome . '/', $nome . 'Listar');
        $this->_slimApp->get('/cadastro/' . $nome . '/:id', $nome . 'Obter');
        $this->_slimApp->post('/cadastro/' . $nome . '', $nome . 'Salvar');
        $this->_slimApp->post('/cadastro/' . $nome . '/:id', $nome . 'Salvar');
        $this->_slimApp->delete('/cadastro/' . $nome . '/:id', $nome . 'Remover');
    }

    function definirRotaObter($nome, $funcao)
    {
        if (!function_exists($funcao)) {
            throw new \Pudim\Excecoes\FuncaoNaoEncontradaExcecao('A função \'' . $funcao . '\' definida para a rota obter \'' . $nome . '\' não existe.');
        }

        $this->_slimApp->get($nome, $funcao);
    }

    function definirRotaPostagem($nome, $funcao)
    {
        if (!function_exists($funcao)) {
            throw new \Pudim\Excecoes\FuncaoNaoEncontradaExcecao('A função \'' . $funcao . '\' definida para a rota postagem \'' . $nome . '\' não existe.');
        }

        $this->_slimApp->post($nome, $funcao);
    }

    function definirRotaNaoEncontrada($nome)
    {
        $this->_slimApp->notFound($nome);
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
        if (Aplicativo::existeVariavelSessao($variavel)) {
            unset($_SESSION[$variavel]);
        }
    }

    /**
     * Obtém o usuário da sessão.
     */
    function getUsuarioSessao()
    {
        if (Aplicativo::existeVariavelSessao('userid')) {
            return $this->_documentos->createQueryBuilder('usuario')
                            ->field('_id')->equals(Aplicativo::obterVariavelSessao('userid'))
                            ->getQuery()
                            ->getSingleResult();
        } else {
            return null;
        }
    }

    /**
     * Retorna o código da empresa (se houver).
     */
    function getEmpresaSessao()
    {
        if (Aplicativo::existeVariavelSessao('empresaid')) {
            return $this->_documentos->createQueryBuilder('empresa')
                            ->field('_id')->equals(Aplicativo::obterVariavelSessao('empresaid'))
                            ->getQuery()
                            ->getSingleResult();
        } else {
            return null;
        }
    }

    function saida($jsonfy = null)
    {
        $this->_slimApp->contentType('application/json; charset=utf-8');

        if (!is_null($jsonfy)) {
            echo json_encode($jsonfy);
        }
    }

    /**
     *
     * @global array $config
     * @param string $para
     * @param string $assunto
     * @param string $mensagem
     * @return boolean
     */
    public function enviarEmail($para, $assunto, $mensagem)
    {
        // para enviar email com HTML, o cabeçalho Content-type precisa ser
        // definido
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

        // cabeçalhos adicionais
        $headers .= 'To: ' . $para . "\r\n";
        $headers .= 'From: ' . $this->_nome . ' <' . $this->_email . '>' . "\r\n";

        // envia-o
        $mail = new \PHPMailer();
        $mail->Priority = 1; // Email priority (1 = High, 3 = Normal, 5 = low)
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

        $emailLogoFilename = __DIR__ . '/../../../../../templates/email-logo.png';
        if (file_exists($emailLogoFilename)) {
            $mail->AddEmbeddedImage($emailLogoFilename, 'logo');
        }

        if (!$mail->Send()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Conexão ao banco de dados.
     * 
     * @return DocumentManager
     */
    private function iniciarDocumentos()
    {
        AnnotationDriver::registerAnnotationClasses();

        $classLoader = new ClassLoader('domain', __DIR__ . '/../../../../..');
        $classLoader->register();

        // cria os diretórios dos proxys e hydrators, caso não haja (necessários
        // para o Doctrine MongoDB)
        if (!PROJECT_STAGE) {
            if (!file_exists(__DIR__ . '/../../../../../generate')) {
                mkdir(__DIR__ . '/../../../../../generate', 0744, true);
            }

            if (!file_exists(__DIR__ . '/../../../../../generate/proxies')) {
                mkdir(__DIR__ . '/../../../../../generate/proxies', 0744, true);
            }

            if (!file_exists(__DIR__ . '/../../../../../generate/hydrators')) {
                mkdir(__DIR__ . '/../../../../../generate/hydrators', 0744, true);
            }
        }

        $configuracao = new Configuration();
        $metadata = AnnotationDriver::create(__DIR__ . '/../../../../../domain');
        $configuracao->setMetadataDriverImpl($metadata);
        $configuracao->setAutoGenerateProxyClasses(!((boolean) PROJECT_STAGE));
        $configuracao->setProxyDir(__DIR__ . '/../../../../../generate/proxies');
        $configuracao->setProxyNamespace('Proxies');
        $configuracao->setAutoGenerateHydratorClasses(!((boolean) PROJECT_STAGE));
        $configuracao->setHydratorDir(__DIR__ . '/../../../../../generate/hydrators');
        $configuracao->setHydratorNamespace('Hydrators');
        $configuracao->setDefaultDB($this->_configuracao->get($this->_servidor . '.persistencia_banco'));

        //$configuracao->setLoggerCallable(function (array $log) { print_r($log); });
        if ((PROJECT_STAGE) && (class_exists('Redis'))) {
            $redis = new Redis();
            $redis->pconnect('127.0.0.1', 6379);
            $metadataCache = new \Doctrine\Common\Cache\RedisCache();
            $metadataCache->setRedis($redis);
            $configuracao->setMetadataCacheImpl($metadataCache);
        }

        $conexao = new Connection($this->_configuracao->get($this->_servidor . '.persistencia_uri'));
        $documentos = DocumentManager::create($conexao, $configuracao);

        // FIX: Muito importante pois força a criação dos índices no aplicativo
        $documentos->getSchemaManager()->ensureIndexes();

        return $documentos;
    }

    private function iniciarAnaliseTrafego()
    {

        if ($this->_configuracao->get($this->_servidor . '.piwik_id')) {
            require_once(__DIR__ . '/auxiliar/PiwikTracker.php');

            $piwikTracker = new PiwikTracker($this->_configuracao->get($this->_servidor . '.piwik_url'), $this->_configuracao->get($this->_servidor . '.piwik_id'));

            if ($this->_configuracao->get($this->_servidor . '.piwik_token_auth')) {
                $piwikTracker->setTokenAuth($this->_configuracao->get($this->_servidor . '.piwik_token_auth'));
            }

            if (isset($_SERVER['HTTP_REFERER'])) {
                $piwikTracker->setReferrer($_SERVER['HTTP_REFERER']);
            }

            $piwikTracker->setIp($_SERVER['REMOTE_ADDR']);
            $piwikTracker->setUserAgent($_SERVER['HTTP_USER_AGENT']);
            $idioma = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            $piwikTracker->setLanguage($idioma[0]);
            unset($idioma);

            if ($this->postExists('localTime')) {
                $piwikTracker->setLocalTime($this->post('localTime'));
            }

            if (($this->postExists('screenWidth')) && ($this->postExists('screenHeight'))) {
                $piwikTracker->setResolution($this->post('screenWidth'), $this->post('screenHeight'));
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
     * Obtém a Análise de Tráfego.
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
        define('TMPDIR', __DIR__ . '/../../../../../tmp');
        if (!file_exists(TMPDIR)) {
            mkdir(TMPDIR, 0744, true);
        }
        ini_set('sys_temp_dir', TMPDIR);
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
        $usuario = new usuario();
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
                foreach ($_POST as $key => $value) {
                    if (gettype($value) === 'string') {
                        $_POST[$key] = trim($value);
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
        if ((PROJECT_STAGE) && (class_exists('Redis'))) {
            ini_set('session.save_handler', 'redis');
            ini_set('session.save_path', 'tcp://127.0.0.1:6379');
        }
    }

    /**
     * Inicia a sessão.
     */
    private function iniciarSessao()
    {
        session_start();
    }

    /**
     * Define o Fuso Horário.
     */
    private function definirFusoHorario()
    {
        date_default_timezone_set($this->_configuracao->get('aplicativo.fuso_horario'));
    }

    /**
     * Define o controle de acesso da origem da requisição.
     */
    private function definirControleDeAcessoDaOrigemDaRequisicao()
    {
        header('Access-Control-Allow-Origin: *');
    }

    /**
     * Carrega todos os controladores.
     */
    private function carregarControladores()
    {
        $controladores = __DIR__ . '/../../../../../controllers/';
        if (file_exists($controladores)) {
            \Pudim\Arquivo::requererDiretorio($controladores);
        }
    }

}
