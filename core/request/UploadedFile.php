<?php
/**
 * Created by PhpStorm.
 * User: syd
 * Date: 18-10-6
 * Time: 下午10:34
 */

namespace core\request;


use core\stream\LazyOpenStream;
use core\stream\Stream;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{
    /**
     * @var int[]
     */
    private static $errors = [
        UPLOAD_ERR_OK,
        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE,
        UPLOAD_ERR_PARTIAL,
        UPLOAD_ERR_NO_FILE,
        UPLOAD_ERR_NO_TMP_DIR,
        UPLOAD_ERR_CANT_WRITE,
        UPLOAD_ERR_EXTENSION,
    ];
    /**
     * @var string
     */
    private $clientFilename;
    /**
     * @var string
     */
    private $clientMediaType;
    /**
     * @var int
     */
    private $error;
    /**
     * @var null|string
     */
    private $file;
    /**
     * @var bool
     */
    private $moved = false;
    /**
     * @var int
     */
    private $size;
    /**
     * @var StreamInterface|null
     */
    private $stream;
    /**
     * @param StreamInterface|string|resource $streamOrFile
     * @param int $size
     * @param int $errorStatus
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     */
    public function __construct(
        $streamOrFile,
        $size,
        $errorStatus,
        $clientFilename = null,
        $clientMediaType = null
    ) {
        $this->setError($errorStatus);
        $this->setSize($size);
        $this->setClientFilename($clientFilename);
        $this->setClientMediaType($clientMediaType);
        if ($this->isOk()) {
            $this->setStreamOrFile($streamOrFile);
        }
    }

    /**
     * Depending on the value set file or stream variable
     *
     * @param mixed $streamOrFile
     * @throws \InvalidArgumentException
     */
    private function setStreamOrFile($streamOrFile)
    {
        if (is_string($streamOrFile)) {
            $this->file = $streamOrFile;
        } elseif (is_resource($streamOrFile)) {
            $this->stream = new Stream($streamOrFile);
        } elseif ($streamOrFile instanceof StreamInterface) {
            $this->stream = $streamOrFile;
        } else {
            throw new \InvalidArgumentException(
                'Invalid stream or file provided for UploadedFile'
            );
        }
    }
    /**
     * @param int $error
     * @throws \InvalidArgumentException
     */
    private function setError($error)
    {
        if (false === is_int($error)) {
            throw new \InvalidArgumentException(
                'Upload file error status must be an integer'
            );
        }
        if (false === in_array($error, UploadedFile::$errors)) {
            throw new \InvalidArgumentException(
                'Invalid error status for UploadedFile'
            );
        }
        $this->error = $error;
    }
    /**
     * @param int $size
     * @throws \InvalidArgumentException
     */
    private function setSize($size)
    {
        if (false === is_int($size)) {
            throw new \InvalidArgumentException(
                'Upload file size must be an integer'
            );
        }
        $this->size = $size;
    }
    /**
     * @param mixed $param
     * @return boolean
     */
    private function isStringOrNull($param)
    {
        return in_array(gettype($param), ['string', 'NULL']);
    }
    /**
     * @param mixed $param
     * @return boolean
     */
    private function isStringNotEmpty($param)
    {
        return is_string($param) && false === empty($param);
    }
    /**
     * @param string|null $clientFilename
     * @throws \InvalidArgumentException
     */
    private function setClientFilename($clientFilename)
    {
        if (false === $this->isStringOrNull($clientFilename)) {
            throw new \InvalidArgumentException(
                'Upload file client filename must be a string or null'
            );
        }
        $this->clientFilename = $clientFilename;
    }
    /**
     * @param string|null $clientMediaType
     * @throws \InvalidArgumentException
     */
    private function setClientMediaType($clientMediaType)
    {
        if (false === $this->isStringOrNull($clientMediaType)) {
            throw new \InvalidArgumentException(
                'Upload file client media type must be a string or null'
            );
        }
        $this->clientMediaType = $clientMediaType;
    }
    /**
     * Return true if there is no upload error
     *
     * @return boolean
     */
    private function isOk()
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    /**
     * @throws \RuntimeException if is moved or not ok
     */
    private function validateActive()
    {
        if (false === $this->isOk()) {
            throw new \RuntimeException('Cannot retrieve stream due to upload error');
        }
        if ($this->isMoved()) {
            throw new \RuntimeException('Cannot retrieve stream after it has already been moved');
        }
    }

    /**
     * @return boolean
     */
    public function isMoved()
    {
        return $this->moved;
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * This method MUST return a StreamInterface instance, representing the
     * uploaded file. The purpose of this method is to allow utilizing native PHP
     * stream functionality to manipulate the file upload, such as
     * stream_copy_to_stream() (though the result will need to be decorated in a
     * native PHP stream wrapper to work with such functions).
     *
     * If the moveTo() method has been called previously, this method MUST raise
     * an exception.
     *
     * @return StreamInterface Stream representation of the uploaded file.
     * @throws \RuntimeException in cases when no stream is available or can be
     *     created.
     */
    public function getStream()
    {
        // TODO: Implement getStream() method.
        $this->validateActive();
        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }
        return new LazyOpenStream($this->file, 'r+');
    }

    /**
     * Move the uploaded file to a new location.
     *
     * Use this method as an alternative to move_uploaded_file(). This method is
     * guaranteed to work in both SAPI and non-SAPI environments.
     * Implementations must determine which environment they are in, and use the
     * appropriate method (move_uploaded_file(), rename(), or a stream
     * operation) to perform the operation.
     *
     * $targetPath may be an absolute path, or a relative path. If it is a
     * relative path, resolution should be the same as used by PHP's rename()
     * function.
     *
     * The original file or stream MUST be removed on completion.
     *
     * If this method is called more than once, any subsequent calls MUST raise
     * an exception.
     *
     * When used in an SAPI environment where $_FILES is populated, when writing
     * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
     * used to ensure permissions and upload status are verified correctly.
     *
     * If you wish to move to a stream, use getStream(), as SAPI operations
     * cannot guarantee writing to stream destinations.
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     * @param string $targetPath Path to which to move the uploaded file.
     * @throws \\InvalidArgumentException if the $targetPath specified is invalid.
     * @throws \RuntimeException on any error during the move operation, or on
     *     the second or subsequent call to the method.
     */
    public function moveTo($targetPath)
    {
        // TODO: Implement moveTo() method.
        $this->validateActive();
        if (false === $this->isStringNotEmpty($targetPath)) {
            throw new \InvalidArgumentException(
                'Invalid path provided for move operation; must be a non-empty string'
            );
        }
        if ($this->file) {
            $this->moved = php_sapi_name() == 'cli'
                ? rename($this->file, $targetPath)
                : move_uploaded_file($this->file, $targetPath);
        } else {
            copy_to_stream(
                $this->getStream(),
                new LazyOpenStream($targetPath, 'w')
            );
            $this->moved = true;
        }
        if (false === $this->moved) {
            throw new \RuntimeException(
                sprintf('Uploaded file could not be moved to %s', $targetPath)
            );
        }
    }

    /**
     * Retrieve the file size.
     *
     * Implementations SHOULD return the value stored in the "size" key of
     * the file in the $_FILES array if available, as PHP calculates this based
     * on the actual size transmitted.
     *
     * @return int|null The file size in bytes or null if unknown.
     */
    public function getSize()
    {
        // TODO: Implement getSize() method.
        return $this->size;
    }

    /**
     * Retrieve the error associated with the uploaded file.
     *
     * The return value MUST be one of PHP's UPLOAD_ERR_XXX constants.
     *
     * If the file was uploaded successfully, this method MUST return
     * UPLOAD_ERR_OK.
     *
     * Implementations SHOULD return the value stored in the "error" key of
     * the file in the $_FILES array.
     *
     * @see http://php.net/manual/en/features.file-upload.errors.php
     * @return int One of PHP's UPLOAD_ERR_XXX constants.
     */
    public function getError()
    {
        // TODO: Implement getError() method.
        return $this->error;
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "name" key of
     * the file in the $_FILES array.
     *
     * @return string|null The filename sent by the client or null if none
     *     was provided.
     */
    public function getClientFilename()
    {
        // TODO: Implement getClientFilename() method.
        return $this->clientFilename;
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious media type with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "type" key of
     * the file in the $_FILES array.
     *
     * @return string|null The media type sent by the client or null if none
     *     was provided.
     */
    public function getClientMediaType()
    {
        // TODO: Implement getClientMediaType() method.
        return $this->clientMediaType;
    }
}