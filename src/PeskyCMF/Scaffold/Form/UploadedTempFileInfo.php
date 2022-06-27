<?php

namespace PeskyCMF\Scaffold\Form;

use App\Db\DbFileInfo;
use App\Db\DbFileInfoV2;
use App\Db\DbImageFileInfo;
use App\Db\DbImageFileInfoV2;
use Illuminate\Http\UploadedFile;
use PeskyORM\ORM\RecordInterface;
use PeskyORMLaravel\Db\Column\Utils\FileConfig;
use PeskyORMLaravel\Db\Column\Utils\FileInfo;
use Ramsey\Uuid\Uuid;
use Swayok\Utils\File;
use Swayok\Utils\Folder;

class UploadedTempFileInfo extends \SplFileInfo {

    protected $name;
    protected $type;
    protected $relativePath;
    protected $realPath;
    protected $isSaved = false;
    protected $isValid = true;
    protected $size;

    static public function getUploadsTempFolder(): string {
        return storage_path('app' . DIRECTORY_SEPARATOR . 'temp');
    }

    static public function getSubfolderName(): string {
        return date('Y-m-d');
    }

    /**
     * @param UploadedFile|string|array|DbFileInfo|DbFileInfoV2|DbImageFileInfo|DbImageFileInfoV2 $file
     * @param bool $save - true: if $file is UploadedFile or array - save it to disk
     * @param bool $makeCopy - true: create a copy of uploaded file and return it instead of original
     *      (use to create multiple records with same files attached)
     */
    public function __construct($file, bool $save = false, bool $makeCopy = false) {
        if (is_string($file)) {
            $this->decode($file);
        } else if (is_array($file)) {
            $this->name = $file['name'];
            $this->type = $file['type'];
            $this->realPath = $file['tmp_name'];
        } else if ($file instanceof DbFileInfo) {
            $this->name = $file->getOriginalFileNameWithExtension();
            $this->type = $file->getMimeType();
            if ($file instanceof DbImageFileInfo) {
                $this->realPath = $file->getFilePath(array_keys($file->getColumn()->getImageVersionsConfigs())[0]);
            } else {
                $this->realPath = $file->getFilePath();
            }
            $this->isSaved = true;
        } else {
            $this->name = $file->getClientOriginalName();
            $this->type = $file->getClientMimeType();
            $this->realPath = $file->getRealPath();
        }
        if (!$this->relativePath) {
            $this->relativePath = $this->makeRelativeFilePath();
        }
        if ($makeCopy) {
            $this->useCopiedFile();
        }
        if ($save) {
            $this->save();
        }
        parent::__construct($this->realPath);
    }
    
    /**
     * Replace real path by copied file real path returning modified instance
     */
    public function useCopiedFile() {
        $copiedFilePath = $this->realPath . '.' . microtime(true);
        File::load($this->getRealPath())->copy($copiedFilePath, true, 0666);
        $this->realPath = $copiedFilePath;
        return $this;
    }
    
    /**
     * Create a copy of this instance that uses a copy of original file
     */
    public function makeCopy() {
        $copy = clone $this;
        return $copy->useCopiedFile();
    }

    public function save() {
        if (!$this->isSaved) {
            $this->createSubfolder();
            $newRealPath = $this->makeAbsolutePath($this->getRelativePath());
            File::load($this->getRealPath())->move($newRealPath, 0666);
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

    public function toFileInfo(FileConfig $fileConfig, RecordInterface $record, ?string $fileSuffix): FileInfo {
        return FileInfo::fromUploadedTempFileInfo($this, $fileConfig, $record, $fileSuffix);
    }

    public function getName(): string {
        return $this->name;
    }
    
    public function getFilename(): string {
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

    public function getSize(): int {
        if (!isset($this->size)) {
            $this->size = filesize($this->getRealPath());
        }
        return $this->size;
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