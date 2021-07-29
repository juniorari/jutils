<?php

/**
 * Class conteiner for methods and lot of stuff utilities
 *
 * @required:
 *  - mbstring enabled on php.ini
 *  - curl enabled on php.ini
 *
 * @author Ari Junio(r) 
 *
 */


namespace JuniorAri\Utils;


use Exception;
use DateTime;

class JUtils {

	/**
	 * Constants defineds to best legibility source-code. The prefix NN_ mean who
	 * it is related to public method capitalizeName().
	 */
	const NN_POINT = '\.';
	const NN_SPACE_POINT = '. ';
	const NN_SPACE = ' ';
	const NN_REGEX_MULTIPLES_SPACES = '\s+';
	const NN_REGEX_ROMAN_NUMBER = '^M{0,4}(CM|CD|D?C{0,3})(XC|XL|L?X{0,3})(IX|IV|V?I{0,3})$';
	
	
	const LOWERCASE = 0;
	const UPPERCASE = 1;
	const CAPITALIZED = 2;
	

	/**
     * @var array The default timezone
     */
	private $_timeZone = 'America/Manaus';

	
	public function __construct($tz = '') {

		if ($tz) {
			$this->_timeZone = $tz;
		}
		date_default_timezone_set($this->_timeZone);
	}

    private static function printError(Exception $e) {
        die("ERRO #`{$e->getCode()}` - `{$e->getMessage()}`<br>File: `{$e->getFile()}`:`{$e->getLine()}`:");
    }


    public static function dd(...$args) {

        foreach($args as $var) {
            self::debug($var);
        }
        die();
    }


    /**
     * Function debug. Show any content inside tag <pre>.
     *
     * @param mixed	$var The variable to be showed
     * @param boolean $continue Break or not the execution of code
     * @param boolean $trimBlankSpaces Trim any space of variable
     */
    public static function debug($var, $continue=true, $trimBlankSpaces=false)
    {
        echo "\n<pre>\n";
        if ($trimBlankSpaces) {
            $var = preg_replace("%\n[\t\ \n\r]+%", "\n", $var);
        }
        if (is_bool($var)) {
            var_dump($var);
        } elseif (is_array($var)) {
            print_r($var);
        } else {
            print_r($var);
        }
        echo "\n</pre>\n";

        if (!$continue) die();
    }


	/**
	 * Normalizes the given proper name, applying the correct capitalization accordingly
     * with the rules and exceptions defined in the code.
     * BY A DESIGN DECISION, MULTIBYTE FUNCTIONS (MB_) WERE ALWAYS USED
     * AS POSSIBLE, TO GUARANTEE ITS USABILITY ON UNICODE STRINGS.
	 * @param string $name The name to be capitalized
	 * @return string The duly standardized name
	 */
    public static function capitalizeName($name)
    {

        try {

            /**
             * The first task of normalization is to deal with parts of the name that
             * perhaps they are abbreviated, considering the existence of
             * full stops (eg JOÃO A. DA SILVA, where "A." is an abbreviated part).
             * Since later we will divide the name in parts taking in
             * considering the space character (" "), we need to ensure that there is a
             * space after the point. We do this by replacing all occurrences of the
             * dot by a sequence of dot and space.
             */
            $nome = mb_ereg_replace(self::NN_POINT, self::NN_SPACE_POINT, $name);

            /**
             * The previous procedure, or even the wrong typing, may have
             * introduced multiple spaces between the parts of the name, which is fully
             * unwanted. To fix this issue, we use a replacement
             * based on regular expression, which will swap all occurrences of
             * multiple spaces for single spaces.
             */
            $nome = mb_ereg_replace(self::NN_REGEX_MULTIPLES_SPACES, self::NN_SPACE, $nome);

            /**
             * That done, we can make the "gross" capitalization, leaving each part of the
             * name with the first letter capitalized and the others lowercase. So,
             * JOÃO DA SILVA => João Da Silva.
             */
            $nome = mb_convert_case($nome, MB_CASE_TITLE, mb_detect_encoding($nome));

            /**
             * At this point, we split the name into parts to work with each.
             * from them separately.
             */
            $partesNome = mb_split(self::NN_SPACE, $nome);

            /**
             * The exceptions to the capitalization rule are defined below. Like
             * we know, some connectives and prepositions from Portuguese and other languages
             * languages are never used with the first letter capitalized.
             * This list of exceptions is based on my personal experience, and can be
             * adapted, expanded or even reduced according to the needs of each
             * case.
             */
            $excecoes = array(
                'de', 'di', 'do', 'da', 'dos', 'das', 'dello', 'della',
                'dalla', 'dal', 'del', 'e', 'em', 'na', 'no', 'nas', 'nos', 'van', 'von',
                'y', 'com'
            );

            for ($i = 0; $i < count($partesNome); ++$i) {

                /**
                 * We check each part of the name against the list of exceptions. If there is
                 * correspondence, the part of the name in question is converted to letters
                 * lowercase.
                 */
                foreach ($excecoes as $excecao)
                    if (mb_strtolower($partesNome[$i]) == mb_strtolower($excecao))
                        $partesNome[$i] = $excecao;

                /**
                 * A rare situation in names of people, but quite common in names of
                 * public places, is the presence of Roman numerals, which, as is known,
                 * are used in CAPITAL LETTERS.
                 * In the website
                 * http://htmlcoderhelper.com/how-do-you-match-only-valid-roman-numerals-with-a-regular-expression/,
                 * I found a regular expression to identify the sayings
                 * numerals. With that, just test if there is a match and if
                 * positive, pass the name part to UPPERCASE. So what before
                 * was "Av. Pope John XXIII" changes to "Av. Pope John XXIII".
                 */
                if (mb_ereg_match(self::NN_REGEX_ROMAN_NUMBER, mb_strtoupper($partesNome[$i]))) {
                    $partesNome[$i] = mb_strtoupper($partesNome[$i]);
                }
            }

            /**
             * Finally, just put all the parts of the name back together, putting a
             * space between them.
             */
            return implode(self::NN_SPACE, $partesNome);


        } catch (Exception $e) {
            self::printError($e);
        }
        return '';
	}


    /**
     * Make a string uppercase.
     *
     * @param string The string to convert to uppercase.
     * @return string The converted string.
     */
    public static function toUpper($str) {
        if(function_exists("mb_strtoupper")) {
            return mb_strtoupper($str, mb_detect_encoding($str));
        } else {
            return strtr(strtoupper($str),"àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿý","ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞßÝ");
        }
    }


    /**
     * Make a string lowercase.
     *
     * @param string The string to convert to lowercase.
     * @return string The converted string.
     */
    public static function toLower($str) {
        if(function_exists("mb_strtolower")) {
            return mb_strtolower($str, mb_detect_encoding($str));
        }
        else {
            return strtr(strtolower($str),"ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞßÝ","àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿý");//strtoupper($str);
        }
    }


    /**
     * Remove accents from string
     * @param string	- The string to remove
     * @return string	- The string removed accents
     */
    public static function removeAccents($string) {

        $str = $string;

        $str = preg_replace('/[áàãâä]/u', 'a', $str);
        $str = preg_replace('/[éèêë]/u', 'e', $str);
        $str = preg_replace('/[íìîï]/u', 'i', $str);
        $str = preg_replace('/[óòõôö]/u', 'o', $str);
        $str = preg_replace('/[úùûü]/u', 'u', $str);
        $str = preg_replace('/[ç]/u', 'c', $str);
        $str = preg_replace('/[ñ]/u', 'n', $str);
        $str = preg_replace('/[ÁÀÃÂÄ]/u', 'A', $str);
        $str = preg_replace('/[ÉÈÊË]/u', 'E', $str);
        $str = preg_replace('/[ÍÌÎÏ]/u', 'I', $str);
        $str = preg_replace('/[ÓÒÕÔÖ]/u', 'O', $str);
        $str = preg_replace('/[ÚÙÜÛ]/u', 'U', $str);
        $str = preg_replace('/[Ç]/u', 'C', $str);
        $str = preg_replace('/[Ñ]/u', 'N', $str);
        $str = str_replace('  ', ' ', $str);
        $string = $str;
        return $string;
    }


    /**
     * The function is to replace all characters with "-", except if it is letters or numbers.
     * Also minimizes, leaving the string like an page alias (nickname)
     * @param string The name to be transformed
     * @return string An friendly name ro page
     */
    public static function createAlias($str) {
        $str = JUtils::removeAccents($str);
        $str = preg_replace('/[-]/ui', '', $str);
        $str = str_replace('  ', ' ', $str);
        $str = str_replace(',', '', $str);
        $str = preg_replace('/[^a-z0-9]/i', '-', $str);
        $str = preg_replace('/_+/', '-', $str); // ideia do Bacco :)
        return JUtils::toLower($str);
    }


    /**
     * Return the address of ZIP Code, based on API of www.postmon.com.br
     * @param string $cep The ZIP code,
     * @return bool|string An array in string format white address complete of ZIP
     */
    public static function searchCEP($cep){

        $c = preg_replace('/[^0-9]/is', '', $cep);
        if (!is_numeric($c)) {
            return null;
        }

        $url = "http://api.postmon.com.br/v1/cep/".$c;

        $ch = curl_init();

        // informar URL e outras funções ao CURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FILETIME, true);

        return curl_exec($ch);
    }


    /**
     * Return today date, in format specified.
     * @param string $format If not specified, will return on format dd/mm/yyyy
     * @return false|string
     */
    public static function dateToday($format="d/m/Y") {
        return date($format);
    }

    /**
     * Check if is valide an date on specified format
     * Examples:
     * <code>
     * dateIsValid('22.22.2222', 'mm.dd.yyyy'); // returns false
     * dateIsValid('11/30/2008', 'mm/dd/yyyy'); // returns true
     * dateIsValid('30-01-2008', 'dd-mm-yyyy'); // returns true
     * dateIsValid('2008 01 30', 'yyyy mm dd'); // returns true
     * dateIsValid('28/02/2018'); // returns true
     * </code>
     * @param string $data The date to be checked
     * @param string formato The format
     * @return bool True if is valid or False if not
     * @throws Exception
     */
    public static function dateIsValid($data, $formato = 'dd/mm/yyyy') {
        try {

            switch ($formato) {
                case 'dd-mm-yyyy':
                case 'dd mm yyyy':
                case 'dd.mm.yyyy':
                case 'dd/mm/yyyy':
                    $d = substr($data, 0, 2);
                    $m = substr($data, 3, 2);
                    $a = substr($data, 6, 4);
                    break;
                case 'yyyy/mm/dd':
                case 'yyyy mm dd':
                case 'yyyy.mm.dd':
                case 'yyyy-mm-dd':
                    $a = substr($data, 0, 4);
                    $m = substr($data, 5, 2);
                    $d = substr($data, 8, 2);
                    break;
                case 'yyyy/dd/mm':
                case 'yyyy dd mm':
                case 'yyyy.dd.mm':
                case 'yyyy-dd-mm':
                    $a = substr($data, 0, 4);
                    $d = substr($data, 5, 2);
                    $m = substr($data, 8, 2);
                    break;
                case 'mm-dd-yyyy':
                case 'mm dd yyyy':
                case 'mm.dd.yyyy':
                case 'mm/dd/yyyy':
                    $m = substr($data, 0, 2);
                    $d = substr($data, 3, 2);
                    $a = substr($data, 6, 4);
                    break;
                case 'yyyymmdd':
                    $a = substr($data, 0, 4);
                    $m = substr($data, 4, 2);
                    $d = substr($data, 6, 2);
                    break;
                case 'yyyyddmm':
                    $a = substr($data, 0, 4);
                    $d = substr($data, 4, 2);
                    $m = substr($data, 6, 2);
                    break;
                case 'ddmmyyyy':
                    $d = substr($data, 0, 2);
                    $m = substr($data, 2, 2);
                    $a = substr($data, 4, 4);
                    break;
                case 'mmddyyyy':
                    $m = substr($data, 0, 2);
                    $d = substr($data, 2, 2);
                    $a = substr($data, 4, 4);
                    break;
                default:
                    throw new Exception("Invalid format type");
                    break;
            }

            if (
                strlen(preg_replace( '/[^0-9]/is', '', $d )) != 2 ||
                strlen(preg_replace( '/[^0-9]/is', '', $m )) != 2 ||
                strlen(preg_replace( '/[^0-9]/is', '', $a )) != 4
            ) {
                return false;
            }
            return checkdate($m, $d, $a);

        } catch (Exception $e) {
            self::printError($e);
        }
        return '';
    }

    /**
     * Converts date from DD/MM/YYYY format to MYSQL format YYYY-MM-DD.
     * If already in YYYY-MM-DD format, returns the same date.
     * If come with the time, put it at the end
     * @param string $dta If it comes, it returns the date that came in Mysql format, if it doesn't, it uses today's date
     * @param boolean $returnCurrent If no date is given, return NULL if false, or use today's date if TRUE
     * @return string Returns the specified date (or not) in MySql format
     */
    public static function dateToMysql($dta="", $returnCurrent=false) {

        try {

            if (JUtils::dateIsValid($dta, 'yyyy-mm-dd')) {
                return trim($dta);
            }

            $datas = explode(" ", $dta);
            $data = $datas[0];
            if (count($datas)>1) {
                $arrHr = explode(":", $datas[1]);
                if (count($arrHr)==3) {
                    $hora = $arrHr[0].":".$arrHr[1].":".$arrHr[2];
                } elseif (count($arrHr)==2) {
                    $hora = $arrHr[0].":".$arrHr[1].":00";
                } else {
                    $hora = $datas[1];
                }

            } else {
                $hora = "";
            }

            if (!JUtils::dateIsValid($data, "dd/mm/yyyy") || $data == "") {
                return trim(($returnCurrent ? date("Y-m-d") . " " . $hora : NULL));
            }

            $date_array = explode("/",$data);
            if(count($date_array)!=3) return false;
            return trim($date_array[2] . "-" . $date_array[1] . "-" . $date_array[0] . " " . $hora);

        } catch (Exception $e) {
            self::printError($e);
        }
        return '';
    }

    /**
     * Prints a date in a specified format
     * NOTE: The date MUST come in MYSQL format: YYYY-MM-DD HH:MM:SS, otherwise it returns false
     * Checks if the date is valid, if it prints in the specified format
     * @param string $data If it does, it returns the date that came in timestamp format, if it doesn't, it uses today's date
     * @param string $formato If yes, return in specified format, if not return in format: d/m/Y - H:i:s
     * @param boolean $retDataHoje Date If empty, returns Today's date. Or if the value is false, it returns blank
     * @return string Returns the specified date (or not) in the specified format
     * @throws Exception
     */
    public static function datePrintDate($data="", $formato="d/m/Y - H:i:s", $retDataHoje=true) {

        if ($data == "" || $data == NULL) {
            if ($retDataHoje) {
                $data = date($formato);
            } else { return ''; }
        }

        $dts = explode( " - ", $data);
        if (count($dts) > 1) {
            $hora = " " . $dts[1];
        } else {
            $hora = "";
        }

        //transforma em YYYY-MM-DD, se estiver em DD/MM/YYYY
        if (JUtils::dateIsValid($dts[0], 'dd/mm/yyyy') ) {
            $data = JUtils::dateToMysql($dts[0], true) . $hora;
        }

        $date = new DateTime($data);

        return $date->format($formato);
    }

    /**
     * Imprime a data de hoje no formato do MySQL: Y-m-d H:i:s
     * @return false|string
     */
    public static function dateHourNowMysql() {
        return date("Y-m-d H:i:s");
    }



    /**
     * Imprime a Data em Português no formato: 01 de janeiro de 2000
     * OBS: A data DEVE vir no formato do MYSQL: YYYY-MM-DD HH:MM:SS, senão retorna a mesma data
     * Verifica se a data é válida, se for imprime no formato especificado
     * Formatos aceitos:
     * - %A: dia da semana por extenso.
     * - %d: dia do mês representado com dois digitos.
     * - %B: mês por extenso.
     * - %Y: ano representado com quatro digitos.
     *
     * @param string $data Se vier, retorna a data que veio no formato timestamp, se não vier, usa a data de hoje
     * @param string $formato Se vier, retorna no formato especificado, se não retorna no formato: %d de %B de %Y
     * @param integer $tipo Se minuscula, maiuscula ou capitalizada => MINUSCULA = 0 / MAIUSCULA = 1 CAPITALIZADO = 2;
     * @return string Retorna a data especificada (ou não) no formato especificado
     */
    public static function datePortuguese($data="", $formato="%d de %B de %Y", $tipo=self::LOWERCASE) {

        setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
        $timestamp = strtotime($data);

        if ($formato=='' || $formato==null) {
            $formato = '%d de %B de %Y';
        }

        //se for no formato do mysql, e válido
        if ($timestamp) {

            if ($tipo === self::UPPERCASE) {
                return JUtils::toUpper(utf8_encode(strftime($formato, strtotime($data))));
            } elseif ($tipo === self::CAPITALIZED) {
                return JUtils::capitalizeName(utf8_encode(strftime($formato, strtotime($data))));
            } else {
                return JUtils::toLower(utf8_encode(strftime($formato, strtotime($data))));
            }


        } else {
            if ($data == '') {
                if ($tipo === self::UPPERCASE) {
                    return JUtils::toUpper(utf8_encode(strftime($formato, strtotime('today'))));
                } elseif ($tipo === self::CAPITALIZED) {
                    return JUtils::capitalizeName(utf8_encode(strftime($formato, strtotime('today'))));
                } else {
                    return JUtils::toLower(utf8_encode(strftime($formato, strtotime('today'))));
                }

            } else {
                return $data;
            }
        }
    }


    /**
     * Formata um valor para o formato moeda, tanto brasileiro quanto americano.
     * Podendo ser "R$ 123.456,78" ou somente "123.456,78" ou "123456,78" ou "123.45678"
     * @param string $valor O valor a ser convertido
     * @param integer $casas Quantidade de casas decimais. Padrão 2 casas
     * @param string $moeda O tipo de moeda, se BR ou EN
     * @return string
     */
    public static function converteMoeda($valor, $casas=2, $moeda="BR") {
        $valor     = preg_replace( '/[^0-9]/', '', $valor); // Deixa apenas números
        $novoValor = substr($valor, 0, -$casas); // Separando número para adição do ponto separador de decimais
        $novoValor = ($casas > 0) ? $novoValor.".".substr($valor, strlen($novoValor), strlen($valor)) : $valor;
        return $moeda == "BR" ? number_format($novoValor, $casas, ',', '.') : ($moeda == "EN" ? number_format($novoValor, $casas, '.', ',') : NULL);
    }


    /**
     * Mostrar o valor em formato moeda com o R$
     * @param float $val O valor a ser mostrado em double, float ou int
     * @return string O valor formatado em moeda brasileira, com o R$
     */
    public static function formatoMoeda($val) {
        return "R$ " . number_format($val,2,',','.');
    }


    /**
     * Imprime o nome de uma pessoa com a quantidade de nomes especificados,
     * não considerando os artigos na ultima posicao.
     * Ex1. ImprimeNome('Maria de Fatima de Souza', 2) => Sai: Maria de Fatima
     * Ex2. ImprimeNome('Maria de Fatima de Souza', 4) => Sai: Maria de Fatima de Souza
     *
     * @param string 	- O nome da pessoa passado
     * @param int		- A quantidade no nomes passados
     * @return string 	- O nome já tratado no formato
     */
    public static function imprimeNome($nome, $qts=4) {

        //o ultimo não pode ser um desses nomes
        $arrInvalido = array('de', 'di', 'do', 'da', 'dos', 'das', 'dello', 'della',
            'dalla', 'dal', 'del', 'e', 'em', 'na', 'no', 'nas', 'nos', 'van', 'von',
            'y', '-', 'a');

        $arrNomes = explode(" ", $nome);

        $saida = "";
        $cont=0;
        foreach($arrNomes as $parteNome) {
            $cont++;
            $saida .= $parteNome . " ";

            if ($cont >= $qts) {
                if (in_array($parteNome, $arrInvalido)) {
                    $cont--; //nao sai ainda
                } else {
                    break;
                }
            }

        }
        return trim($saida);
    }


    /**
     * Função para mostrar o CEP no formato com os pontos e traço.
     * Se a qtd de caracteres for menor que 8, retorno o valor passado
     *
     * @param string $cep O número do CEP, sem os pontos e traços
     * @return string O resultado amigável, com os pontos e traços do CEP
     */
    public static function mostrarCEP($cep){

        if (strlen($cep) != 8)
            return $cep;

        $cn = substr($cep, 0, 2) . ".";
        $cn.= substr($cep, 2, 3) . "-";
        $cn.= substr($cep, 5, 3); //final

        return $cn;
    }


    /**
     * Função para mostrar o CNPJ no formato com os pontos. Se a qtd de caracteres for menor
     * que 14, retorno o valor passado
     *
     * @param string  	- O número do cnpj, sem os pontos e traços
     * @return string	- O resultado amigável, com os pontos e traços do CNPJ
     */
    public static function mostrarCPF($cpf){

        if (strlen($cpf) < 11)
            return $cpf;

        $cn = substr($cpf, 0, 3) . ".";
        $cn.= substr($cpf, 3, 3) . ".";
        $cn.= substr($cpf, 6, 3) . "-";
        $cn.= substr($cpf, 9, 2);

        return $cn;
    }


    /**
     * Retorna os dados deserializados
     * @param string $string
     * @return array|string
     */
    public static function unserializaDados($string) {
        $ret = unserialize(($string));
        if ($ret) return $ret;
        else return '';
    }

    /**
     * Serializa os dados
     * @param mixed $array
     * @return string
     */
    public static function serializaDados($array) {
        $ret = (serialize($array));
        if ($ret) return $ret;
        else return null;
    }


    /**
     * Retorna Sim ou Não com a fonte awesome
     * $valor int|boolean 0 ou 1, true ou false
     */
    public static function simNao($valor) {

        if ($valor === 1) {
            return '<i class="text-green fa fa-check"></i>';
        } elseif ($valor === 0) {
            return '<i class="text-red fa fa-close"></i>';
        } else {
            return $valor;
        }
    }


    /**
     * Soma X dias a data de hoje
     * @param int $dias Quantos dias somar
     * @return false|string A data somada aos dias passados, no formato YYYY-MM-DD
     */
    public static function dateSomaDiasHoje($dias=3) {

        if (!$dias)
            $dias = 3;

        if (!is_numeric($dias))
            return date('Y-m-d');

        return date('Y-m-d', strtotime("+".$dias." days"));
    }


    /**
     * Soma X dias à data especificada
     * @param string $data A data a ser somada. DEVERÁ vir no formato YYYY-MM-DD, se nao vier, considera a data de hoje
     * @param int $dias Quantos dias somar
     * @return string A data somada aos dias passados, no formato YYYY-MM-DD
     * @throws Exception
     */
    public static function dateSomaData($data, $dias=3) {
        if ($data == "" || is_null($data)) {
            $data = date('Y-m-d');
        }

        $dt = explode(" ", $data);
        if (count($dt) > 1) {
            $hora = " " . $dt[1];
        } else {
            $hora = "";
        }

        if (!$dias)
            $dias = 3;//GetConfig("DiasFatura");

        if (!is_numeric($dias)) {
            $dias = 3;
        }

        return date('Y-m-d', strtotime("+".$dias." days", strtotime(JUtils::datePrintDate($data, 'd-m-Y')))) . $hora;
    }


    /**
     * Retorna somente os números de uma string
     * @param string $str A string a ser tratada
     * @return string A string retornada, somente números
     */
    public static function somenteNumeros($str) {
        return preg_replace( '/[^0-9]/is', '', $str );
    }

    /***
     * Faz o corte de uma string, com o tamanho definido
     * @param $str
     * @param int $tam
     * @param string $strFinal
     * @return string
     */
    public static function strCorta($str, $tam = 15, $strFinal = '…') {
        if (strlen($str) <= 0) return '';
        $t = ($tam <= 0 ? 15 : $tam);
        if (strlen($str) <= $t) return $str;
        $midle = floor($t / 2);
        return trim(substr($str, 0, $midle)) . $strFinal . trim(substr($str, -($midle - strlen($strFinal))));
    }

    /**
     * Função para colocar a quantidade de zeros a esquerda
     * @param string $valor O valor que vamos colocar os zeros
     * @param int $qts A quantidade de zeros que vamos colocar, se não vier colocamos o padrão que é 4
     * @return string O valor já tratado com os zeros à esquerda
     */
    public static function strZeros($valor, $qts=4) {
        $valor = str_pad($valor, $qts, "0", STR_PAD_LEFT);
        return $valor; //retorna o valor formatado para gravar no banco
    }



    /**
     * Valida CPF
     *
     * @author Luiz Otávio Miranda <contato@todoespacoonline.com/w>
     * @param string $cpf O CPF com ou sem pontos e traço
     * @return bool True para CPF correto - False para CPF incorreto
     *
     */
    public static function validaCPF($cpf) {

        /**
         * Multiplica dígitos vezes posições
         *
         * @param string $digitos Os digitos desejados
         * @param int $posicoes A posição que vai iniciar a regressão
         * @param int $soma_digitos A soma das multiplicações entre posições e dígitos
         * @return int Os dígitos enviados concatenados com o último dígito
         *
         */
        if (!function_exists('calc_digitos_posicoes')) {
            return false;
        }

        // Verifica se o CPF foi enviado
        if ( !$cpf ) {
            return false;
        }

        // Remove tudo que não é número do CPF
        // Ex.: 025.462.884-23 = 02546288423
        $cpf = preg_replace( '/[^0-9]/is', '', $cpf );

        // Verifica se o CPF tem 11 caracteres
        // Ex.: 02546288423 = 11 números
        if ( strlen( $cpf ) != 11 ) {
            return false;
        }

        if ( $cpf == '11111111111' || $cpf == '22222222222' ||
            $cpf == '33333333333' || $cpf == '44444444444' ||
            $cpf == '55555555555' || $cpf == '66666666666' ||
            $cpf == '77777777777' || $cpf == '88888888888' ||
            $cpf == '99999999999' || $cpf == '00000000000'  ) {
            return false;
        }


        // Captura os 9 primeiros dígitos do CPF
        // Ex.: 02546288423 = 025462884
        $digitos = substr($cpf, 0, 9);

        // Faz o cálculo dos 9 primeiros dígitos do CPF para obter o primeiro dígito
        $novo_cpf = calc_digitos_posicoes( $digitos );

        // Faz o cálculo dos 10 dígitos do CPF para obter o último dígito
        $novo_cpf = calc_digitos_posicoes( $novo_cpf, 11 );

        //debug("$cpf <> $novo_cpf",0);
        //debug($this->$campo);


        // Verifica se o novo CPF gerado é idêntico ao CPF enviado
        if ( $novo_cpf === $cpf ) {
            // CPF válido
            return true;
        } else {
            // CPF inválido
            return false;
        }


        function calc_digitos_posicoes( $digitos, $posicoes = 10, $soma_digitos = 0 ) {
            // Faz a soma dos dígitos com a posição
            // Ex. para 10 posições:
            //   0	2	5	4	6	2	8	8   4
            // x10   x9   x8   x7   x6   x5   x4   x3  x2
            //   0 + 18 + 40 + 28 + 36 + 10 + 32 + 24 + 8 = 196
            for ( $i = 0; $i < strlen( $digitos ); $i++  ) {
                $soma_digitos = $soma_digitos + ( $digitos[$i] * $posicoes );
                $posicoes--;
            }

            // Captura o resto da divisão entre $soma_digitos dividido por 11
            // Ex.: 196 % 11 = 9
            $soma_digitos = $soma_digitos % 11;

            // Verifica se $soma_digitos é menor que 2
            if ( $soma_digitos < 2 ) {
                // $soma_digitos agora será zero
                $soma_digitos = 0;
            } else {
                // Se for maior que 2, o resultado é 11 menos $soma_digitos
                // Ex.: 11 - 9 = 2
                // Nosso dígito procurado é 2
                $soma_digitos = 11 - $soma_digitos;
            }

            // Concatena mais um dígito aos primeiro nove dígitos
            // Ex.: 025462884 + 2 = 0254628842
            $cpf = $digitos . $soma_digitos;

            // Retorna
            return $cpf;
        }
    }


    /**
     * Com esta função é possível realizar uma validação praticamente completa do endereço de e-mail.
     * Além de verificar se a escrita do e-mail está correta, ele também faz uma verificação se o domínio utilizado no endereço realmente existe.
     * @param string $email O email a ser verificado
     * @return boolean
     */
    public static function validaEmail($email){
        //verifica se e-mail esta no formato correto de escrita
        if (!preg_match('^([a-zA-Z0-9.-_])*([@])([a-z0-9]).([a-z]{2,3})^',$email)){
            return false;
        }
        else{
            //Valida o dominio
            $dominio=explode('@',$email);
            if(!checkdnsrr($dominio[1],'A')){
                return false;
            } else{return true;} // Retorno true para indicar que o e-mail é valido
        }
    }


    /**
     * Valida uma URL, se tem a aparência válida ou não
     * @param string $url A string URL que vai ser verificada
     * @return boolean O retorno
     */
    public static function validaURL($url) {

        if (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url)) {
            return true;
        } else {
            return false;
        }
    }
}
