<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold\Form;

use PeskyORMColumns\Column\Files\Utils\MimeTypesHelper;

class FilesUploaderConfig
{
    
    public int $maxFileSizeKb = 20480;
    public int $minFilesCount = 0;
    public int $maxFilesCount = 1;
    
    protected array $allowedMimeTypes = [
        MimeTypesHelper::JPEG,
        MimeTypesHelper::PNG,
    ];
    
    protected array $allowedFilesExtensions = [
        'jpg',
        'jpeg',
        'png',
    ];
    
    protected array $additionalFilesExtensions = [];
    
    public function setAllowedFilesExtensions(array $extensions): FilesUploaderConfig
    {
        $this->allowedFilesExtensions = $this->normalizeMimesAndExtensions($extensions);
        $this->allowedMimeTypes = [];
        foreach ($this->allowedFilesExtensions as $extension) {
            $mimeType = MimeTypesHelper::getMimeTypeForExtension($extension);
            if (!$mimeType) {
                $this->allowedMimeTypes[] = $mimeType;
            }
        }
        $this->allowedMimeTypes = array_unique($this->allowedMimeTypes);
        return $this;
    }
    
    public function addAllowedFilesExtensions(array $extensions): FilesUploaderConfig
    {
        $this->additionalFilesExtensions = $this->normalizeMimesAndExtensions($extensions);
        return $this;
    }
    
    public function getAllowedFilesExtensions(): array
    {
        return array_unique(array_merge($this->allowedFilesExtensions, $this->additionalFilesExtensions));
    }
    
    public function setAllowedMimeTypes(array $mimeTypes): FilesUploaderConfig
    {
        $this->allowedMimeTypes = $this->normalizeMimesAndExtensions($mimeTypes);
        $this->allowedFilesExtensions = [];
        foreach ($mimeTypes as $mimeType) {
            $ext = MimeTypesHelper::getExtensionForMimeType($mimeType);
            if ($ext) {
                $this->allowedFilesExtensions[] = $ext;
            }
            if ($mimeType === MimeTypesHelper::JPEG) {
                $this->allowedFilesExtensions[] = 'jpeg'; //< special case
            }
        }
        $this->allowedFilesExtensions = array_unique($this->allowedFilesExtensions);
        return $this;
    }
    
    public function getAllowedMimeTypes(): array
    {
        return $this->allowedMimeTypes;
    }
    
    protected function normalizeMimesAndExtensions(array $items): array
    {
        $items = array_filter($items, function ($value) {
            return is_string($value) && !empty(trim($value));
        });
        array_walk($items, function ($value) {
            return mb_strtolower(trim($value));
        });
        return array_unique($items);
    }
    
    public function toArray(string $inputId): array
    {
        return [
            'id' => $inputId,
            'min_files_count' => $this->minFilesCount,
            'max_files_count' => $this->maxFilesCount,
            'max_file_size' => $this->maxFileSizeKb,
            'allowed_extensions' => $this->getAllowedFilesExtensions(),
            'allowed_mime_types' => $this->getAllowedMimeTypes(),
        ];
    }
}