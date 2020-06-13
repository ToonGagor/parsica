<?php declare(strict_types=1);

namespace Mathias\ParserCombinator;

use Mathias\ParserCombinator\Parser\Parser;
use Mathias\ParserCombinator\ParseResult\ParseResult;
use function Mathias\ParserCombinator\ParseResult\{fail};

/**
 * Identity parser, returns the Parser as is.
 *
 * @template T
 *
 * @param Parser<T> $parser
 *
 * @return Parser<T>
 */
function identity(Parser $parser): Parser
{
    return $parser;
}

/**
 * Parse something, strip it from the remaining input, but discard the output.
 *
 * @template T
 *
 * @param Parser<T> $parser
 *
 * @return Parser<T>
 */
function ignore(Parser $parser): Parser
{
    return $parser->ignore();
}

/**
 * Optionally parse something, but still succeed if the thing is not there
 *
 * @template T
 *
 * @param Parser<T> $parser
 *
 * @return Parser<T|string>
 */
function optional(Parser $parser): Parser
{
    return $parser->optional();
}

/**
 * Parse something, then follow by something else. Ignore the result of the first parser and return the result of the
 * second parser.
 *
 * Haskell Equivalent if (>>)
 *
 * @param Parser<T1> $first
 * @param Parser<T2> $second
 *
 * @return Parser<T2>
 * @deprecated 0.2
 * @template T1
 * @template T2
 */
function seq(Parser $first, Parser $second): Parser
{
    return $first->followedBy($second);
}

/**
 * Either parse the first thing or the second thing
 *
 * @template T
 *
 * @param Parser<T> $first
 * @param Parser<T> $second
 *
 * @return Parser<T>
 * @deprecated 0.2
 */
function either(Parser $first, Parser $second): Parser
{
    return $first->or($second);
}

/**
 * Mappend all the passed parsers.
 *
 * @template T
 *
 * @param list<Parser<T>> $parsers
 *
 * @return Parser<T>
 */
function assemble(Parser ...$parsers): Parser
{
    return array_reduce(
        $parsers,
        fn(Parser $l, Parser $r): Parser => $l->mappend($r),
        nothing()
    )->label('assemble()');
}

/**
 * Parse into an array that consists of the results of all parsers.
 *
 * @template T
 *
 * @param list<Parser<T>> $parsers
 *
 * @return Parser<T>
 */
function collect(Parser ...$parsers): Parser
{
    /** @psalm-suppress MissingClosureParamType */
    $toArray = fn($v): array => [$v];
    $arrayParsers = array_map(
        fn(Parser $parser): Parser => $parser->fmap($toArray),
        $parsers
    );
    return assemble(...$arrayParsers)->label('collect()');
}

/**
 * Tries each parser one by one
 *
 * @param Parser<TParsed>[] $parsers
 *
 * @return Parser<TParsed>
 * @deprecated 0.2
 *
 * @template TParsed
 *
 */
function any(Parser ...$parsers): Parser
{
    return Parser::make(function (string $input) use ($parsers): ParseResult {
        $expectations = [];
        foreach ($parsers as $parser) {
            $r = $parser->run($input);
            if ($r->isSuccess()) {
                return $r;
            } else {
                $expectations[] = $r->expected();
            }
        }
        return fail("any(" . implode(", ", $expectations) . ")", "@TODO");
    });
}

/**
 * One or more repetitions of Parser
 *
 * @param Parser<TParsed> $parser
 *
 * @return Parser<TParsed>
 * @deprecated 0.2
 *
 * @template TParsed
 *
 */
function atLeastOne(Parser $parser): Parser
{
    return Parser::make(function (string $input) use ($parser): ParseResult {
        $r = $parser->run($input);
        if ($r->isFail()) return $r;

        while ($r->isSuccess()) {
            $next = $parser->continueFrom($r);
            if ($next->isFail()) return $r;
            $r = $r->mappend($next);
        }
        return $r;
    });
}