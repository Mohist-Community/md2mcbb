<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use League\CommonMark\DocParser;
use phpDocumentor\Reflection\DocBlockFactory;
use PhpParser\Comment\Doc;

class BBCodeController extends Controller
{
    public function index(Request $request){
        $files=File::files(app()->basePath('app/Parser/Extensions'));
        foreach($files as &$file){
            $file=$file->getFilenameWithoutExtension();
        }
        return view('welcome')
            ->with('parsers',$files);
    }
    public function input(Request $request,$parser){
        $class='\\App\\Parser\\Extensions\\'.$parser;
        if(!class_exists($class)){
            return 'No such parser.';
        }
        $doc=explode("\n",(new \ReflectionClass($class))->getDocComment());
        $lines=[];
        foreach ($doc as $line){
            if(strlen($line)>=3
                && substr($line,0,3)==' * '){
                $lines[]=substr($line,3);
            }elseif ($line == ' *'){
                $lines[]="\n";
            }
        }
        $describe=implode("\n",$lines);
        return view('input')
            ->with('parser',$parser)
            ->with('describe',$describe);
    }
    public function parser(Request $request) {
        $validation = Validator::make($request->all(), [
            'markdown' => 'required',
            'parser' => 'required',
        ]);

        if ($validation->fails()) {
            return $validation->errors()->first();
        }
        $input=$validation->validated();
        $class='\\App\\Parser\\Extensions\\'.$input['parser'];
        if(!class_exists($class)){
            return 'No such parser.';
        }
        $parser = new $class();
        $bbcode = $parser->text($input['markdown']);
        return response($bbcode);
    }
}
