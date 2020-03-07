<?php

namespace PeskyCMF\Scaffold\Form;

use Illuminate\Http\UploadedFile;
use Ramsey\Uuid\Uuid;
use Swayok\Utils\Folder;

class UploadedTempFileInfo {

    protected $name;
    protected $type;
    protected $relativePath;
    protected $realPath;
    protected $isSaved = false;
    protected $isValid = true;

    static public function getUploadsTempFolder(): string {
        return storage_path('app' . DIRECTORY_SEPARATOR . 'temp');
    }

    static public function getSubfolderName(): string {
        return date('Y-m-d');
    }

    /**
     * @param UploadedFile|string|array $file
     * @param bool $save - true: if $file is UploadedFile or array - save it to disk
     */
    public function __construct($file, bool $save = false) {
        if (is_string($file)) {
            $this->decode($file);
        } else if (is_array($file)) {
            $this->name = $file['name'];
            $this->type = $file['type'];
            $this->realPath = $file['tmp_name'];
        } else {
            $this->name = $file->getClientOriginalName();
            $this->type = $file->getClientMimeType();
            $this->realPath = $file->getRealPath();
        }
        if (!$this->relativePath) {
            $this->relativePath = $this->makeRelativeFilePath();
        }
        if ($save) {
            $this->save();
        }
    }

    public function save() {
        if (!$this->isSaved) {
            $this->createSubfolder();
            $newRealPath = $this->makeAbsolutePath($this->getRelativePath());
            move_uploaded_file($this->getRealPath(), $newRealPath);
            chmod($newRealPath, 0666);
            $this->realPath = $newRealPath;
        }
        return $this;
    }

    public function delete() {
        \File::delete($this->getRealPath());
        return $this;
    }

    public function toArray(): array {
        return [
            'name' => $this->getName(),
            'type' => $this->getType(),
            'relative_path' => $this->getRelativePath(),
            'absolute_path' => $this->getRealPath()
        ];
    }

    public function getName(): string {
        return $this->name;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getRelativePath(): string {
        return $this->relativePath;
    }

    public function getRealPath(): string {
        return $this->realPath;
    }

    public function isValid(): bool {
        return $this->isValid;
    }

    public function isImage(): bool {
        return preg_match('%^image/%', $this->getType());
    }

    public function encode(): string {
        return \Crypt::encrypt([
            'name' => $this->getName(),
            'type' => $this->getType(),
            'path' => $this->getRelativePath()
        ], true);
    }

    protected function decode(string $encodedData) {
        $data = \Crypt::decrypt($encodedData, true);
        if (is_array($data) && isset($data['name'], $data['type'], $data['path'])) {
            $this->name = $data['name'];
            $this->type = $data['type'];
            $this->relativePath = $data['path'];
            $this->realPath = $this->makeAbsolutePath($data['path']);
            $this->isSaved = true;
        } else {
            $this->isValid = false;
        }
    }

    protected function makeRelativeFilePath(): string {
        return '/' . static::getSubfolderName() . '/' . Uuid::uuid4()->toString() . '.tmp';
    }

    protected function createSubfolder(): void {
        Folder::load($this->makeAbsolutePath(static::getSubfolderName()), true, 0777);
    }

    protected function makeAbsolutePath(string $relativePath): string {
        return static::getUploadsTempFolder() . DIRECTORY_SEPARATOR . ltrim($relativePath, '/\\');
    }
}
