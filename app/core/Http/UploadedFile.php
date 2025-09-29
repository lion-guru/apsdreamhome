<?php

namespace App\Core\Http;

use RuntimeException;
use InvalidArgumentException;
use SplFileInfo;

class UploadedFile extends SplFileInfo
{
    /**
     * The original name of the uploaded file.
     *
     * @var string
     */
    private $clientOriginalName;

    /**
     * The MIME type of the uploaded file.
     *
     * @var string|null
     */
    private $clientMimeType;

    /**
     * The size of the uploaded file in bytes.
     *
     * @var int
     */
    private $size;

    /**
     * The error code associated with the file upload.
     *
     * @var int
     */
    private $error;

    /**
     * Whether the file was uploaded successfully.
     *
     * @var bool
     */
    private $moved = false;

    /**
     * Create a new uploaded file instance.
     *
     * @param string $path The temporary file path
     * @param string $originalName The original file name
     * @param string|null $mimeType The MIME type of the file
     * @param int|null $error The error code
     * @param bool $test Whether this is a test file (from PHP unit tests)
     */
    public function __construct(string $path, string $originalName, string $mimeType = null, int $error = null, bool $test = false)
    {
        if (!is_file($path) && !$test) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not exist', $path));
        }

        parent::__construct($path);
        $this->clientOriginalName = $originalName;
        $this->clientMimeType = $mimeType;
        $this->size = filesize($path);
        $this->error = $error ?? UPLOAD_ERR_OK;
    }

    /**
     * Get the original file name.
     *
     * @return string
     */
    public function getClientOriginalName(): string
    {
        return $this->clientOriginalName;
    }

    /**
     * Get the file extension.
     *
     * @return string
     */
    public function getClientOriginalExtension(): string
    {
        return pathinfo($this->clientOriginalName, PATHINFO_EXTENSION);
    }

    /**
     * Get the MIME type of the file.
     *
     * @return string
     */
    public function getClientMimeType(): ?string
    {
        if ($this->clientMimeType === null && $this->getRealPath()) {
            $this->clientMimeType = mime_content_type($this->getRealPath()) ?: null;
        }

        return $this->clientMimeType;
    }

    /**
     * Get the size of the file.
     *
     * @return int The file size in bytes
     */
    public function getSize(): int
    {
        return $this->size ?? parent::getSize();
    }

    /**
     * Get the error code.
     *
     * @return int
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * Get the error message.
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        static $errors = [
            UPLOAD_ERR_OK => 'There is no error, the file uploaded with success',
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
        ];

        return $errors[$this->error] ?? 'Unknown upload error';
    }

    /**
     * Check if the file was uploaded successfully.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->error === UPLOAD_ERR_OK && is_uploaded_file($this->getPathname());
    }

    /**
     * Move the uploaded file to a new location.
     *
     * @param string $directory The destination directory
     * @param string|null $name The new file name
     * @return self
     * @throws RuntimeException On any error during the move operation
     */
    public function move(string $directory, string $name = null): self
    {
        if ($this->moved) {
            throw new RuntimeException('The uploaded file has already been moved.');
        }

        if (!$this->isValid()) {
            throw new RuntimeException($this->getErrorMessage());
        }

        $target = $this->getTargetFile($directory, $name);
        $targetDirectory = dirname($target);

        if (!is_dir($targetDirectory)) {
            if (false === @mkdir($targetDirectory, 0777, true) && !is_dir($targetDirectory)) {
                throw new RuntimeException(sprintf('Unable to create the "%s" directory', $targetDirectory));
            }
        } elseif (!is_writable($targetDirectory)) {
            throw new RuntimeException(sprintf('Unable to write in the "%s" directory', $targetDirectory));
        }

        if (!@move_uploaded_file($this->getPathname(), $target)) {
            $error = error_get_last();
            throw new RuntimeException(sprintf('Could not move the file "%s" to "%s" (%s)', $this->getPathname(), $target, strip_tags($error['message'] ?? '')));
        }

        @chmod($target, 0666 & ~umask());

        $this->moved = true;

        return $this->setFile($target);
    }

    /**
     * Get the target file path.
     *
     * @param string $directory
     * @param string|null $name
     * @return string
     */
    private function getTargetFile(string $directory, string $name = null): string
    {
        if (!is_dir($directory)) {
            throw new RuntimeException(sprintf('The target directory "%s" does not exist', $directory));
        }

        if (!is_writable($directory)) {
            throw new RuntimeException(sprintf('The target directory "%s" is not writable', $directory));
        }

        $target = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . (null === $name ? $this->getBasename() : $name);
        
        return $target;
    }

    /**
     * Create a new instance for testing.
     *
     * @param string $path The file path
     * @param string $originalName The original file name
     * @param string|null $mimeType The MIME type
     * @param int|null $error The error code
     * @return static
     */
    public static function createFromPath(string $path, string $originalName, string $mimeType = null, int $error = null): self
    {
        return new static($path, $originalName, $mimeType, $error, true);
    }

    /**
     * Set the file.
     *
     * @param string $path The file path
     * @return $this
     */
    protected function setFile(string $path)
    {
        $this->setFileInfo(new static($path, $this->clientOriginalName, $this->clientMimeType, $this->error, true));
        $this->moved = false;
        
        return $this;
    }

    /**
     * Set the file info.
     *
     * @param static $file
     * @return void
     */
    protected function setFileInfo(self $file): void
    {
        $this->clientOriginalName = $file->getClientOriginalName();
        $this->clientMimeType = $file->getClientMimeType();
        $this->size = $file->getSize();
        $this->error = $file->getError();
    }

    /**
     * Get the file info as an array.
     *
     * @return array
     */
    public function getFileInfoArray(): array
    {
        return [
            'name' => $this->getClientOriginalName(),
            'type' => $this->getClientMimeType(),
            'tmp_name' => $this->getPathname(),
            'error' => $this->getError(),
            'size' => $this->getSize()
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function getFileInfo(string $class = null): \SplFileInfo
    {
        return parent::getFileInfo($class);
    }

    /**
     * Get the file contents.
     *
     * @throws RuntimeException If the file cannot be read
     */
    public function getContent(): string
    {
        if (false === $content = file_get_contents($this->getPathname())) {
            throw new RuntimeException(sprintf('Could not get the content of the file "%s"', $this->getPathname()));
        }

        return $content;
    }

    /**
     * Get the file as a data URL.
     *
     * @return string
     */
    public function getDataUrl(): string
    {
        return 'data:' . ($this->getClientMimeType() ?? 'application/octet-stream') . ';base64,' . base64_encode($this->getContent());
    }

    /**
     * Check if the file is an image.
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return strpos($this->getClientMimeType() ?? '', 'image/') === 0 && @is_array(getimagesize($this->getPathname()));
    }

    /**
     * Get the image dimensions.
     *
     * @return array|false
     */
    public function getImageSize()
    {
        return $this->isImage() ? getimagesize($this->getPathname()) : false;
    }

    /**
     * Get the image width.
     *
     * @return int|false
     */
    public function getImageWidth()
    {
        return $this->isImage() ? getimagesize($this->getPathname())[0] : false;
    }

    /**
     * Get the image height.
     *
     * @return int|false
     */
    public function getImageHeight()
    {
        return $this->isImage() ? getimagesize($this->getPathname())[1] : false;
    }
}
