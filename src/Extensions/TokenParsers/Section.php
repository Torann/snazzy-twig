<?php

namespace Torann\SnazzyTwig\Extensions\TokenParsers;

use Twig_Node;
use Twig_Token;
use Twig_Node_Block;
use Twig_TokenParser;

class Section extends Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $lineNum = $token->getLine();
        $stream = $this->parser->getStream();
        $name = $stream->expect(Twig_Token::NAME_TYPE)->getValue();

        $this->parser->setBlock($name, $block = new Twig_Node_Block($name, new Twig_Node([]), $lineNum));
        $this->parser->pushLocalScope();
        $this->parser->pushBlockStack($name);

        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideBlockEnd'], true);
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $block->setNode('body', $body);

        $this->parser->popBlockStack();
        $this->parser->popLocalScope();

        return new Twig_Node();
    }

    public function decideBlockEnd(Twig_Token $token)
    {
        return $token->test('endsection');
    }

    public function getTag()
    {
        return 'section';
    }
}
