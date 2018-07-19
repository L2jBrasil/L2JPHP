<?php
/**
 * Created by PhpStorm.
 * User: Leonan
 * Date: 19/07/2018
 * Time: 13:28
 */

namespace L2jBrasil\L2JPHP\Utils;


class FileSystem
{

    //Tenta montar o caminho do diretório absoluto de forma normalizada
    public static function normalizePath($path)
    {
        $parts = array(); // Array to build a new path from the good parts
        $path = str_replace('\\', DIRECTORY_SEPARATOR, $path); // Replace backslashes with forwardslashes
        $path = preg_replace('/\/+/', DIRECTORY_SEPARATOR, $path); // Combine multiple slashes into a single slash
        $segments = explode(DIRECTORY_SEPARATOR, $path); // Collect path segments
        foreach ($segments as $segment) {
            if ($segment != '.') {
                $test = array_pop($parts);
                if (is_null($test))
                    $parts[] = $segment;
                else if ($segment == '..') {
                    if ($test == '..')
                        $parts[] = $test;

                    if ($test == '..' || $test == '')
                        $parts[] = $segment;
                } else {
                    $parts[] = $test;
                    $parts[] = $segment;
                }
            }
        }
        return implode(DIRECTORY_SEPARATOR, $parts);
    }

    //Monta um caminho de arquivos de acordo com o caminho do array
    public static function mountDir($path, $separator = DIRECTORY_SEPARATOR)
    {
        $b = "";
        for ($index = 0; $index < count($path); $index++) {
            $b .= $path[$index] . $separator;
        }
        return $b;
    }


    //Monta o recuo de diretório com anterior corretamente dado a quantidade
    public static function backDir($i = 1, $lastseparator = true)
    {
        $b = "";
        for ($index = 0; $index < $i; $index++) {
            $b .= DIRECTORY_SEPARATOR . "..";
        }
        return ($lastseparator) ? $b . DIRECTORY_SEPARATOR : $b;
    }

}