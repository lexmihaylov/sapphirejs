<?php
require 'libs/JsCompiler.php';
require 'libs/CssCompiler.php';

function usage() {
    echo "USAGE:\n".
         " $ ./compile.phar [js|css] input [output]\n";
    echo "EXAMPLES:\n".
         " $ ./compile.phar js /path/project.js\n".
         " $ ./compile.phar css /path/style.css\n".
         " $ ./compile.phar js /path/project.js /path/project.min.js\n".
         " $ ./compile.phar css /path/style.css /path/style.min.css\n";
    exit(0);
}

function compileJs($input, &$output = null) {
    $compiler = null;
    if(is_null($output)) {
        $compiler = new JsCompiler($input);
    } else {
        $compiler = new JsCompiler($input, $output);
    }
    
    $compiler->compile();
}

function compileCss($input, &$output = null) {
    $compiler = null;
    if(is_null($output)) {
        $compiler = new CssCompiler($input);
    } else {
        $compiler = new CssCompiler($input, $output);
    }
    
    $compiler->compile();
}

if(isset($argv[1]) && isset($argv[2])) {
    switch ($argv[1]) {
        case 'js':
            compileJs($argv[2], $argv[3]);
            break;
        case 'css':
            compileCss($argv[2], $argv[3]);
            break;
        default :
            usage();
            break;
    }
} else {
    usage();
}
?>
