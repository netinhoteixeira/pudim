<?php

ini_set('xdebug.max_nesting_level', 1000);

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'source', 'nucleo', 'lib', 'Pudim', 'Arquivo.php']);

use Pudim\Arquivo;

class ArquivoTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        
    }

    public function testRequererDiretorio()
    {
        // Classe Texto não importada
        //echo \Pudim\Texto::reduzirNome('Francisco Ernesto Teixeira', '10');

        $diretorio = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'source']);
        echo 'Vai requerer o diretório: ' . $diretorio . "\n";
        Arquivo::requererDiretorio($diretorio);

        echo \Pudim\Texto::reduzirNome('Francisco Ernesto Teixeira', '10');
    }

}
