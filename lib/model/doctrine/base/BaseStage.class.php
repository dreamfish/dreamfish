<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Stage', 'doctrine');

/**
 * BaseStage
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $name
 * @property timestamp $created_at
 * @property timestamp $updated_at
 * @property Doctrine_Collection $Stage
 * 
 * @method integer             getId()         Returns the current record's "id" value
 * @method string              getName()       Returns the current record's "name" value
 * @method timestamp           getCreatedAt()  Returns the current record's "created_at" value
 * @method timestamp           getUpdatedAt()  Returns the current record's "updated_at" value
 * @method Doctrine_Collection getStage()      Returns the current record's "Stage" collection
 * @method Stage               setId()         Sets the current record's "id" value
 * @method Stage               setName()       Sets the current record's "name" value
 * @method Stage               setCreatedAt()  Sets the current record's "created_at" value
 * @method Stage               setUpdatedAt()  Sets the current record's "updated_at" value
 * @method Stage               setStage()      Sets the current record's "Stage" collection
 * 
 * @package    dfmarketplace
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseStage extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('stage');
        $this->hasColumn('id', 'integer', 8, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
             'length' => '8',
             ));
        $this->hasColumn('name', 'string', 255, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => '255',
             ));
        $this->hasColumn('created_at', 'timestamp', 25, array(
             'type' => 'timestamp',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => '25',
             ));
        $this->hasColumn('updated_at', 'timestamp', 25, array(
             'type' => 'timestamp',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => '25',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('Project as Stage', array(
             'local' => 'id',
             'foreign' => 'stage_id'));
    }
}