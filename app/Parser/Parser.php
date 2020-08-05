<?php

namespace App\Parser;

use Parsedown;

/**
 * Class Parser
 * @package App\Parser
 * @link \App\Console\Commands\Parse
 */
class Parser extends Parsedown
{
    protected $textLevelElements = array(
        'size', 'url', 'b', 'align', 'code','td','table','list','u','img'
    );

    /**
     * Inline Part.
     */

    protected function lines(array $lines)
    {
        $CurrentBlock = null;

        foreach ($lines as $line)
        {
            if (chop($line) === '')
            {
                if (isset($CurrentBlock))
                {
                    $CurrentBlock['interrupted'] = true;
                }

                continue;
            }

            if (strpos($line, "\t") !== false)
            {
                $parts = explode("\t", $line);

                $line = $parts[0];

                unset($parts[0]);

                foreach ($parts as $part)
                {
                    $shortage = 4 - self::mb_strlen($line, 'utf-8') % 4;

                    $line .= str_repeat(' ', $shortage);
                    $line .= $part;
                }
            }

            $indent = 0;

            while (isset($line[$indent]) and $line[$indent] === ' ')
            {
                $indent ++;
            }

            $text = $indent > 0 ? substr($line, $indent) : $line;

            # ~

            $Line = array('body' => $line, 'indent' => $indent, 'text' => $text);

            # ~

            if (isset($CurrentBlock['continuable']))
            {
                $Block = $this->{'block'.$CurrentBlock['type'].'Continue'}($Line, $CurrentBlock);

                if (isset($Block))
                {
                    $CurrentBlock = $Block;

                    continue;
                }
                else
                {
                    if ($this->isBlockCompletable($CurrentBlock['type']))
                    {
                        $CurrentBlock = $this->{'block'.$CurrentBlock['type'].'Complete'}($CurrentBlock);
                    }
                }
            }

            # ~

            $marker = $text[0];

            # ~

            $blockTypes = $this->unmarkedBlockTypes;

            if (isset($this->BlockTypes[$marker]))
            {
                foreach ($this->BlockTypes[$marker] as $blockType)
                {
                    $blockTypes []= $blockType;
                }
            }

            #
            # ~

            foreach ($blockTypes as $blockType)
            {
                $Block = $this->{'block'.$blockType}($Line, $CurrentBlock);

                if (isset($Block))
                {
                    $Block['type'] = $blockType;

                    if ( ! isset($Block['identified']))
                    {
                        $Blocks []= $CurrentBlock;

                        $Block['identified'] = true;
                    }

                    if ($this->isBlockContinuable($blockType))
                    {
                        $Block['continuable'] = true;
                    }

                    $CurrentBlock = $Block;

                    continue 2;
                }
            }

            # ~

            if (isset($CurrentBlock) and ! isset($CurrentBlock['type']) and ! isset($CurrentBlock['interrupted']))
            {
                $CurrentBlock['element']['text'] .= "\n".$text;
            }
            else
            {
                $Blocks []= $CurrentBlock;

                $CurrentBlock = $this->paragraph($Line);

                $CurrentBlock['identified'] = true;
            }
        }

        # ~

        if (isset($CurrentBlock['continuable']) and $this->isBlockCompletable($CurrentBlock['type']))
        {
            $CurrentBlock = $this->{'block'.$CurrentBlock['type'].'Complete'}($CurrentBlock);
        }

        # ~

        $Blocks []= $CurrentBlock;

        unset($Blocks[0]);

        # ~

        $markup = '';

        foreach ($Blocks as $Block)
        {
            if (isset($Block['hidden']))
            {
                continue;
            }

            $markup .= isset($Block['markup']) ? $Block['markup'] : $this->element($Block['element']);
        }

        # ~

        return $markup;
    }
    protected function inlineEmphasis($Excerpt)
    {
        if ( ! isset($Excerpt['text'][1]))
        {
            return;
        }

        $marker = $Excerpt['text'][0];

        if ($Excerpt['text'][1] === $marker and preg_match($this->StrongRegex[$marker], $Excerpt['text'], $matches))
        {
            $emphasis = 'b';
        }
        elseif (preg_match($this->EmRegex[$marker], $Excerpt['text'], $matches))
        {
            $emphasis = 'i';
        }
        else
        {
            return;
        }

        return array(
            'extent' => strlen($matches[0]),
            'element' => array(
                'name' => $emphasis,
                'handler' => 'line',
                'text' => $matches[1],
            ),
        );
    }
    protected function inlineStrikethrough($Excerpt)
    {
        if ( ! isset($Excerpt['text'][1]))
        {
            return;
        }

        if ($Excerpt['text'][1] === '~' and preg_match('/^~~(?=\S)(.+?)(?<=\S)~~/', $Excerpt['text'], $matches))
        {
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 's',
                    'text' => $matches[1],
                    'handler' => 'line',
                ),
            );
        }
    }
    protected function inlineEmailTag($Excerpt)
    {
        if (strpos($Excerpt['text'], '>') !== false and preg_match('/^<((mailto:)?\S+?@\S+?)>/i', $Excerpt['text'], $matches))
        {
            $url = $matches[1];

            if ( ! isset($matches[2]))
            {
                $url = 'mailto:' . $url;
            }

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'url',
                    'text' => $matches[1],
                    'data' => $url,
                ),
            );
        }
    }
    protected function inlineLink($Excerpt)
    {
        $Element = array(
            'name' => 'url',
            'handler' => 'line',
            'nonNestables' => array('Url', 'Link'),
            'text' => null,
            'data' => null,
        );

        $extent = 0;

        $remainder = $Excerpt['text'];

        if (preg_match('/\[((?:[^][]++|(?R))*+)\]/', $remainder, $matches))
        {
            $Element['text'] = $matches[1];

            $extent += strlen($matches[0]);

            $remainder = substr($remainder, $extent);
        }
        else
        {
            return;
        }

        if (preg_match('/^[(]\s*+((?:[^ ()]++|[(][^ )]+[)])++)(?:[ ]+("[^"]*"|\'[^\']*\'))?\s*[)]/', $remainder, $matches))
        {
            $Element['data'] = $matches[1];

            $extent += strlen($matches[0]);
        }
        else
        {
            if (preg_match('/^\s*\[(.*?)\]/', $remainder, $matches))
            {
                $definition = strlen($matches[1]) ? $matches[1] : $Element['text'];
                $definition = strtolower($definition);

                $extent += strlen($matches[0]);
            }
            else
            {
                $definition = strtolower($Element['text']);
            }

            if ( ! isset($this->DefinitionData['Reference'][$definition]))
            {
                return;
            }

            $Definition = $this->DefinitionData['Reference'][$definition];

            $Element['data'] = $Definition['url'];
        }

        return array(
            'extent' => $extent,
            'element' => $Element,
        );
    }
    protected function inlineUrl($Excerpt)
    {
        if ($this->urlsLinked !== true or ! isset($Excerpt['text'][2]) or $Excerpt['text'][2] !== '/')
        {
            return;
        }

        if (preg_match('/\bhttps?:[\/]{2}[^\s<]+\b\/*/ui', $Excerpt['context'], $matches, PREG_OFFSET_CAPTURE))
        {
            $url = $matches[0][0];
            $Inline = array(
                'extent' => strlen($matches[0][0]),
                'position' => $matches[0][1],
                'element' => array(
                    'name' => 'url',
                    'text' => $url,
                    'data' => $url,
                ),
            );

            return $Inline;
        }
    }
    protected function inlineUrlTag($Excerpt)
    {
        if (strpos($Excerpt['text'], '>') !== false and preg_match('/^<(\w+:\/{2}[^ >]+)>/i', $Excerpt['text'], $matches))
        {
            $url = $matches[1];

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'a',
                    'text' => $url,
                    'data' => $url,
                ),
            );
        }
    }
    protected function inlineCode($Excerpt)
    {
        $marker = $Excerpt['text'][0];

        if (preg_match('/^('.$marker.'+)[ ]*(.+?)[ ]*(?<!'.$marker.')\1(?!'.$marker.')/s', $Excerpt['text'], $matches))
        {
            $text = $matches[2];
            $text = preg_replace("/[ ]*\n/", ' ', $text);

            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'u',
                    'text' => $text,
                ),
            );
        }
    }
    protected function inlineImage($Excerpt)
    {
        if ( ! isset($Excerpt['text'][1]) or $Excerpt['text'][1] !== '[')
        {
            return;
        }

        $Excerpt['text']= substr($Excerpt['text'], 1);

        $Link = $this->inlineLink($Excerpt);

        if ($Link === null)
        {
            return;
        }

        $Inline = array(
            'extent' => $Link['extent'] + 1,
            'element' => array(
                'name' => 'img',
                'text' => $Link['element']['data']
            ),
        );

        return $Inline;
    }

    /* Block Part. */

    /* # hi */
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
            $Block = array(
                'element' => array(
                    'name' => 'align',
                    'text' => array(),
                    'handler' => 'elements',
                    'data' => 'left',
                ),
            );
            $Element = array(
                'name' => 'size',
                'text' => $text.($level <= 2 ? '' : "\n"),
                'handler' => 'line',
                'data' => 8-$level,
            );
            $Block['element']['text'][] = $Element;
            if($level <= 2){
                $Element = array(
                    'name' => 'hr',
                    'noClose' => 'true',
                );
                $Block['element']['text'][] = $Element;
            }
            return $Block;
        }
    }

    /* <tab>{                   *
     * <tab>    "abc":"def"     *
     * <tab>}                   */
    protected function blockCode($Line, $Block = null)
    {
        if (isset($Block) and ! isset($Block['type']) and ! isset($Block['interrupted']))
        {
            return;
        }

        if ($Line['indent'] >= 4)
        {
            $text = substr($Line['body'], 4);

            $Block = array(
                'element' => array(
                    'name' => 'code',
                    'rawHtml' => $text,
                ),
            );

            return $Block;
        }
    }
    protected function blockCodeContinue($Line, $Block)
    {
        if ($Line['indent'] >= 4)
        {
            if (isset($Block['interrupted']))
            {
                $Block['element']['rawHtml'] .= "\n";

                unset($Block['interrupted']);
            }

            $Block['element']['rawHtml'] .= "\n";

            $text = substr($Line['body'], 4);

            $Block['element']['rawHtml'] .= $text;

            return $Block;
        }
    }
    protected function blockCodeComplete($Block)
    {
        $text = $Block['element']['rawHtml'];

        $Block['element']['rawHtml'] = $text;

        return $Block;
    }

    /* ````json                 *
     * {                        *
     *     "abc":"def"          *
     * }                        *
     * ````                     */
    protected function blockFencedCode($Line)
    {
        if (preg_match('/^['.$Line['text'][0].']{3,}[ ]*([^`]+)?[ ]*$/', $Line['text'], $matches))
        {
            return array(
                'char' => $Line['text'][0],
                'element' =>  array(
                    'name' => 'code',
                    'rawHtml' => '',
                ),
            );
        }
    }
    protected function blockFencedCodeContinue($Line, $Block)
    {
        if (isset($Block['complete']))
        {
            return;
        }

        if (isset($Block['interrupted']))
        {
            $Block['element']['rawHtml'] .= "\n";

            unset($Block['interrupted']);
        }

        if (preg_match('/^'.$Block['char'].'{3,}[ ]*$/', $Line['text']))
        {
            $Block['element']['rawHtml'] = substr($Block['element']['rawHtml'], 1);

            $Block['complete'] = true;

            return $Block;
        }

        $Block['element']['rawHtml'] .= "\n".$Line['body'];

        return $Block;
    }
    protected function blockFencedCodeComplete($Block)
    {
        $text = $Block['element']['rawHtml'];

        $Block['element']['rawHtml'] = $text;

        return $Block;
    }

    /* - a                      *
     * - b                      *
     * - c                      */
    /* 1. a                     *
     * 2. b                     *
     * 3. c                     */
    protected function blockList($Line)
    {
        list($name, $pattern) = $Line['text'][0] <= '-' ? array('ul', '[*+-]') : array('ol', '[0-9]+[.]');

        if (preg_match('/^('.$pattern.'[ ]+)(.*)/', $Line['text'], $matches))
        {
            $Block = array(
                'indent' => $Line['indent'],
                'pattern' => $pattern,
                'element' => array(
                    'name' => 'list',
                    'handler' => 'elements',
                ),
            );

            if($name === 'ol')
            {
                $listStart = stristr($matches[0], '.', true);

                if($listStart !== '1')
                {
                    $Block['element']['data'] = $listStart;
                }
            }

            $Block['li'] = array(
                'name' => '*',
                'handler' => 'li',
                'text' => array(
                    $matches[2],
                ),
                'noClose' => true,
            );

            $Block['element']['text'] []= &$Block['li'];

            return $Block;
        }
    }
    protected function blockListContinue($Line, array $Block)
    {
        if ($Block['indent'] === $Line['indent'] and preg_match('/^'.$Block['pattern'].'(?:[ ]+(.*)|$)/', $Line['text'], $matches))
        {
            if (isset($Block['interrupted']))
            {
                $Block['li']['text'] []= '';

                $Block['loose'] = true;

                unset($Block['interrupted']);
            }

            unset($Block['li']);

            $text = isset($matches[1]) ? $matches[1] : '';

            $Block['li'] = array(
                'name' => '*',
                'handler' => 'li',
                'text' => array(
                    $text,
                ),
                'noClose' => true,
            );

            $Block['element']['text'] []= & $Block['li'];

            return $Block;
        }

        if ($Line['text'][0] === '[' and $this->blockReference($Line))
        {
            return $Block;
        }

        if ( ! isset($Block['interrupted']))
        {
            $text = preg_replace('/^[ ]{0,4}/', '', $Line['body']);

            $Block['li']['text'] []= $text;

            return $Block;
        }

        if ($Line['indent'] > 0)
        {
            $Block['li']['text'] []= '';

            $text = preg_replace('/^[ ]{0,4}/', '', $Line['body']);

            $Block['li']['text'] []= $text;

            unset($Block['interrupted']);

            return $Block;
        }
    }
    protected function blockListComplete(array $Block)
    {
        if (isset($Block['loose']))
        {
            foreach ($Block['element']['text'] as &$li)
            {
                if (end($li['text']) !== '')
                {
                    $li['text'] []= '';
                }
            }
        }
        return $Block;
    }

    /* | aa | bbb |             *
     * | -- | --: |             *
     * | cc | ddd |             **/
    protected function blockTable($Line, array $Block = null)
    {
        if ( ! isset($Block) or isset($Block['type']) or isset($Block['interrupted']))
        {
            return;
        }

        if (strpos($Block['element']['text'], '|') !== false and chop($Line['text'], ' -:|') === '')
        {
            $alignments = array();

            $divider = $Line['text'];

            $divider = trim($divider);
            $divider = trim($divider, '|');

            $dividerCells = explode('|', $divider);

            foreach ($dividerCells as $dividerCell)
            {
                $dividerCell = trim($dividerCell);

                if ($dividerCell === '')
                {
                    continue;
                }

                $alignment = null;

                if ($dividerCell[0] === ':')
                {
                    $alignment = 'left';
                }

                if (substr($dividerCell, - 1) === ':')
                {
                    $alignment = $alignment === 'left' ? 'center' : 'right';
                }

                $alignments []= $alignment;
            }

            # ~

            $HeaderElements = array();

            $header = $Block['element']['text'];

            $header = trim($header);
            $header = trim($header, '|');

            $headerCells = explode('|', $header);

            foreach ($headerCells as $index => $headerCell)
            {
                $headerCell = trim($headerCell);

                $HeaderElement = array(
                    'name' => 'td',
                    'text' => $headerCell,
                    'handler' => 'line',
                );

                if (isset($alignments[$index]))
                {
                    $alignment = $alignments[$index];

                    $HeaderElement['alignment'] = $alignment;
                }

                $HeaderElements []= $HeaderElement;
            }

            # ~

            $Block = array(
                'alignments' => $alignments,
                'identified' => true,
                'element' => array(
                    'name' => 'table',
                    'handler' => 'elements',
                ),
            );

            $Block['element']['text'][]= array(
                'name' => 'tr',
                'handler' => 'elements',
                'text' => $HeaderElements,
            );

            return $Block;
        }
    }
    protected function blockTableContinue($Line, array $Block)
    {
        if (isset($Block['interrupted']))
        {
            return;
        }

        if ($Line['text'][0] === '|' or strpos($Line['text'], '|'))
        {
            $Elements = array();

            $row = $Line['text'];

            $row = trim($row);
            $row = trim($row, '|');

            preg_match_all('/(?:(\\\\[|])|[^|`]|`[^`]+`|`)+/', $row, $matches);

            foreach ($matches[0] as $index => $cell)
            {
                $cell = trim($cell);

                $Element = array(
                    'name' => 'td',
                    'handler' => 'line',
                    'text' => $cell,
                );

                if (isset($Block['alignments'][$index]))
                {
                    $Element['alignment'] = $Block['alignments'][$index];
                }

                $Elements []= $Element;
            }

            $Element = array(
                'name' => 'tr',
                'handler' => 'elements',
                'text' => $Elements,
            );

            $Block['element']['text'][]= $Element;

            return $Block;
        }
    }

    protected function paragraph($Line)
    {
        $Block = array(
            'element' => array(
                'name' => 'align',
                'text' => $Line['text'],
                'handler' => 'line',
                'data' => 'left',
            ),
        );

        return $Block;
    }

    protected function element(array $Element,$nonNestables = null,$parentElement = null)
    {
        if ($this->safeMode)
        {
            $Element = $this->sanitiseElement($Element);
        }
        $markup = '';
        $name=@$Element['name'];
        if($name
            && method_exists($this,'element'.ucfirst($name))
            && !isset($Element['disableOverride'])){
            return $this->{'element'.ucfirst($name)}($Element,$nonNestables,$parentElement);
        }
        if($name
            && !isset($Element['noOpen'])) {
            $markup .= '[' . $Element['name'];

            if (isset($Element['data'])) {
                $markup .= '=';
                if (is_array($Element['data'])) {
                    $markup .= implode(',', $Element['data']);
                } else {
                    $markup .= $Element['data'];
                }
            }
            $markup .= ']';
        }
        $permitRawHtml = false;
        if (isset($Element['text'])) {
            $text = $Element['text'];
        } elseif (isset($Element['rawHtml'])) {
            $text = $Element['rawHtml'];
            $allowRawHtmlInSafeMode = isset($Element['allowRawHtmlInSafeMode']) && $Element['allowRawHtmlInSafeMode'];
            $permitRawHtml = !$this->safeMode || $allowRawHtmlInSafeMode;
        }


        if (isset($Element['noOpen'])
            && $Element['noOpen']) {
            $markup = '';
        }

        if (isset($Element['alignment'])) {
            $alignment = $Element['alignment'];
        } elseif (isset($parentElement['alignment'])) {
            $alignment = $parentElement['alignment'];
            $Element['alignment'] = $alignment;
        }
        if (isset($text))
        {
            if (!isset($Element['nonNestables']))
            {
                $Element['nonNestables'] = array();
            }

            if (isset($Element['handler']))
            {
                $text = $this->{$Element['handler']}($text, $Element['nonNestables']);
            }
            elseif (!$permitRawHtml)
            {
                $text = self::escape($text, true);
            }

            if(isset($alignment)){
                $markup .= $this->element(array(
                    'name' => 'align',
                    'data' => $alignment,
                    'rawHtml' => $text,
                    'allowRawHtmlInSafeMode' => true,
                ));
            }else{
                $markup .= $text;
            }

        }
        if($name
            && !isset($Element['noClose'])){
                $markup .= '[/'.$Element['name'].']';
                if(!in_array($Element['name'],$this->textLevelElements)){
                    $markup .= "\n";
            }
        }
        return $markup;
    }
    protected function elements(array $Elements)
    {
        $markup = '';

        foreach ($Elements as $Element)
        {
            $markup .= $this->element($Element);
        }

        return $markup;
    }

    protected static function mb_strlen($str, $encoding = null)
    {
        $s = (string) $str;
        $len = \strlen($s);
        for ($i = 0, $j = 0; $i < $len; ++$i, ++$j) {
            switch ($s[$i] & "\xF0") {
                case "\xC0":
                case "\xD0":
                    break;
                case "\xF0":
                    ++$i;
                case "\xE0":
                    $i += 2;
                    break;
            }
        }
        return $j;
    }
}
