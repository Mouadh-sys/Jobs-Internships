<?php

namespace App\Doctrine;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * Custom DQL function for JSON_CONTAINS
 * Usage: SELECT u FROM User u WHERE JSON_CONTAINS(u.roles, 'ROLE_ADMIN') = 1
 */
class JsonContainsFunction extends FunctionNode
{
    public $jsonField;
    public $value;

    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->jsonField = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->value = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'JSON_CONTAINS(' .
            $this->jsonField->dispatch($sqlWalker) . ', ' .
            $this->value->dispatch($sqlWalker) .
        ')';
    }
}

