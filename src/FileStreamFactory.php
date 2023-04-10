<?php

namespace Alfresco;

use function Safe\fclose;
use function Safe\fopen;
use function Safe\fwrite;
use function Safe\mkdir;

class FileStreamFactory
{
    protected array $buffer = [];

    public function make(string $path, int $chunk): Stream
    {
        return new Stream(
            $path,
            function (string $path) use ($chunk) {
                if (! file_exists(dirname($path))) {
                    mkdir(dirname($path), recursive: true);
                }

                return with(fopen($path, 'w'), fn ($file) => [
                    function (string $content) use ($file, $chunk) {
                        $this->buffer[] = $content;

                        if (count($this->buffer) >= $chunk) {
                            $this->flushBuffer($file);
                        }
                    },
                    function () use ($file) {
                        $this->flushBuffer($file);

                        fclose($file);
                    },
                ]);
            },
        );
    }

    protected function flushBuffer($file): void
    {
        if ($this->buffer === []) {
            return;
        }

        fwrite($file, implode('', $this->buffer));

        $this->buffer = [];
    }
}
