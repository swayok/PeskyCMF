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
     * @param string|null $key - string: get value for a key or default one | null: get all data
     * @param mixed $default
     * @return array|mixed
     */
    public function getData(string $key = null, $default = null)
    {
        return $key === null ? $this->data : Arr::get($this->data, $key, $default);
    }
    
    /**
     * @param array $data
     * @return static
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }
    
    /**
     * @param string|int|float $key
     * @param mixed $value
     * @return static
     * @throws ScaffoldException
     */
    public function addData($key, $value)
    {
        if (empty($key)) {
            throw new ScaffoldException('$key cannot be empty');
        }
        $this->data[$key] = $value;
        return $this;
    }
    
    /**
     * @return static
     */
    public function mergeData(array $data)
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }
    
    public function getTemplate(): ?string
    {
        return $this->template;
    }
    
    /**
     * @return static
     */
    public function setTemplate(string $template)
    {
        $this->template = $template;
        return $this;
    }
    
    public function hasTemplate(): bool
    {
        return !empty($this->template);
    }
    
}
