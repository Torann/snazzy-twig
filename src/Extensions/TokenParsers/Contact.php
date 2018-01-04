<?php

namespace Torann\SnazzyTwig\Extensions\TokenParsers;

use Twig_Node;
use Twig_Token;
use Twig_Node_Text;
use Twig_Node_Block;
use Twig_TokenParser;
use Twig_Node_BlockReference;

class Contact extends Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $lineNum = $token->getLine();
        $stream = $this->parser->getStream();
        $name = 'twig_contact_form' . str_random(4);

        $this->parser->setBlock($name, $block = new Twig_Node_Block($name, new Twig_Node([]), $lineNum));
        $this->parser->pushLocalScope();
        $this->parser->pushBlockStack($name);

        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideBlockEnd'], true);
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $this->wrapFormElements($body);

        $block->setNode('body', $body);

        $this->parser->popBlockStack();
        $this->parser->popLocalScope();

        return new Twig_Node_BlockReference($name, $lineNum, $this->getTag());
    }

    /**
     * Wrap form elements with the actual form.
     *
     * @param Twig_Node_Text $body
     */
    public function wrapFormElements(Twig_Node_Text $body)
    {
        $form = $body->getAttribute('data');

        $body->setAttribute('data', '<form class="form-part" method="post" action="/ajax/contact"><input type="hidden" name="_token" value="{{csrf_token}}">' . $form . '</form>');
    }

    public function decideBlockEnd(Twig_Token $token)
    {
        return $token->test('endcontact');
    }

    public function getTag()
    {
        return 'contact';
    }
}
