<?php

namespace Alfresco;

use Illuminate\Support\Stringable;

class Output
{
    /**
     * The index in the rainbox.
     */
    protected int $rainboxIndex = 50;

    /**
     * Indicates a line has been written.
     */
    protected bool $lineWritten = true;

    /**
     * Write the given string.
     */
    public function write(string $message): Output
    {
        $this->lineWritten = false;

        echo str($message)
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
            ], [
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
            ])
            ->whenContains(['<🌈>', '</🌈>'], function (Stringable $message) {
                $result = str('');

                while ($message->contains('<🌈>')) {
                    $result = $result->append($message->before('<🌈>'));

                    $inner = $message->after('<🌈>')->before('</🌈>');

                    $result = $result->append($this->🌈($inner->value(), 0.3));

                    $message = $message->after('</🌈>');
                }

                return $result->append($message);
            });

        return $this;
    }

    /**
     * Write the given line.
     */
    public function line(string $message = ''): Output
    {
        if (! $this->lineWritten) {
            $message = PHP_EOL.$message;
        }

        $this->write($message.PHP_EOL);

        $this->lineWritten = true;

        return $this;
    }

    /**
     * Convert to rainbow output.
     *
     * @source https://github.com/busyloop/lolcat
     */
    protected function 🌈(string $message, float $frequency): string
    {
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
