<?php

namespace Smart\Core\Doctrine;

use Sinergi\Config\Collection as Config;

class AutoloadCollisionAvoider
{
    /**
     * @param Config $config
     */
    public function avoidAutoloadCollision(Config $config)
    {
        foreach ($config->get('doctrine.connections') as $config) {
            if (isset($config['paths'])) {
                foreach ($config['paths'] as $path) {
                    $this->loadPath($path);
                }
            }
        }
    }

    /**
     * @param string $path
     */
    private function loadPath($path)
    {
        $path = realpath($path);
        if (file_exists($path)) {
            $files = $this->scanDir($path);
            foreach ($files as $file) {
                $className = $this->getClassName($file);
                if (!class_exists($className) && !interface_exists($className)) {
                    require_once $file;
                } else {
                    if (!$this->isFileIncluded($file)) {
                        $this->requireFileNoCollision($file);
                    }
                }
            }
        }
    }

    /**
     * @param string $file
     */
    private function requireFileNoCollision($file)
    {
        $content = file_get_contents($file);
        file_put_contents($file, '');
        require $file;
        file_put_contents($file, $content);
    }

    /**
     * @param string $file
     * @return bool
     */
    private function isFileIncluded($file)
    {
        $includedFiles = get_included_files();
        if (!in_array($file, $includedFiles)) {
            return false;
        }
        return true;
    }

    /**
     * @param string $file
     * @return string
     */
    private function getClassName($file)
    {
        $fp = fopen($file, 'r');
        $class = $namespace = $buffer = '';
        $i = 0;
        while (!$class) {
            if (feof($fp)) break;

            $buffer .= fread($fp, 512);
            $tokens = @token_get_all($buffer);

            if (strpos($buffer, '{') === false) continue;

            for (;$i<count($tokens);$i++) {
                if ($tokens[$i][0] === T_NAMESPACE) {
                    for ($j=$i+1;$j<count($tokens); $j++) {
                        if ($tokens[$j][0] === T_STRING) {
                            $namespace .= '\\'.$tokens[$j][1];
                        } else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }
                if ($tokens[$i][0] === T_CLASS || $tokens[$i][0] === T_INTERFACE) {
                    for ($j=$i+1;$j<count($tokens);$j++) {
                        if ($tokens[$j] === '{' && isset($tokens[$i+2][1])) {
                            $class = $tokens[$i+2][1];
                        }
                    }
                }
            }
        }

        if (!empty($namespace)) {
            return $namespace . "\\" . $class;
        } else {
            return $class;
        }
    }

    /**
     * @param string $dir
     * @return array
     */
    private function scanDir($dir)
    {
        $files = [];
        foreach (scandir($dir) as $file) {
            if ($file !== '.' && $file !== '..') {
                if (substr($file, -4) === '.php') {
                    $files[] = $dir . DIRECTORY_SEPARATOR . $file;
                } elseif (is_dir($dir . DIRECTORY_SEPARATOR . $file)) {
                    $childs = $this->scanDir($dir . DIRECTORY_SEPARATOR . $file);
                    $files = array_merge($files, $childs);
                }
            }
        }
        return $files;
    }
}
