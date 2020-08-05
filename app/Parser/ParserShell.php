<?php

namespace App\Parser;

use App\Parser\Extensions\TestParser;

class ParserShell
{
    public static function init($argv){
        ob_start();
        $input = isset($argv[1]) ? $argv[1] : '-';
        $input = $input == '-' ? 'php://stdin' : $input;
        $output = isset($argv[2]) ? $argv[2] : '-';
        $output = $output == '-' ? 'php://stdout' : $output;
        file_put_contents($output,(new TestParser())->parse(file_get_contents($input)));
        file_put_contents('php://stderr',ob_get_clean());
        return 0;
    }
}
