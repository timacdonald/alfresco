<?php

namespace Alfresco\Stream;

use Alfresco\StreamState;
use Closure;
use RuntimeException;

class Stream
{
    /**
     * The strem's state.
     */
    protected StreamState $state = StreamState::Unopened;

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
        if ($this->state === StreamState::Closed) {
            throw new RuntimeException('Unable to write. Stream is closed.');
        }

        if ($this->state === StreamState::Unopened) {
            [$this->write, $this->close] = ($this->open)($this->path);

            $this->state = StreamState::Open;
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
        if ($this->state === StreamState::Closed) {
            throw new RuntimeException('Stream has already been closed.');
        }

        if ($this->state === StreamState::Open) {
            assert($this->close !== null);

            ($this->close)();

            $this->write = $this->close = null;
        }

        $this->state = StreamState::Closed;
    }
}
