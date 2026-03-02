<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Tokenizer\Analyzer;

use PhpCsFixer\Tokenizer\CT;
use PhpCsFixer\Tokenizer\Tokens;

// Define token constants for compatibility
if (!defined('T_STRING')) {
    define('T_STRING', 315);
}
if (!defined('T_NS_SEPARATOR')) {
    define('T_NS_SEPARATOR', 378);
}
if (!defined('T_DOUBLE_COLON')) {
    define('T_DOUBLE_COLON', 355);
}
if (!defined('T_ELLIPSIS')) {
    define('T_ELLIPSIS', 369);
}
if (!defined('T_VARIABLE')) {
    define('T_VARIABLE', 316);
}
if (!defined('T_EXTENDS')) {
    define('T_EXTENDS', 351);
}
if (!defined('T_INSTANCEOF')) {
    define('T_INSTANCEOF', 364);
}
if (!defined('T_INSTEADOF')) {
    define('T_INSTEADOF', 363);
}
if (!defined('T_IMPLEMENTS')) {
    define('T_IMPLEMENTS', 352);
}
if (!defined('T_NEW')) {
    define('T_NEW', 350);
}
if (!defined('T_CATCH')) {
    define('T_CATCH', 339);
}
if (!defined('T_FUNCTION')) {
    define('T_FUNCTION', 334);
}
if (!defined('T_OPEN_TAG')) {
    define('T_OPEN_TAG', 367);
}
if (!defined('T_OPEN_TAG_WITH_ECHO')) {
    define('T_OPEN_TAG_WITH_ECHO', 368);
}
if (!defined('T_USE')) {
    define('T_USE', 357);
}

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ClassyAnalyzer
{
    public function isClassyInvocation(Tokens $tokens, int $index): bool
    {
        $token = $tokens[$index];

        if (!isset($token) || !method_exists($token, 'isGivenKind') || !$token->isGivenKind(T_STRING)) {
            throw new \LogicException(sprintf('No T_STRING at given index %d, got "%s".', $index, isset($tokens[$index]) && method_exists($tokens[$index], 'getName') ? $tokens[$index]->getName() : 'unknown'));
        }

        if (isset($token) && method_exists($token, 'getContent') && (new Analysis\TypeAnalysis($token->getContent()))->isReservedType()) {
            return false;
        }

        $next = $tokens->getNextMeaningfulToken($index);
        $nextToken = $tokens[$next];

        if (isset($nextToken) && method_exists($nextToken, 'isGivenKind') && $nextToken->isGivenKind(T_NS_SEPARATOR)) {
            return false;
        }

        if (isset($nextToken) && method_exists($nextToken, 'isGivenKind') && $nextToken->isGivenKind([T_DOUBLE_COLON, T_ELLIPSIS, CT::T_TYPE_ALTERNATION, CT::T_TYPE_INTERSECTION, T_VARIABLE])) {
            return true;
        }

        $prev = $tokens->getPrevMeaningfulToken($index);

        while (isset($tokens[$prev]) && method_exists($tokens[$prev], 'isGivenKind') && $tokens[$prev]->isGivenKind([CT::T_NAMESPACE_OPERATOR, T_NS_SEPARATOR, T_STRING])) {
            $prev = $tokens->getPrevMeaningfulToken($prev);
        }

        $prevToken = $tokens[$prev];

        if (isset($prevToken) && method_exists($prevToken, 'isGivenKind') && $prevToken->isGivenKind([T_EXTENDS, T_INSTANCEOF, T_INSTEADOF, T_IMPLEMENTS, T_NEW, CT::T_NULLABLE_TYPE, CT::T_TYPE_ALTERNATION, CT::T_TYPE_INTERSECTION, CT::T_TYPE_COLON, CT::T_USE_TRAIT])) {
            return true;
        }

        if (\PHP_VERSION_ID >= 8_00_00 && isset($nextToken) && method_exists($nextToken, 'equals') && $nextToken->equals(')') && isset($prevToken) && method_exists($prevToken, 'equals') && $prevToken->equals('(') && isset($tokens[$tokens->getPrevMeaningfulToken($prev)]) && method_exists($tokens[$tokens->getPrevMeaningfulToken($prev)], 'isGivenKind') && $tokens[$tokens->getPrevMeaningfulToken($prev)]->isGivenKind(T_CATCH)) {
            return true;
        }

        if (AttributeAnalyzer::isAttribute($tokens, $index)) {
            return true;
        }

        // `Foo & $bar` could be:
        //   - function reference parameter: function baz(Foo & $bar) {}
        //   - bit operator: $x = Foo & $bar;
        if (isset($nextToken) && method_exists($nextToken, 'equals') && $nextToken->equals('&') && isset($tokens[$tokens->getNextMeaningfulToken($next)]) && method_exists($tokens[$tokens->getNextMeaningfulToken($next)], 'isGivenKind') && $tokens[$tokens->getNextMeaningfulToken($next)]->isGivenKind(T_VARIABLE)) {
            $checkIndex = $tokens->getPrevTokenOfKind($prev + 1, [';', '{', '}', [T_FUNCTION], [T_OPEN_TAG], [T_OPEN_TAG_WITH_ECHO]]);

            return isset($tokens[$checkIndex]) && method_exists($tokens[$checkIndex], 'isGivenKind') && $tokens[$checkIndex]->isGivenKind(T_FUNCTION);
        }

        if (!isset($prevToken) || !method_exists($prevToken, 'equals') || !$prevToken->equals(',')) {
            return false;
        }

        do {
            $prev = $tokens->getPrevMeaningfulToken($prev);
        } while (isset($tokens[$prev]) && method_exists($tokens[$prev], 'equalsAny') && $tokens[$prev]->equalsAny([',', [T_NS_SEPARATOR], [T_STRING], [CT::T_NAMESPACE_OPERATOR]]));

        return isset($tokens[$prev]) && method_exists($tokens[$prev], 'isGivenKind') && $tokens[$prev]->isGivenKind([T_IMPLEMENTS, CT::T_USE_TRAIT]);
    }
}
