<?php

/**
 * Validador de chave fiscal brasileira (CPF/CNPJ) 
 * @author pablo_veinberg <pveinberg@gmail.com> 
 * @license http://www.gnu.org/licenses/licenses.html#GPL 
 *  
 */
class ValidarChaveFiscal
{

    /**
     * String da chave que será validada.        
     */
    private $chave = '';

    /**
     * Tipo da chave que será validada: CPF ou CNPJ.    
     */
    private $tipo = '';

    /**
     * Extensão da String da chave.  
     */
    private $tamanho = 0;

    /**
     * Array contendo os dígitos da chave ingressada.      
     */
    private $digitos = array();

    /**
     * Primeiro dígito verificador.  
     */
    private $dv1 = 0;

    /**
     * Segundo dígito verificador.      
     */
    private $dv2 = 0;

    /**
     * Array contendo os possíveis erros.      
     */
    private $erros = array();

    const CNPJ_LEN = 14;
    const CNPJ_TIPO = "CNPJ";
    const CPF_LEN = 11;
    const CPF_TIPO = "CPF";
    const DIV = 11;
    // ERROS 
    const APENAS_NUMEROS = 'O #tipo deverá ter apenas números.';
    const TAMANHO_ERRADO = 'O #tipo deverá ter #numero digitos.';
    const DV_ERRADO = 'Dígito verificador incorreto.';
    const NUMERO_INVALIDO = 'O número é inválido.';
    const TIPO_INVALIDO = 'O tipo de chave é inválido.';

    /**
     * Construtor da classe.  
     * $cpf deverá ser uma String para ser validada. 
     * $tipo deverá ser uma String o tipo da chave (CPF/CNPJ). 
     * Será setada a propriedade $chave, $digitos e o tamanho, dependendo do tipo da chave. 
     */
    function __construct($chave, $tipo)
    {

        if ($this->isNullOrEmpty($chave)) {
            $this->addErro(self::NUMERO_INVALIDO);
        }

        if (strtoupper($tipo) == self::CNPJ_TIPO or strtoupper($tipo) == self::CPF_TIPO) {
            $this->setTipo(strtoupper($tipo));
        } else {
            $this->addErro($this->recuperarErro(self::TIPO_INVALIDO));
            return;
        }
        $this->setChave(trim($this->limparChave($chave)));
        $this->setDigitos($this->getChave());
        $this->setTamanho($this->isCNPJ() ? self::CNPJ_LEN : self::CPF_LEN);
    }

    /**
     * Valida o valor inserido no construtor.  
     * Retorna TRUE caso seja válido e FALSE caso contrário.   
     * @return boolean    
     */
    public function isValido()
    {
        $resultado = true;

        if (!$this->isTamanhoValido()) {
            return false;
        }

        if (!$this->isNumeroValido()) {
            $resultado = false;
        }

        if (!$this->isDigitosValidos()) {
            $resultado = false;
        }

        if (!$this->isDigitoVerificadorValido()) {
            $resultado = false;
        }

        return $resultado;
    }

    /**
     * Valida os números verificadores (últimos 2 dígitos da chave).  
     * Retorna TRUE caso válido e FALSE caso contrário.   
     * @return boolean    
     */
    private function isDigitoVerificadorValido()
    {
        $soma = array();

        $arrayMultiplicadorCNPJ = array(
            array(5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2),
            array(6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2)
        );

        $arrayMultiplicadorCPF = array(
            array(10, 9, 8, 7, 6, 5, 4, 3, 2),
            array(11, 10, 9, 8, 7, 6, 5, 4, 3, 2)
        );

        $arrayMultiplicador = $this->isCNPJ() ? $arrayMultiplicadorCNPJ : $arrayMultiplicadorCPF;

        for ($fase = 0; $fase < 2; $fase++) {
            for ($i = 0; $i < count($arrayMultiplicador[$fase]); $i++) {
                $soma[$fase][] = $this->digitos[$i] * $arrayMultiplicador[$fase][$i];
            }
        }

        $resto[0] = array_sum($soma[0]) % self::DIV;
        $resto[1] = array_sum($soma[1]) % self::DIV;

        $this->setDv1($resto[0] < 2 ? 0 : self::DIV - $resto[0]);
        $this->setDv2($resto[1] < 2 ? 0 : self::DIV - $resto[1]);

        if ($this->getDv1() == $this->getDigito($this->getTamanho() - 2) && $this->getDv2() == $this->getDigito($this->getTamanho() - 1)) {
            return true;
        } else {
            $this->addErro($this->recuperarErro(self::DV_ERRADO));
            return false;
        }
    }

    /**
     * Valida o tamanho do CPF especificado na constante CPF_LEN / CNPJ_LEN; 
     * @return boolean 
     */
    private function isTamanhoValido()
    {
        if (count($this->getDigitos()) != $this->getTamanho()) {
            $this->addErro($this->recuperarErro(self::TAMANHO_ERRADO));
            return false;
        }
        return true;
    }

    /**
     * Valida que todos os dígitos da String CPF sejan números; 
     * @return boolean 
     */
    private function isDigitosValidos()
    {
        foreach ($this->getDigitos() as $numero) {
            if (is_null($numero) || !is_numeric($numero)) {
                $this->addErro($this->recuperarErro(self::APENAS_NUMEROS));
                return false;
            }
        }
        return true;
    }

    /**
     * Valida as excepções.  
     * Números de CPF / CNPJ com todos seus dígitos iguais não são permitidos. 
     * @return boolean 
     */
    private function isNumeroValido()
    {

        $numeros = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");

        foreach ($numeros as $numero) {
            $numeroInvalido = $numero;
            $numeroInvalido = str_pad($numeroInvalido, $this->getTamanho(), $numero, STR_PAD_RIGHT);

            if ($this->getChave() == $numeroInvalido) {
                $this->addErro($this->recuperarErro(self::NUMERO_INVALIDO));
                return false;
            }

            unset($numeroInvalido);
        }

        return true;
    }

    /**
     * Retorna apenas os números da chave, caso esta venha formatada. 
     */
    private function limparChave($chave)
    {
        return str_replace("/", "", str_replace("-", "", str_replace(".", "", $chave)));
    }

    /**
     * Verifica se uma String é nula ou vazía. 
     */
    private function isNullOrEmpty($str = null)
    {
        return ((is_null($str)) || (empty($str)));
    }

    /**
     * Retorna TRUE caso a chave seja um CNPJ e FALSE caso contrário.  
     * @return boolean 
     */
    private function isCNPJ()
    {
        if ($this->getTipo() == self::CNPJ_TIPO) {
            return true;
        }
    }

    /**
     * Substitui campos chave nas mensagens de erro.  
     * @return String 
     */
    private function recuperarErro($erro)
    {
        $coringas = array(
            self::CNPJ_TIPO => array(
                '#tipo' => self::CNPJ_TIPO,
                '#numero' => self::CNPJ_LEN),
            self::CPF_TIPO => array(
                '#tipo' => self::CPF_TIPO,
                '#numero' => self::CPF_LEN
            )
        );

        foreach ($coringas[$this->getTipo()] as $chave => $valor) {
            $erro = str_replace($chave, $valor, $erro);
        }

        return $erro;
    }

    /**
     * Retorna a chave formatada.  
     * @return String 
     */
    public function getChaveFormatada()
    {
        $chave = $this->getChave();
        if ($this->getTipo() == self::CPF_TIPO) {
            $return = sprintf("%03d.%03d.%03d-%02d", substr($chave, 0, 3), substr($chave, 3, 3), substr($chave, 6, 3), substr($chave, 9, 2));
        } else {
            $return = sprintf("%02d.%03d.%03d/%04d-%02d", substr($chave, 0, 2), substr($chave, 2, 3), substr($chave, 5, 3), substr($chave, 8, 4), substr($chave, 12, 2));
        }

        return $return;
    }

    //getters and setters 

    /**
     * Getter.  
     */
    public function getChave()
    {
        return $this->chave;
    }

    /**
     * Getter.  
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Setter.  
     */
    public function setChave($chave)
    {
        $this->chave = $chave;
    }

    /**
     * Getter.  
     */
    public function getTamanho()
    {
        return $this->tamanho;
    }

    /**
     * Setter.  
     */
    public function setTamanho($tamanho)
    {
        $this->tamanho = $tamanho;
    }

    /**
     * Setter.  
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }

    /**
     * Getter.  
     */
    public function getDigitos()
    {
        return $this->digitos;
    }

    /**
     * Getter.  
     */
    public function getDigito($index)
    {
        return $this->digitos[$index];
    }

    /**
     * Getter.  
     */
    public function getDv1()
    {
        return $this->dv1;
    }

    /**
     * Getter.  
     */
    public function getDv2()
    {
        return $this->dv2;
    }

    /**
     * Getter.  
     */
    public function getErros()
    {
        return $this->erros;
    }

    /**
     * Setter.  
     */
    public function setCnpj($cnpj)
    {
        $this->cnpj = $cnpj;
    }

    /**
     * Setter.  
     */
    public function setDigitos($digitos)
    {
        $this->digitos = str_split($digitos);
    }

    /**
     * Setter.  
     */
    public function setDv1($dv1)
    {
        $this->dv1 = $dv1;
    }

    /**
     * Setter.  
     */
    public function setDv2($dv2)
    {
        $this->dv2 = $dv2;
    }

    /**
     * Setter.  
     */
    public function setErros($erros)
    {
        $this->erros = $erros;
    }

    /**
     * Adiciona um digito no array dos dígitos da chave.  
     */
    public function addDigito($numero)
    {
        if (is_numeric($numero)) {
            $this->digitos[] = (int) $numero;
        }
    }

    /**
     * Adiciona um erro no array de erros.  
     */
    public function addErro($erro)
    {
        $this->erros[] = $erro;
    }

}
