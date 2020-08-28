<?php

namespace App\Parser\Extensions;

use App\Parser\Parser;
use SplStack;

/**
 * Class SlimParser
 * @package App\Parser\Extensions
 * @author 爱心魔王FHC
 * @link https://www.mcbbs.net/forum.php?mod=redirect&goto=findpost&ptid=1087617&pid=19176439
 */
class SlimParser extends Parser
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
            $op .= '[/color][/size][/font][/td][/tr][/table]';
            $this->stack->pop();
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
                $Block = [
                    'markup' => $this->popHeader(0)
                        . '[table=530,#d5b4ff][tr][td][align=center][b][color=Purple][font=微软雅黑][size=4]'
                        . $this->line($text)
                        . '[/size][/font][/color][/b][/align][/td][/tr][/table]'
                        . '[table=530,#ecddff][tr][td][font=微软雅黑][size=3][color=DarkOrchid]'
                ];
                $this->stack->push($level);
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
        $this->stack->push(1);
        return '[align=center][table=550,#a024b5][tr][td][table=540,#d35de8][tr][td]'
            . '[table=530,#ecddff][tr][td][font=微软雅黑][size=3][color=DarkOrchid]'
            . parent::text($text)
            . $this->popHeader(0)
            . '[/td][/tr][/table][/td][/tr][/table][/align]';
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
