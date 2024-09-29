<?php

declare(strict_types=1);

namespace Alfresco\Stream;

use function Safe\fopen;
use function Safe\mkdir;
use function Safe\fclose;
use function Safe\fwrite;

class FileStreamFactory
{
    /**
     * The in-memory buffer.
     *
     * @var list<string>
     */
    protected array $buffer = [];

    /**
     * Make a new file stream.
     */
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

    /**
     * Flush the buffer to the stream.
     *
     * @param  resource  $file
     */
    protected function flushBuffer($file): void
    {
        if ($this->buffer === []) {
            return;
        }

        fwrite($file, implode('', $this->buffer));

        $this->buffer = [];
    }
}
