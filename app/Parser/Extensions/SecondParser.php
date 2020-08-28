<?php

namespace App\Parser\Extensions;

use App\Parser\Parser;
use SplStack;

/**
 * Class SecondParser
 * @package App\Parser\Extensions
 * @author 余音是只猫
 * @link https://www.mcbbs.net/thread-934065-1-1.html
 */
class SecondParser extends Parser
{
    /** @var SplStack $stack */
    protected $stack;
    public function __construct()
    {
        $this->stack=new SplStack();
    }
    protected function popHeader($level){
        $op = '';
        while(!$this->stack->isEmpty() && $this->stack->top() >= $level){
            $op .= '[/color][/td][/tr][/table][/align]'."\n";
        }
        return $op;
    }

    protected function blockHeader($Line)
    {
        if (isset($Line['text'][1]))
        {
            $level = 1;

            while (isset($Line['text'][$level]) and $Line['text'][$level] === '#')
            {
                $level ++;
            }

            if ($level > 6)
            {
                return;
            }

            $text = trim($Line['text'], '# ');
            if($level <= 2) {
                switch ($level){
                    case 1:
                        $Block = [
                            'markup' => $this->popHeader($level)
                                . '[align=center][font=Black][size=4][color=#000000]'
                                . $this->line($text)
                                . '[/color][/size][/font][/align]'
                        ];
                        break;
                    case 2:
                        $Block = [
                            'markup' => $this->popHeader($level)
                                . '[align=center][table=95%,DarkGray][tr][td][size=3][color=#000000]'
                                . $this->line($text)
                                . '[/color][/size][/td][/tr][/table][/align]'
                                . '[align=center][table=95%,Gainsboro][tr][td][color=#000000]'
                        ];
                        $this->stack->push($level);
                }
            }else{
                $Block = [
                    'element' => [
                        'name' => 'align',
                        'text' => [
                            'name' => 'size',
                            'text' => [
                                'name' => 'b',
                                'text' => $text.($level <= 2 ? '' : "\n"),
                                'handler' => 'line',
                            ],
                            'handler' => 'element',
                            'data' => 8-$level,
                        ],
                        'handler' => 'element',
                        'data' => 'left',
                    ],
                ];
            }
            return $Block;
        }
    }

    public function text($text)
    {
        return '[align=center][b][size=2][font=Tahoma][table=90%,DimGray][tr][td]'
            . parent::text($text)
            . $this->popHeader(0)
            . '[/td][/tr][/table][/font][/size][/b][/align]';
    }

    protected function elementSize(array $Element,$nonNestables = null,$parentElement = null){
        switch (@$Element['data']){
            case 7:
                $Element['data'] = '32px';
                break;
            case 6:
                $Element['data'] = '24px';
                break;
            case 5:
                $Element['data'] = '20px';
                break;
            default:
                $Element['data'] = '16px';
        }
        $Element['disableOverride']=true;
        return parent::element($Element,$nonNestables,$parentElement);
    }
}
