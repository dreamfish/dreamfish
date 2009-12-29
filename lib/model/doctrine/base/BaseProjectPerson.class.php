<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('ProjectPerson', 'doctrine');

/**
 * BaseProjectPerson
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $person_id
 * @property integer $project_id
 * @property timestamp $created_at
 * @property timestamp $updated_at
 * @property Person $Person
 * @property Project $Project
 * 
 * @method integer       getPersonId()   Returns the current record's "person_id" value
 * @method integer       getProjectId()  Returns the current record's "project_id" value
 * @method timestamp     getCreatedAt()  Returns the current record's "created_at" value
 * @method timestamp     getUpdatedAt()  Returns the current record's "updated_at" value
 * @method Person        getPerson()     Returns the current record's "Person" value
 * @method Project       getProject()    Returns the current record's "Project" value
 * @method ProjectPerson setPersonId()   Sets the current record's "person_id" value
 * @method ProjectPerson setProjectId()  Sets the current record's "project_id" value
 * @method ProjectPerson setCreatedAt()  Sets the current record's "created_at" value
 * @method ProjectPerson setUpdatedAt()  Sets the current record's "updated_at" value
 * @method ProjectPerson setPerson()     Sets the current record's "Person" value
 * @method ProjectPerson setProject()    Sets the current record's "Project" value
 * 
 * @package    dfmarketplace
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseProjectPerson extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('project_person');
        $this->hasColumn('person_id', 'integer', 8, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => false,
             'length' => '8',
             ));
        $this->hasColumn('project_id', 'integer', 8, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => false,
             'length' => '8',
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
        $this->hasOne('Person', array(
             'local' => 'person_id',
             'foreign' => 'id'));

        $this->hasOne('Project', array(
             'local' => 'project_id',
             'foreign' => 'id'));
    }
}