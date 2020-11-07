<?php declare(strict_types=1);
/*
 * This file is part of the Parsica library.
 *
 * Copyright (c) 2020 Mathias Verraes <mathias@verraes.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Verraes\Parsica\Expression;

use Verraes\Parsica\Parser;

/**
 * @internal
 * @template TSymbol
 * @template TExpressionAST
 */
final class BinaryOperator
{
    /**
     * @psalm-var Parser<TSymbol>
     */
    private Parser $symbol;

    /** @psalm-var callable(TExpressionAST, TExpressionAST):TExpressionAST $transform */
    private $transform;

    private string $label;

    /**
     * @psalm-param Parser<TSymbol> $symbol
     * @psalm-param callable(TExpressionAST, TExpressionAST):TExpressionAST $transform
     * @psalm-param string $label
     */
    function __construct(Parser $symbol, callable $transform, string $label = "")
    {
        $this->symbol = $symbol;
        $this->transform = $transform;
        $this->label = $label ?: $symbol->getLabel() . " operator";
    }

    /**
     * @psalm-return Parser<TSymbol>
     */
    function symbol(): Parser
    {
        return $this->symbol;
    }

    /**
     * @psalm-return callable(TExpressionAST, TExpressionAST):TExpressionAST
     */
    function transform(): callable
    {
        return $this->transform;
    }

    function label(): string
    {
        return $this->label;
    }

}
