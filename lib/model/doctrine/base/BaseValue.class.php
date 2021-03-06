<?php

/**
 * BaseValue
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $name
 * @property string $type
 * @property Doctrine_Collection $ProjectValues
 * 
 * @method integer             getId()            Returns the current record's "id" value
 * @method string              getName()          Returns the current record's "name" value
 * @method string              getType()          Returns the current record's "type" value
 * @method Doctrine_Collection getProjectValues() Returns the current record's "ProjectValues" collection
 * @method Value               setId()            Sets the current record's "id" value
 * @method Value               setName()          Sets the current record's "name" value
 * @method Value               setType()          Sets the current record's "type" value
 * @method Value               setProjectValues() Sets the current record's "ProjectValues" collection
 * 
 * @package    dfmarketplace
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseValue extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('value');
        $this->hasColumn('id', 'integer', null, array(
             'type' => 'integer',
             'primary' => true,
             ));
        $this->hasColumn('name', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
        $this->hasColumn('type', 'string', 255, array(
             'type' => 'string',
             'length' => '255',
             ));
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasMany('Project as ProjectValues', array(
             'refClass' => 'ProjectValue',
             'local' => 'value_id',
             'foreign' => 'project_id'));
    }
}