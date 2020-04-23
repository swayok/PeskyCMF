<?php

namespace PeskyCMF\Db;

use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Contracts\Queue\EntityNotFoundException;
use PeskyORM\DbObject;
use ReflectionClass;
use ReflectionProperty;

trait SerializesDbObjects {

    /**
     * Prepare the instance for serialization.
     *
     * @return array
     */
    public function __sleep() {
        $properties = (new ReflectionClass($this))->getProperties();

        foreach ($properties as $property) {
            $property->setValue($this, $this->getSerializedPropertyValue(
                $this->getPropertyValue($property)
            ));
        }

        return array_map(function ($p) {
            return $p->getName();
        }, $properties);
    }

    /**
     * Restore the model after serialization.
     *
     * @return void
     */
    public function __wakeup() {
        foreach ((new ReflectionClass($this))->getProperties() as $property) {
            $property->setValue($this, $this->getRestoredPropertyValue(
                $this->getPropertyValue($property)
            ));
        }
    }

    /**
     * Get the property value prepared for serialization.
     *
     * @param  mixed $value
     * @return mixed
     */
    protected function getSerializedPropertyValue($value) {
        return $value instanceof DbObject
            ? new ModelIdentifier(get_class($value), $value->_getPkValue()) : $value;
    }

    /**
     * Get the restored property value after deserialization.
     *
     * @param  mixed $value
     * @return mixed
     */
    protected function getRestoredPropertyValue($value) {
        if ($value instanceof ModelIdentifier){
            /** @var DbObject $class */
            $class = $value->class;
            $dbObject = $class::read($value->id);
            if (!$dbObject->exists()) {
                throw new EntityNotFoundException($value->class, $value->id);
            }
            return $dbObject;
        }
        return $value;
    }

    /**
     * Get the property value for the given property.
     *
     * @param  \ReflectionProperty $property
     * @return mixed
     */
    protected function getPropertyValue(ReflectionProperty $property) {
        $property->setAccessible(true);

        return $property->getValue($this);
    }
}
