<?php

namespace Alfresco;

use Closure;
use Illuminate\Support\Stringable;

class Output
{
    protected int $rainboxIndex = 50;

    protected bool $lineWritten = true;

    public function __construct(
        protected Closure $write,
        protected bool $ansi,
    ) {
        //
    }

    public function __invoke(string $message): Output
    {
        $this->lineWritten = false;

        ($this->write)(str($message)
            ->replace([
                '<bold>',
                '</bold>',
                '<green>',
                '</green>',
                '<yellow>',
                '</yellow>',
                '<blue>',
                '</blue>',
                '<dim>',
                '</dim>',
            ], $this->ansi ? [
                "\033[1m", // bold
                "\033[22m",
                "\033[92m", // green
                "\033[39m",
                "\033[93m", // yellow
                "\033[39m",
                "\033[94m", // blue
                "\033[39m",
                "\033[2m", // dim
                "\033[22m",
            ] : '')
            ->whenContains(['<🌈>', '</🌈>'], function (Stringable $message) {
                $result = str('');

                while ($message->contains('<🌈>')) {
                    $result = $result->append($message->before('<🌈>'));

                    $inner = $message->after('<🌈>')->before('</🌈>');

                    $result = $result->append($this->🌈($inner->value(), 0.3));

                    $message = $message->after('</🌈>');
                }

                return $result->append($message);
            })
            ->value());

        return $this;
    }

    public function line(string $message = ''): Output
    {
        if (! $this->lineWritten) {
            $message = PHP_EOL.$message;
        }

        $this($message.PHP_EOL);

        $this->lineWritten = true;

        return $this;
    }

    /**
     * @source https://github.com/busyloop/lolcat
     */
    protected function 🌈(string $message, float $frequency): string
    {
        if (! $this->ansi) {
            return $message;
        }

        return collect(mb_str_split($message))
            ->map(function (string $character) use ($frequency) {
                $this->rainboxIndex++;

                return vsprintf("\033[38;2;%'.02d;%'.02d;%'.02dm%s\033[39m", [
                    (int) (sin($frequency * $this->rainboxIndex + 0) * 127) + 128,
                    (int) (sin($frequency * $this->rainboxIndex + (2 * M_PI / 3)) * 127) + 128,
                    (int) (sin($frequency * $this->rainboxIndex + (4 * M_PI / 3)) * 127) + 128,
                    $character,
                ]);
            })->implode('');
    }
}
