<?php

use ashch\sitecore\migrations\Migration;

/**
 * Class m191022_101731_template
 */
class m191022_101731_template extends Migration
{

    protected $table_id = 'template';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table_name, [
            'id' => $this->primaryKey()->unsigned()->notNull()->Comment('ID'),
            'slug' => $this->string(99)->Comment('Slug'),
            'type_id' => $this->integer(20)->unsigned()->defaultValue('1')->notNull()->Comment('Type'),
            'status' => $this->tinyInteger()->unsigned()->defaultValue('1')->Comment('Status'),
            'sorting' => $this->smallInteger()->defaultValue('0')->Comment('Sorting'),
            'name' => $this->string(255)->Comment('Name'),
            'image' => $this->string(255)->Comment('Image'),
            'description' => $this->string(255)->Comment('Description'),
            'content' => $this->text()->Comment('Content'),
            'created_by' => $this->integer()->unsigned()->Comment('Created by:'),
            'updated_by' => $this->integer()->unsigned()->Comment('Updated by:'),
            'created_at' => $this->integer()->unsigned()->Comment('Created at:'),
            'updated_at' => $this->integer()->unsigned()->Comment('Updated at:'),
        ]);
    }

}
