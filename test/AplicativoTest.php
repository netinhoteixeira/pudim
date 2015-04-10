<?php

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'source', 'nucleo', 'lib', 'Pudim', 'Arquivo.php']);

class AplicativoTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        require_once '../vendor/autoload.php';
        \Pudim\Arquivo::requererDiretorio(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'source']));
    }

    public function testEfetuarConexao()
    {
        $aplicativo = \Pudim\Aplicativo::getInstance(__DIR__ . DIRECTORY_SEPARATOR .'../');

        // cadastra um funcionário
        $funcionario = new \Domain\Entity\Funcionario();

        $funcionario->setCadastro(new \DateTime());
        $funcionario->setNome('Joao');
        $funcionario->setNascimento(new \DateTime());
        $funcionario->setSexo('M');
        $funcionario->setCpf('05486864401');
        $funcionario->setEstadocivil('S');
        $funcionario->setPerfil('F');
        $funcionario->setAtivo(true);

        try {
            $aplicativo->getConexao()->persist($funcionario);
            $aplicativo->getConexao()->flush();

            echo 'Cadastrado com sucesso!';
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

}
