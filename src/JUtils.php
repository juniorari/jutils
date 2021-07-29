<?php

/**
 * Class conteiner for methods and lot of stuff utilities
 *
 * @required:
 *  - ext-mbstring enabled on php.ini
 *
 * @author Ari Junio(r) 
 *
 */

 namespace JuniorAri\Utils;


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

    private static function printError(\Exception $e) {
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
    public function capitalizeName($name)
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


        } catch (\Exception $e) {

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

}
