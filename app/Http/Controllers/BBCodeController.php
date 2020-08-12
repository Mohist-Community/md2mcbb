<?php

namespace App\Http\Controllers;

use App\Parser\Extensions\TestParser;
use Illuminate\Http\Request;
use Validator;

class BBCodeController extends Controller
{
    public function toBBCode(Request $request) {
        $validation = Validator::make($request->all(), [
            'markdown' => 'required'
        ]);

        if ($validation->fails()) {
            return response($validation->errors()->first());
        }

        $parser = new TestParser();
        $parser->setSafeMode(true);
        $bbcode = $parser->text($validation->validated()['markdown']);
        return response($bbcode);
    }
}
