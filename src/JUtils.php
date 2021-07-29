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


	/**
	 * Normaliza o nome próprio dado, aplicando a capitalização correta de acordo
     * com as regras e exceções definidas no código.
     * POR UMA DECISÃO DE PROJETO, FORAM UTILIZADAS FUNÇÕES MULTIBYTE (MB_) SEMPRE
	 * QUE POSSÍVEL, PARA GARANTIR SUA USABILIDADE EM STRINGS UNICODE.
	 * @param string $name O nome a ser normalizado
	 * @return string O nome devidamente normalizado
	 */
    public static function capitalizeName($name)
    {

        try {


            /**
             * A primeira tarefa da normalização é lidar com partes do nome que
             * porventura estejam abreviadas,considerando-se para tanto a existência de
             * pontos finais (p. ex. JOÃO A. DA SILVA, onde "A." é uma parte abreviada).
             * Dado que mais à frente dividiremos o nome em partes tomando em
             * consideração o caracter de espaço (" "), precisamos garantir que haja um
             * espaço após o ponto. Fazemos isso substituindo todas as ocorrências do
             * ponto por uma sequência de ponto e espaço.
             */
            $nome = mb_ereg_replace(self::NN_POINT, self::NN_SPACE_POINT, $name);

            /**
             * O procedimento anterior, ou mesmo a digitação errônea, podem ter
             * introduzido espaços múltiplos entre as partes do nome, o que é totalmente
             * indesejado. Para corrigir essa questão, utilizamos uma substituição
             * baseada em expressão regular, a qual trocará todas as ocorrências de
             * espaços múltiplos por espaços simples.
             */
            $nome = mb_ereg_replace(self::NN_REGEX_MULTIPLES_SPACES, self::NN_SPACE, $nome);

            /**
             * Isso feito, podemos fazer a capitalização "bruta", deixando cada parte do
             * nome com a primeira letra maiúscula e as demais minúsculas. Assim,
             * JOÃO DA SILVA => João Da Silva.
             */
            $nome = mb_convert_case($nome, MB_CASE_TITLE, mb_detect_encoding($nome));

            /**
             * Nesse ponto, dividimos o nome em partes, para trabalhar com cada uma
             * delas separadamente.
             */
            $partesNome = mb_split(self::NN_SPACE, $nome);

            /**
             * A seguir, são definidas as exceções à regra de capitalização. Como
             * sabemos, alguns conectivos e preposições da língua portuguesa e de outras
             * línguas jamais são utilizadas com a primeira letra maiúscula.
             * Essa lista de exceções baseia-se na minha experiência pessoal, e pode ser
             * adaptada, expandida ou mesmo reduzida conforme as necessidades de cada
             * caso.
             */
            $excecoes = array(
                'de', 'di', 'do', 'da', 'dos', 'das', 'dello', 'della',
                'dalla', 'dal', 'del', 'e', 'em', 'na', 'no', 'nas', 'nos', 'van', 'von',
                'y', 'com'
            );

            for ($i = 0; $i < count($partesNome); ++$i) {

                /**
                 * Verificamos cada parte do nome contra a lista de exceções. Caso haja
                 * correspondência, a parte do nome em questão é convertida para letras
                 * minúsculas.
                 */
                foreach ($excecoes as $excecao)
                    if (mb_strtolower($partesNome[$i]) == mb_strtolower($excecao))
                        $partesNome[$i] = $excecao;

                /**
                 * Uma situação rara em nomes de pessoas, mas bastante comum em nomes de
                 * logradouros, é a presença de numerais romanos, os quais, como é sabido,
                 * são utilizados em letras MAIÚSCULAS.
                 * No site
                 * http://htmlcoderhelper.com/how-do-you-match-only-valid-roman-numerals-with-a-regular-expression/,
                 * encontrei uma expressão regular para a identificação dos ditos
                 * numerais. Com isso, basta testar se há uma correspondência e, em caso
                 * positivo, passar a parte do nome para MAIÚSCULAS. Assim, o que antes
                 * era "Av. Papa João Xxiii" passa para "Av. Papa João XXIII".
                 */
                if (mb_ereg_match(self::NN_REGEX_ROMAN_NUMBER, mb_strtoupper($partesNome[$i]))) {
                    $partesNome[$i] = mb_strtoupper($partesNome[$i]);
                }
            }

            /**
             * Finalmente, basta juntar novamente todas as partes do nome, colocando um
             * espaço entre elas.
             */
            return implode(self::NN_SPACE, $partesNome);


        } catch (Exception $e) {

            self::printError($e);

        }


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

            //se o dia ou mes nao for 2 numeros ou o ano nao for 4 numeros, é falso, não está no formato especificado
            if (
                strlen(preg_replace( '/[^0-9]/is', '', $d )) != 2 ||
                strlen(preg_replace( '/[^0-9]/is', '', $m )) != 2 ||
                strlen(preg_replace( '/[^0-9]/is', '', $a )) != 4
            ) {
                return false;
            }
            return checkdate($m, $d, $a);

        } catch (\Exception $e) {
            self::printError($e);
        }
    }

    /**
     * Converte a data do formato DD/MM/YYYY para o formato do MYSQL YYYY-MM-DD.
     * Se já estiver no formato YYYY-MM-DD, retorna a mesma data.
     * Se vier com a hora coloca ela no final
     * @param string $dta opcional - se vier, retorna a data que veio no formato do Mysql, se não vier, usa a data de hoje
     * @param boolean $retornaAtual boolean opcional - se não vier a data, retorna NULL se for false, ou usa a data de hoje se for TRUE
     * @return timestamp 	- retorna a data especificada (ou não) no formato do MySql
     */
    public static function ConverteDataParaMysql($dta="", $retornaAtual=false) {

        try {

            //se já estiver no formato do mysql
            if (Utils::isDataValida($dta, 'yyyy-mm-dd')) {
                return trim($dta);
            }


            //debug($data . " dd/mm/yyyy");
            //tem hora na data??
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

            //debug($data);
            //debug($dta);

            //debug("$data <> $dta",1);
            //debug(Utils::isDataValida($data, "dd/mm/yyyy"),0);

            //debug($hora);
            if (!Utils::isDataValida($data, "dd/mm/yyyy") || $data == "") {
                return trim(($retornaAtual ? date("Y-m-d") . " " . $hora : NULL));
            }

            //se for passado uma data inválida, assume a data atual
            //if(isDataValida($data,"dd/mm/yyyy"))
            //$data = date("d/m/Y");
            //debug("==>".$data,0);


            $date_array = explode("/",$data);
            if(count($date_array)!=3) return false;
            return trim($date_array[2] . "-" . $date_array[1] . "-" . $date_array[0] . " " . $hora);
            //return strtotime($data);


        } catch (RuntimeException $e) {

            die($e->getMessage());

        }
    }
}
