<?php

declare(strict_types=1);

namespace Alfresco;

class Output
{
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
            ]);

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
}
