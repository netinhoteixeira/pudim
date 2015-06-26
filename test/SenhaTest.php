<?php

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'source', 'nucleo', 'lib', 'Pudim', 'Arquivo.php']);

use Pudim\Senha;

class SenhaTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Pudim\Arquivo::requererDiretorio(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'source']));
    }

    public function testGerarSenha()
    {
        echo Senha::gerar() . "\n";
        echo Senha::gerar(15) . "\n";
        echo Senha::gerar(10, TRUE) . "\n";
        echo Senha::gerar(8, FALSE, 'ld') . "\n";
    }
    
    public function testGerarSenhaCriptografada()
    {
        echo Senha::gerarCriptografada() . "\n";
        echo Senha::gerarCriptografada(15) . "\n";
        echo Senha::gerarCriptografada(10, TRUE) . "\n";
        echo Senha::gerarCriptografada(8, FALSE, 'ld') . "\n";
    }

}
