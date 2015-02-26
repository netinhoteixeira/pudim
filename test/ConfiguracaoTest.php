<?php

ini_set('xdebug.max_nesting_level', 1000);

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'source', 'nucleo', 'lib', 'Pudim', 'Arquivo.php']);

use Pudim\Arquivo;

class ConfiguracaoTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        
    }

    public function testCriarConfiguracao()
    {
        $diretorio = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'source']);
        echo 'Vai requerer o diretório: ' . $diretorio . "\n";
        Arquivo::requererDiretorio($diretorio);

        $tempdir = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'tmp']);
        Arquivo::criarDiretorio($tempdir);

        $arquivo = implode(DIRECTORY_SEPARATOR, [$tempdir, 'configuracao.ini']);
        $configuracao = new \Pudim\Configuracao($arquivo);

        $configuracao->set('sem_0_secao_valor_texto', 'teXto');
        $configuracao->set('sem_0_secao_valor_numero', 3);
        $configuracao->set('sem_0_secao_valor_boleano', true);

        $configuracao->set('secao_a.valor_texto', 'texto');
        $configuracao->set('secao_a.valor_numero', 1);
        $configuracao->set('secao_a.valor_boleano', true);

        $configuracao->set('secao_b.valor_texto', 'Texto');
        $configuracao->set('secao_b.valor_numero', 2);
        $configuracao->set('secao_b.valor_boleano', false);

        $configuracao->set('secao_c.valor_texto', 'teXto');
        $configuracao->set('secao_c.valor_numero', 3);
        $configuracao->set('secao_c.valor_boleano', true);

        $configuracao->set('sem_secao_valor_texto', 'teXto');
        $configuracao->set('sem_secao_valor_numero', 3);
        $configuracao->set('sem_secao_valor_boleano', true);

        print_r($configuracao->getPropriedades());

        $configuracao->persistir();
    }

}
