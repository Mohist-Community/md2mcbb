<?php

namespace App\Parser\Extensions;

use App\Parser\Parser;
use SplStack;

class TestParser extends Parser
{
    protected $iconList = [
        '{:portal:}',
        '{:beacon:}',
        '{:barrier:}',
        '{:crafting_table_top:}',
        '{:crafting_table_front:}',
        '{:furnace_front_off:}',
        '{:furnace_front_on:}',
        '{:enchanting_table_top:}',
        '{:ladder:}',
        '{:iron_bars:}',
        '{:structure_block_data:}',
        '{:structure_block:}',
        '{:structure_block_corner:}',
        '{:structure_block_save:}',
        '{:tnt_side:}',
        '{:structure_block_load:}',
        '{:mob_spawner:}',
        '{:mushroom_brown:}',
        '{:mushroom_red:}',
        '{:deadbush:}',
        '{:nether_wart_stage:}',
        '{:grass_side:}',
        '{:grass_side_snowed:}',
        '{:mycelium_side:}',
        '{:dirt_podzol_side:}',
        '{:grass_path_side:}',
        '{:dirt:}',
        '{:dirt_podzol_top:}',
        '{:hay_block_side:}',
        '{:glowstone:}',
        '{:stonebrick_carved:}',
        '{:bedrock:}',
        '{:diamond_block:}',
        '{:emerald_block:}',
        '{:gold_block:}',
        '{:stonebrick:}',
        '{:mushroom_block_skin_brown:}',
        '{:mushroom_block_skin_red:}',
        '{:mushroom_block_inside:}',
        '{:ice_packed:}',
        '{:stonebrick_mossy:}',
        '{:iron_ore:}',
        '{:quartz_ore:}',
        '{:gold_ore:}',
        '{:lapis_ore:}',
        '{:diamond_ore:}',
        '{:redstone_ore:}',
        '{:emerald_ore:}',
        '{:coal_ore:}',
        '{:pumpkin_face_on:}',
        '{:stonebrick_cracked:}',
        '{:endframe_top:}',
        '{:coarse_dirt:}',
        '{:melon_side:}',
        '{:pumpkin_face_off:}',
        '{:lava_still:}',
        '{:magma:}',
        '{:ice:}',
        '{:water_still:}',
        '{:Grid_Fire:}',
    ];
    protected $iconPoint = 0;
    /** @var SplStack $stack */
    protected $stack;
    public function __construct()
    {
        shuffle($this->iconList);
        $this->stack=new SplStack();
    }
    protected function popHeader($level){
        $op = '';
        while(!$this->stack->isEmpty() && $this->stack->top() >= $level){
            $op .= '[/align][/td][/tr][/table][/align]';
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
                /**
                 * [align=center][table=98%,#B0C4DE][tr][td][align=left][size=24px]{:stonebrick_mossy:} h1[/size][/align][/td][/tr]
                [/table][table=98%,#EEE8AA][tr][td][align=left]正文[/align][/td][/tr]
                [/table][/align]
                 */
                $Block = [
                    'markup' => $this->popHeader($level)
                        . '[table=98%,#B0C4DE][tr][td][align=left][size=24px]'
                        . $this->iconList[$this->iconPoint++%count($this->iconList)]
                        . ' ' . $text.'[/size][/align][/td][/tr][/table]' . '[table=98%,#EEE8AA][tr][td][align=left]'
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

    protected function lines(array $lines)
    {
        return parent::lines($lines).
            $this->popHeader(0);
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
