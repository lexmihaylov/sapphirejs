<?php
/**
 * Description of Compiler
 *
 * @author alexander
 */
class JsCompiler {
    private $__output = null;
    private $__loadedDeps = array();
    private $__outputFile = null;
    private $__compressorBin = null;
    
    const __depExp = '/sapp.require\((\'|")([^(\'|")]+)(\'|")\)(;|)/s';
    
    public function __construct($filePath, $outputFilePath = null) {
        
        if(!preg_match('/\.js$/', $filePath)) {
            throw new ErrorException('The input file is not a javascript file: '.basename($filePath));
        }
        
        echo "Entering: ".dirname($filePath)."\n";
        chdir(dirname($filePath));
        
        $this->__compressorBin = 'java -jar '.dirname(Phar::running(false)).'/bin/compressor.jar';
        
        $this->__loadFile(basename($filePath));
        
        if(is_null($outputFilePath)) {
            $this->__outputFile = preg_replace('/\.js$/s', '.min.js', basename($filePath));
        } else {
            $this->__outputFile = $outputFilePath;
        }        
    }
    
    public function compile() {
        $this->__loadCore();
        
        echo "Including dependencies:\n";
        
        $this->__output = $this->__includeDependencies($this->__output);
        
        echo "Compiling: \n";
        
        file_put_contents('.tmp.js', $this->__output);
        
        exec($this->__compressorBin." -v --type js .tmp.js -o $this->__outputFile");
        
        unlink('.tmp.js');
        
        echo "\nDone.\n";
    }


    private function __loadCore() {
        $this->__output = file_get_contents('sapphire/sapp.core.js') . PHP_EOL .
                          $this->__output;
    }
    
    private function __loadFile($filePath) {
        $this->__output = file_get_contents($filePath) . PHP_EOL;
    }
    
    private function __includeDependencies($output) {
        preg_match_all(self::__depExp, $output, $dep);
        
        if(isset($dep[2]) && count($dep[2]) > 0) {
            foreach($dep[2] as $dependency) {
                $output = $this->__loadDependency($dependency, $output);
            }
        }
        
        return $output;
    }
    
    private function __loadDependency($filePath, $output) {
        $dep = $filePath;
        
        if(!preg_match('/\.js$/s', $dep)) {
            $dep .= '.js';
        }
        
        if(!file_exists($dep)) {
            throw new ErrorException('Compiler cannot find dependency file: '.$dep);
        }
        
        $replaceExp = '/sapp.require\((\'|")'.  preg_quote($filePath, '/').'(\'|")\)(;|)/s';
        preg_match($replaceExp, $output, $requireStatement);
        if(isset($requireStatement[0])) {
            $requireStatement = $requireStatement[0];

            if($this->__isDepLoaded($dep)) {
                $output = str_replace($requireStatement, '', $output);;
            } else {

                $dep = file_get_contents($dep);

                if(preg_match(self::__depExp, $dep)) {
                    $dep = $this->__includeDependencies($dep);
                }

                $output = str_replace($requireStatement, "\n$dep\n", $output);

                echo "Loaded: $filePath\n";
            }
        }
        return $output;
    }
    
    private function __isDepLoaded($dependency) {
        if(in_array($dependency, $this->__loadedDeps)) {
            return true;
        }
        
        $this->__loadedDeps[] = $dependency;
        
        return false;
    }
}
?>
