<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 * (c) Armin Ronacher
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Twig\Error\SyntaxError;
use Twig\Source;
use Twig\Token;

/**
 * Represents a token stream.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class Twig_TokenStream
{
    private $tokens;
    private $current = 0;
    private $source;

    public function __construct(array $tokens, Source $source = null)
    {
        $this->tokens = $tokens;
        $this->source = $source ?: new Source('', '');
    }

    public function __toString()
    {
        return implode("\n", $this->tokens);
    }

    public function injectTokens(array $tokens)
    {
        $this->tokens = array_merge(\array_slice($this->tokens, 0, $this->current), $tokens, \array_slice($this->tokens, $this->current));
    }

    /**
     * Sets the pointer to the next token and returns the old one.
     *
     * @return Token
     */
    public function next()
    {
        if (!isset($this->tokens[++$this->current])) {
            throw new SyntaxError('Unexpected end of template.', $this->tokens[$this->current - 1]->getLine(), $this->source);
        }

        return $this->tokens[$this->current - 1];
    }

    /**
     * Tests a token, sets the pointer to the next one and returns it or throws a syntax error.
     *
     * @return Token|null The next token if the condition is true, null otherwise
     */
    public function nextIf($primary, $secondary = null)
    {
        if ($this->tokens[$this->current]->test($primary, $secondary)) {
            return $this->next();
        }
    }

    /**
     * Tests a token and returns it or throws a syntax error.
     *
     * @return Token
     */
    public function expect($type, $value = null, $message = null)
    {
        $token = $this->tokens[$this->current];
        if (!$token->test($type, $value)) {
            $line = $token->getLine();
            throw new SyntaxError(sprintf('%sUnexpected token "%s" of value "%s" ("%s" expected%s).',
                $message ? $message.'. ' : '',
                Token::typeToEnglish($token->getType()), $token->getValue(),
                Token::typeToEnglish($type), $value ? sprintf(' with value "%s"', $value) : ''),
                $line,
                $this->source
            );
        }
        $this->next();

        return $token;
    }

    /**
     * Looks at the next token.
     *
     * @param int $number
     *
     * @return Token
     */
    public function look($number = 1)
    {
        if (!isset($this->tokens[$this->current + $number])) {
            throw new SyntaxError('Unexpected end of template.', $this->tokens[$this->current + $number - 1]->getLine(), $this->source);
        }

        return $this->tokens[$this->current + $number];
    }

    /**
     * Tests the current token.
     *
     * @return bool
     */
    public function test($primary, $secondary = null)
    {
        return $this->tokens[$this->current]->test($primary, $secondary);
    }

    /**
     * Checks if end of stream was reached.
     *
     * @return bool
     */
    public function isEOF()
    {
        return /* Token::EOF_TYPE */ -1 === $this->tokens[$this->current]->getType();
    }

    /**
     * @return Token
     */
    public function getCurrent()
    {
        return $this->tokens[$this->current];
    }

    /**
     * Gets the source associated with this stream.
     *
     * @return Source
     *
     * @internal
     */
    public function getSourceContext()
    {
        return $this->source;
    }
}

class_alias('Twig_TokenStream', 'Twig\TokenStream', false);
