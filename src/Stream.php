<?php

namespace Alfresco;

use Closure;
use RuntimeException;

class Stream
{
    protected StreamState $state = StreamState::Unopened;

    protected ?Closure $write;

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

    public function write(string $content): static
    {
        if ($this->state === StreamState::Closed) {
            throw new RuntimeException('Unable to write. Stream is closed.');
        }

        if ($this->state === StreamState::Unopened) {
            [$this->write, $this->close] = ($this->open)($this->path);

            $this->state = StreamState::Open;
        }

        ($this->write)($content);

        return $this;
    }

    public function close(): void
    {
        if ($this->state === StreamState::Closed) {
            throw new RuntimeException('Stream has already been closed.');
        }

        if ($this->state === StreamState::Open) {
            ($this->close)();

            $this->write = $this->close = null;
        }

        $this->state = StreamState::Closed;
    }
}
