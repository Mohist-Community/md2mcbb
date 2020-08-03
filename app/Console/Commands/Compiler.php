<?php

namespace App\Console\Commands;

use App\Parser\Parser;
use App\Parser\ParserShell;
use Illuminate\Console\Command;
use Parsedown;
use Phar;
use Symfony\Component\Console\Input\InputArgument;

class Compiler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compiler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make md2mcbb into a phar.';

    /** @var Phar $phar */
    private $phar;
    private $files;
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->files=[];
        @unlink(base_path('md2mcbb.phar'));
        $this->phar = new Phar(base_path('md2mcbb.phar'), 0, 'md2mcbb.phar');
        $this->addClass(Parser::class);
        $this->addClass(ParserShell::class);
        $this->addClass(Parsedown::class);
        $this->phar->setStub($this->getStub());
        $this->fileChmod($this->phar->getPath(), '-rwxrwxr-x');
        rename(base_path('md2mcbb.phar'),base_path('md2mcbb'));
    }
    private function addClass($class){
        $this->phar->addFile((new \ReflectionClass($class))->getFileName(),md5($class));
    }
    private function addPath($path)
    {
        if(is_file($path)){
            $this->files[]=basename($path);
            $this->phar->addFile($path, basename($path));
        }elseif (is_dir($path)) {
            $this->phar->addEmptyDir(substr($path, strlen(base_path())));
            $files = scandir($path);
            foreach ($files as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $this->addPath($path . '/' . $file);
            }
        }else{
            throw new \Exception('No such file or directory: '.$path);
        }
    }
    private function getStub()
    {
        $stub = '#!/usr/bin/env php';
        $stub .= "\n";
        $stub .= '<?php ';
        $stub .= 'Phar::mapPhar(\'md2mcbb.phar\');';
        $stub .= 'function __autoload($className){';
        $stub .= 'require_once \'phar://md2mcbb.phar/\'.md5($className);';
        $stub .= 'return true;';
        $stub .= '}';
        $stub .= 'return \\App\Parser\\ParserShell::init($argv);';
        $stub .= '__HALT_COMPILER();';
        return $stub;
    }
    private function fileChmod($file, $permissions)
    {
        $mode = 0;

        if ($permissions[1] == 'r') {
            $mode += 0400;
        }
        if ($permissions[2] == 'w') {
            $mode += 0200;
        }
        if ($permissions[3] == 'x') {
            $mode += 0100;
        } elseif ($permissions[3] == 's') {
            $mode += 04100;
        } elseif ($permissions[3] == 'S') {
            $mode += 04000;
        }

        if ($permissions[4] == 'r') {
            $mode += 040;
        }
        if ($permissions[5] == 'w') {
            $mode += 020;
        }
        if ($permissions[6] == 'x') {
            $mode += 010;
        } elseif ($permissions[6] == 's') {
            $mode += 02010;
        } elseif ($permissions[6] == 'S') {
            $mode += 02000;
        }

        if ($permissions[7] == 'r') {
            $mode += 04;
        }
        if ($permissions[8] == 'w') {
            $mode += 02;
        }
        if ($permissions[9] == 'x') {
            $mode += 01;
        } elseif ($permissions[9] == 't') {
            $mode += 01001;
        } elseif ($permissions[9] == 'T') {
            $mode += 01000;
        }

        return chmod($file, $mode);
    }
}
