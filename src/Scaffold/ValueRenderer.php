<?php

declare(strict_types=1);

namespace PeskyCMF\Scaffold;

use Illuminate\Support\Arr;

abstract class ValueRenderer
{
    
    protected ?string $template = null;
    protected array $data = [];
    
    public static function create(?string $view = null): ValueRenderer
    {
        return new static($view);
    }
    
    public function __construct(?string $view = null)
    {
        if (!empty($view)) {
            $this->template = $view;
        }
    }
    
    /**
     * @param float|int|string|null $key - string: get value for a key or default one | null: get all data
     * @param mixed|null $default
     * @return mixed
     */
    public function getData(float|int|string $key = null, mixed $default = null): mixed
    {
        return $key === null ? $this->data : Arr::get($this->data, $key, $default);
    }
    
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }
    
    /**
     * @throws \InvalidArgumentException
     */
    public function addData(float|int|string $key, mixed $value): static
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('$key argument value cannot be empty');
        }
        $this->data[$key] = $value;
        return $this;
    }
    
    public function mergeData(array $data): static
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }
    
    public function getTemplate(): ?string
    {
        return $this->template;
    }
    
    public function setTemplate(string $template): static
    {
        $this->template = $template;
        return $this;
    }
    
    public function hasTemplate(): bool
    {
        return !empty($this->template);
    }
    
}
