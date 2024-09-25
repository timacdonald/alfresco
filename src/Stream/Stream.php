<?php

declare(strict_types=1);

namespace Alfresco\Stream;

use Closure;
use RuntimeException;

class Stream
{
    /**
     * The strem's state.
     */
    protected State $state = State::Unopened;

    /**
     * The write handler.
     */
    protected ?Closure $write;

    /**
     * The close handler.
     */
    protected ?Closure $close;

    /**
     * Create a new instance.
     */
    public function __construct(
        public string $path,
        protected Closure $open,
    ) {
        //
    }

    /**
     * Write the the stream.
     */
    public function write(string $content): static
    {
        if ($this->state === State::Closed) {
            throw new RuntimeException('Unable to write. Stream is closed.');
        }

        if ($this->state === State::Unopened) {
            [$this->write, $this->close] = ($this->open)($this->path);

            $this->state = State::Open;
        }

        assert($this->write !== null);

        ($this->write)($content);

        return $this;
    }

    /**
     * Close the stream.
     */
    public function close(): void
    {
        if ($this->state === State::Closed) {
            throw new RuntimeException('Stream has already been closed.');
        }

        if ($this->state === State::Open) {
            assert($this->close !== null);

            ($this->close)();

            $this->write = $this->close = null;
        }

        $this->state = State::Closed;
    }
}
