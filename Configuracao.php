<?php

namespace Pudim;

/**
 * Classe Configuracao.
 */
class Configuracao
{

    private $_arquivo;
    private $_propriedades = array();

    function Configuracao($arquivo)
    {
        $this->_arquivo = $arquivo;
        $this->_propriedades = $this->better_parse_ini_file($this->_arquivo, true);
    }

    function get($nome)
    {
        if (strpos($nome, '.')) {
            list($nomeSessao, $propriedade) = explode('.', $nome);
            $secao = &$this->_propriedades[$nomeSessao];
            $nome = $propriedade;
        } else {
            $secao = &$propriedades;
        }

        if (is_array($secao) && isset($secao[$nome])) {
            return $secao[$nome];
        }

        return false;
    }

    function set($nome, $valor)
    {
        if (strpos($nome, '.')) {
            list($nomeSessao, $propriedade) = explode('.', $nome);
            $secao = &$this->_propriedades[$nomeSessao];
            $nome = $propriedade;
        } else {
            $secao = &$propriedades;
        }

        if (is_array($secao) && isset($secao[$nome])) {
            $secao[$nome] = $valor;
        }
    }

    function persist()
    {
        $resultado = array();
        foreach ($this->_propriedades as $chave => $valor) {
            if (is_array($valor)) {
                $resultado[] = "[$chave]";
                foreach ($valor as $schave => $svalor) {
                    $recurso = "$schave = ";

                    if (is_numeric($svalor)) {
                        $recurso .= $svalor;
                    } elseif (is_bool($svalor)) {
                        $recurso .= $svalor ? 'true' : 'false';
                    } else {
                        $recurso .= $svalor;
                    }

                    $resultado[] = $recurso;
                }

                // blank line
                $resultado[] = '';
            } else {
                $resultado[] = "$chave = ";

                if (is_numeric($svalor)) {
                    $recurso .= $svalor;
                } elseif (is_bool($svalor)) {
                    $recurso .= $svalor ? 'true' : 'false';
                } else {
                    $recurso .= '"' . $svalor . '"';
                }

                $resultado[] = $recurso;
            }
        }
        $this->safefilerewrite($this->_arquivo, implode("\r\n", $resultado));
    }

    private function safefilerewrite($fileName, $dataToSave)
    {
        if ($fp = fopen($fileName, 'w')) {
            $startTime = microtime();
            do {
                $canWrite = flock($fp, LOCK_EX);
                // If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load 
                if (!$canWrite) {
                    usleep(round(rand(0, 100) * 1000));
                }
            } while ((!$canWrite)and ( (microtime() - $startTime) < 1000));

            //file was locked so now we can store information 
            if ($canWrite) {
                fwrite($fp, $dataToSave);
                flock($fp, LOCK_UN);
            }
            fclose($fp);
        }
    }

    /**
     * array better_parse_ini_file (string $filename [, boolean $process_sections] )
     *
     * Purpose: Load in the ini file specified in filename, and return
     *          the settings in an associative array. By setting the
     *          last $process_sections parameter to true, you get a
     *          multidimensional array, with the section names and
     *          settings included. The default for process_sections is
     *          false.
     *
     * Return: - An associative array containing the data
     *        - false if any error occured
     *
     * Author: Sebastien Cevey <seb@cine7.net>
     *        Original Code base : <info@megaman.nl>
     */
    private function better_parse_ini_file($filename, $process_sections = false)
    {
        $ini_array = array();
        $sec_name = '';
        $lines = file($filename);
        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            if (($line[0] === '[') && ($line[strlen($line) - 1] === ']')) {
                $sec_name = substr($line, 1, strlen($line) - 2);
            } else {
                $pos = strpos($line, '=');
                $property = trim(substr($line, 0, $pos));
                $value = trim(substr($line, $pos + 1));

                switch ($value) {
                    case 'true':
                        $value = true;
                        break;

                    case 'false':
                        $value = false;
                        break;
                }

                if ($process_sections) {
                    $ini_array[$sec_name][$property] = $value;
                } else {
                    $ini_array[$property] = $value;
                }
            }
        }

        return $ini_array;
    }

}
