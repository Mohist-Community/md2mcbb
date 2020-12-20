<?php

namespace App\Parser\Extensions;

use App\Parser\Parser;
use SplStack;

/**
 * @author 余音是只猫
 *
 * @link https://www.mcbbs.net/thread-934065-1-1.html
 */
class FourthParser extends Parser
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
            switch ($this->stack->pop()){
                case 1:
                    $op .= '[/align][/td][/tr][/table]'."\n";
                    break;
                case 2:
                    $op .= '[/b][/align][/td][/tr][/table][/align][/td][/tr][/table]'."\n";
            }
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
                                . '[table][tr=rgb(255,160,122)][td][align=left]'
                                . '[table][tr=rgb(255,140,0)][td][align=center][size=5][color=#000000][b]'
                                . $this->line($text)
                                . '[/b][/color][/size][/align][/td][/tr][/table][hr]'
                        ];
                        break;
                    case 2:
                        $Block = [
                            'markup' => $this->popHeader($level)
                                . '[table][tr=#B0C4DE][td][align=center][b][size=4][color=#000000]'
                                . $this->line($text)
                                . '[/color][/size][/b][/align][/td][/tr][/table]'
                                . '[table][tr=rgb(240,230,140)][td][align=left][table][tr=rgb(238,232,170)][td][align=center][b]'
                        ];
                }
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
        return '[align=center]'
            . parent::text($text)
            . $this->popHeader(0)
            . '[/align]';
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
