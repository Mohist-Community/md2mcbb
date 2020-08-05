<?php

namespace App\Parser\Extensions;

use App\Parser\Parser;

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
    public function __construct()
    {
        shuffle($this->iconList);
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
                    'level' => $level,
                    'element' => [
                        'name' => 'align',
                        'text' => [
                            [
                                'name' => 'table',
                                'text' => [
                                    'name' => 'tr',
                                    'handler' => 'element',
                                    'text' => [
                                        'name' => 'td',
                                        'handler' => 'element',
                                        'text' => [
                                            'name' => 'align',
                                            'text' => [
                                                'name' => 'size',
                                                'text' => $this->iconList[$this->iconPoint++%count($this->iconList)].' '.$text,
                                                'handler' => 'line',
                                                'data' => min(8 - $level, 6),
                                            ],
                                            'handler' => 'element',
                                            'data' => 'left',
                                        ],
                                    ]
                                ],
                                'handler' => 'element',
                                'data' => [
                                    '98%',
                                    '#B0C4DE',
                                ],
                            ]
                        ],
                        'handler' => 'elements',
                        'data' => 'center',
                    ],
                ];
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
    protected function blockHeaderContinue($Line, $Block)
    {
        if(!isset($Block['level'])){
            return;
        }
        if (isset($Line['text'][1])
            && $Line['text'][0] == '#')
        {
            $level = 1;

            while (isset($Line['text'][$level]) and $Line['text'][$level] === '#')
            {
                $level ++;
            }
            if($level == $Block['level']){
                return;
            }
        }
        if(!isset($Block['element']['text'][1])){
            $Block['element']['text'][1] = [
                'name' => 'table',
                'text' => [
                    'name' => 'tr',
                    'handler' => 'element',
                    'text' => [
                        'name' => 'td',
                        'handler' => 'element',
                        'text' => [
                            'name' => 'align',
                            'text' => (array) $Line['body'],
                            'handler' => 'lines',
                            'data' => 'left',
                        ],
                    ]
                ],
                'handler' => 'element',
                'data' => [
                    '98%',
                    '#EEE8AA',
                ],
            ];
        }else{
            $Block['element']['text'][1]['text']['text']['text']['text'][] = $Line['body'];
        }
        return $Block;
    }
    protected function blockHeaderComplete($Block){
        $Block['element']['text'][]=[
            'text'=>"\n"
        ];
        return $Block;
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
