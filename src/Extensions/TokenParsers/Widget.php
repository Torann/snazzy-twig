<?php

namespace Torann\SnazzyTwig\Extensions\TokenParsers;

use Twig_Node;
use Twig_Token;
use Twig_Node_Block;
use Twig_TokenParser;
use Twig_Node_Expression_Constant;
use Torann\SnazzyTwig\Extensions\Node\WidgetReference;

class Widget extends Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $lineNum = $token->getLine();
        $stream = $this->parser->getStream();
        $name = 'twig_doodad_' . $stream->expect(Twig_Token::NAME_TYPE)->getValue();

        // Get widget options
        $options = $this->parseOptions();

        $this->parser->setBlock($name, $block = new Twig_Node_Block($name, new Twig_Node([]), $lineNum));
        $this->parser->pushLocalScope();
        $this->parser->pushBlockStack($name);

        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideBlockEnd'], true);
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $block->setNode('body', $body);

        $this->parser->popBlockStack();
        $this->parser->popLocalScope();

        return new WidgetReference($name, $options, $lineNum, $this->getTag());
    }

    protected function parseOptions()
    {
        $options = [];

        if ($this->parser->getStream()->test(Twig_Token::BLOCK_END_TYPE) === false) {
            $expr = $this->parser->getExpressionParser()->parseExpression();

            // Parse them shity options
            foreach ($expr->getKeyValuePairs() as $pair) {
                if ($pair['key'] instanceof Twig_Node_Expression_Constant
                    && $pair['value'] instanceof Twig_Node_Expression_Constant
                ) {
                    $options[$pair['key']->getAttribute('value')] = $pair['value']->getAttribute('value');
                }
            }
        }

        return $options;
    }

    public function decideBlockEnd(Twig_Token $token)
    {
        return $token->test('endwidget');
    }

    public function getTag()
    {
        return 'widget';
    }
}
