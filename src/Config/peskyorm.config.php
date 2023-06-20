<?php

declare(strict_types=1);

return [

    /**
     * Directory where DB classes are stored.
     * Used by OrmMakeDbClassesCommand command
     */
    'classes_path' => app_path('Db'),

    /**
     * Base namespace for DB classes
     * Used by OrmMakeDbClassesCommand command
     */
    'classes_namespace' => 'App\\Db',

    /**
     * Classes to use as parents for classes generated by OrmMakeDbClassesCommand command
     */
    'base_table_class' => \PeskyCMF\Db\CmfDbTable::class,
    'base_record_class' => \PeskyCMF\Db\CmfDbRecord::class,
    'base_table_structure_class' => \PeskyCMF\Db\CmfDbTableStructure::class,

    /**
     * DB classes builder class used to generate DB classes by table name
     * Used by OrmMakeDbClassesCommand command
     */
    'class_builder' => \PeskyORM\ORM\ClassBuilder\ClassBuilder::class,
];
