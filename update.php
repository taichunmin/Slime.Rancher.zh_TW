<?php

if($argc != 3) {
    die("Usage: {$argv[0]} englishFile translateFile");
}

class Parser {
    public function splitFile($filename) {
        $str = file_get_contents($filename);
        if(false === $str)
            throw new Exception("Failed to open '{$filename}'");
        $str = str_replace("\r\n", "\n", $str);
        // echo $str;
        $lines = preg_split('/(?<=[^\\\\])\n/', $str);
        return $lines;
    }

    public function toKeyVal($lines) {
        $tmp = [];
        foreach($lines as $line)
            if(is_array($match = $this->isI18nLine($line))) {
                $tmp[ $match[1] ] = $match[2];
            }
        return $tmp;
    }

    public function isI18nLine($line) {
        return preg_match('/^([\w.]+) ?= ?(.*)$/us', $line, $match) ? $match : false;
    }
}

$parser = new Parser;

$twArr = $parser->toKeyVal( $parser->splitFile($argv[2]) );
$enLines = $parser->splitFile($argv[1]);

$newLines = [];
foreach( $enLines as $enLine ) {
    if(is_array($match = $parser->isI18nLine($enLine))) {
        $newLines[] = "{$match[1]} = ".(empty($twArr[ $match[1] ]) ? $match[2] : $twArr[ $match[1] ]);
    } else $newLines[] = $enLine;
}
$newStr = str_replace("\n", "\r\n", implode("\n", $newLines));
file_put_contents($argv[2], $newStr);
