<?php declare(strict_types=1);
/*
 * This file is part of sebastian/cli-parser.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace SebastianBergmann\CliParser;

use PHPUnit\Framework\TestCase;

/**
 * @covers \SebastianBergmann\CliParser\Parser
 * @covers \SebastianBergmann\CliParser\AmbiguousOptionException
 * @covers \SebastianBergmann\CliParser\OptionDoesNotAllowArgumentException
 * @covers \SebastianBergmann\CliParser\RequiredOptionArgumentMissingException
 * @covers \SebastianBergmann\CliParser\UnknownOptionException
 */
final class ParserTest extends TestCase
{
    public function testParsesShortOptionsWithOptionalValues(): void
    {
        $this->assertSame(
            [
                [
                    [
                        'f',
                        null,
                    ],
                ],
                [
                    'myArgument',
                ],
            ],
            (new Parser)->parse(
                [
                    'command',
                    'myArgument',
                    '-f',
                ],
                'f::'
            )
        );
    }

    public function testParsesLongOptionsWithValues(): void
    {
        $this->assertSame(
            [
                [
                    ['--exec', null],
                    ['--conf', 'config.xml'],
                    ['--optn', null],
                    ['--optn', 'content-of-o'],
                ],
                [
                    'parameter-0',
                    'parameter-1',
                    'parameter-2',
                    'parameter-n',
                ],
            ],
            (new Parser)->parse(
                [
                    'command',
                    'parameter-0',
                    '--exec',
                    'parameter-1',
                    '--conf',
                    'config.xml',
                    '--optn',
                    'parameter-2',
                    '--optn=content-of-o',
                    'parameter-n',
                ],
                '',
                ['exec', 'conf=', 'optn==']
            )
        );
    }

    public function testParsesShortongOptionsWithValues(): void
    {
        $this->assertSame(
            [
                [
                    ['x', null],
                    ['c', 'config.xml'],
                    ['o', null],
                    ['o', 'content-of-o'],
                ],
                [
                    'parameter-0',
                    'parameter-1',
                    'parameter-2',
                    'parameter-n',
                ],
            ],
            (new Parser)->parse(
                [
                    'command',
                    'parameter-0',
                    '-x',
                    'parameter-1',
                    '-c',
                    'config.xml',
                    '-o',
                    'parameter-2',
                    '-ocontent-of-o',
                    'parameter-n',
                ],
                'xc:o::'
            )
        );
    }

    public function testParsesLongOptionsAfterArguments(): void
    {
        $this->assertSame(
            [
                [
                    [
                        '--colors',
                        null,
                    ],
                ],
                [
                    'myArgument',
                ],
            ],
            (new Parser)->parse(
                [
                    'command',
                    'myArgument',
                    '--colors',
                ],
                '',
                ['colors==']
            )
        );
    }

    public function testParsesShortOptionsAfterArguments(): void
    {
        $this->assertSame(
            [
                [
                    [
                        'v',
                        null,
                    ],
                ],
                [
                    'myArgument',
                ],
            ],
            (new Parser)->parse(
                [
                    'command',
                    'myArgument',
                    '-v',
                ],
                'v'
            )
        );
    }

    public function testReturnsEmptyResultWhenNotOptionsArePassed(): void
    {
        $this->assertSame(
            [
                [],
                [],
            ],
            (new Parser)->parse(
                [],
                'v'
            )
        );
    }

    public function testRaisesAnExceptionForUnknownLongOption(): void
    {
        $this->expectException(UnknownOptionException::class);
        $this->expectExceptionMessage('Unknown option "--foo"');

        /* @noinspection UnusedFunctionResultInspection */
        (new Parser)->parse(
            [
                'command',
                '--foo',
            ],
            '',
            ['colors']
        );
    }

    public function testRaisesAnExceptionForUnknownShortOption(): void
    {
        $this->expectException(UnknownOptionException::class);
        $this->expectExceptionMessage('Unknown option "-v"');

        /* @noinspection UnusedFunctionResultInspection */
        (new Parser)->parse(
            [
                'command',
                'myArgument',
                '-v',
            ],
            ''
        );
    }

    public function testRaisesAnExceptionWhenRequiredArgumentForLongOptionIsMissing(): void
    {
        $this->expectException(RequiredOptionArgumentMissingException::class);
        $this->expectExceptionMessage('Required argument for option "--foo" is missing');

        /* @noinspection UnusedFunctionResultInspection */
        (new Parser)->parse(
            [
                'command',
                '--foo',
            ],
            '',
            ['foo=']
        );
    }

    public function testRaisesAnExceptionWhenRequiredArgumentForShortOptionIsMissing(): void
    {
        $this->expectException(RequiredOptionArgumentMissingException::class);
        $this->expectExceptionMessage('Required argument for option "-f" is missing');

        /* @noinspection UnusedFunctionResultInspection */
        (new Parser)->parse(
            [
                'command',
                'myArgument',
                '-f',
            ],
            'f:'
        );
    }

    public function testRaisesAnExceptionWhenLongOptionIsAmbiguous(): void
    {
        $this->expectException(AmbiguousOptionException::class);
        $this->expectExceptionMessage('Option "--col" is ambiguous');

        /* @noinspection UnusedFunctionResultInspection */
        (new Parser)->parse(
            [
                'command',
                '--col',
            ],
            '',
            ['columns', 'colors']
        );
    }

    public function testRaisesAnExceptionWhenAnArgumentIsGivenForLongOptionThatDoesNotAllowAnArgument(): void
    {
        $this->expectException(OptionDoesNotAllowArgumentException::class);
        $this->expectExceptionMessage('Option "--foo" does not allow an argument');

        /* @noinspection UnusedFunctionResultInspection */
        (new Parser)->parse(
            [
                'command',
                '--foo=bar',
            ],
            '',
            ['foo']
        );
    }
}
