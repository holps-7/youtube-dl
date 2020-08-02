<?php

namespace YouTube;

class SignatureDecoder
{
    /**
     * Throws both \Exception and \Error
     * https://www.php.net/manual/en/language.errors.php7.php
     *
     * @param $signature
     * @param $js_code
     * @return string
     */
    public function decode($signature, $js_code)
    {
        $func_name = $this->parseFunctionName($js_code);
        $instructions = (array)$this->parseFunctionCode($func_name, $js_code);
        foreach ($instructions as $opt) {
            $command = $opt[0];
            $value = $opt[1];
            if ($command == 'swap') {
                $temp = $signature[0];
                $signature[0] = $signature[$value % strlen($signature)];
                $signature[$value] = $temp;
            } elseif ($command == 'splice') {
                $signature = substr($signature, $value);
            } elseif ($command == 'reverse') {
                $signature = strrev($signature);
            }
        }
        return trim($signature);
    }

    public function parseFunctionName($js_code)
    {
        if (preg_match('@,\s*encodeURIComponent\((\w{2})@is', $js_code, $matches)) {
            $func_name = $matches[1];
            $func_name = preg_quote($func_name);
            return $func_name;
        } else if (preg_match('@\b([a-zA-Z0-9$]{2})\s*=\s*function\(\s*a\s*\)\s*{\s*a\s*=\s*a\.split\(\s*""\s*\)@is', $js_code, $matches)) {
            return preg_quote($matches[1]);
        }
        return null;
    }

    public function parseFunctionCode($func_name, $player_htmlz)
    {
        if (preg_match('/' . $func_name . '=function\([a-z]+\){(.*?)}/', $player_htmlz, $matches)) {
            $js_code = $matches[1];
            if (preg_match_all('/([a-z0-9]{2})\.([a-z0-9]{2})\([^,]+,(\d+)\)/i', $js_code, $matches) != false) {
                $obj_list = $matches[1];
                $func_list = $matches[2];
                preg_match_all('/(' . implode('|', $func_list) . '):function(.*?)\}/m', $player_htmlz, $matches2, PREG_SET_ORDER);
                $functions = array();
                foreach ($matches2 as $m) {
                    if (strpos($m[2], 'splice') !== false) {
                        $functions[$m[1]] = 'splice';
                    } elseif (strpos($m[2], 'a.length') !== false) {
                        $functions[$m[1]] = 'swap';
                    } elseif (strpos($m[2], 'reverse') !== false) {
                        $functions[$m[1]] = 'reverse';
                    }
                }
                $instructions = array();
                foreach ($matches[2] as $index => $name) {
                    $instructions[] = array($functions[$name], $matches[3][$index]);
                }
                return $instructions;
            }
        }
        return null;
    }
}
