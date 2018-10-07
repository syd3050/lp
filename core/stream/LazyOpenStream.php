<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-7
 * Time: 上午10:44
 */

namespace core\stream;


use core\traits\StreamDecoratorTrait;
use Psr\Http\Message\StreamInterface;

class LazyOpenStream implements StreamInterface
{
    use StreamDecoratorTrait;

    /** @var string File to open */
    private $filename;
    /** @var string $mode */
    private $mode;
    /**
     * @param string $filename File to lazily open
     * @param string $mode     fopen mode to use when opening the stream
     */
    public function __construct($filename, $mode)
    {
        $this->filename = $filename;
        $this->mode = $mode;
    }
    /**
     * Creates the underlying stream lazily when required.
     *
     * @return StreamInterface
     */
    protected function createStream()
    {
        return stream_for(try_fopen($this->filename, $this->mode));
    }
}