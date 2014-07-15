<?php

namespace Pudim;

/**
 * Classe Arquivo.
 */
class Arquivo
{

    /**
     * Remove o arquivo fornecido.
     * 
     * @param string $arquivo Caminho do arquivo
     */
    public static function remover($arquivo)
    {
        try {
            if ((isset($arquivo)) && (!is_null($arquivo)) && (file_exists($arquivo))) {
                unlink($arquivo);
            }
        } catch (Exception $ex) {
            // ignora
        }
    }

    /**
     * Localiza e requisita todos os arquivos de um determinado diretório.
     *
     * @param string $path Caminho que será requisitado os arquivos
     */
    public static function requererDiretorio($path)
    {
        foreach (glob($path . '*.php') as $filename) {
            require_once $filename;
        }
    }

}
