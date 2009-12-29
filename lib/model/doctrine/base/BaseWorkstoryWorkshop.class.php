<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('WorkstoryWorkshop', 'doctrine');

/**
 * BaseWorkstoryWorkshop
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $workstory_id
 * @property integer $workshop_id
 * @property timestamp $created_at
 * @property timestamp $updated_at
 * @property Workshop $Workshop
 * @property Workstory $Workstory
 * 
 * @method integer           getWorkstoryId()  Returns the current record's "workstory_id" value
 * @method integer           getWorkshopId()   Returns the current record's "workshop_id" value
 * @method timestamp         getCreatedAt()    Returns the current record's "created_at" value
 * @method timestamp         getUpdatedAt()    Returns the current record's "updated_at" value
 * @method Workshop          getWorkshop()     Returns the current record's "Workshop" value
 * @method Workstory         getWorkstory()    Returns the current record's "Workstory" value
 * @method WorkstoryWorkshop setWorkstoryId()  Sets the current record's "workstory_id" value
 * @method WorkstoryWorkshop setWorkshopId()   Sets the current record's "workshop_id" value
 * @method WorkstoryWorkshop setCreatedAt()    Sets the current record's "created_at" value
 * @method WorkstoryWorkshop setUpdatedAt()    Sets the current record's "updated_at" value
 * @method WorkstoryWorkshop setWorkshop()     Sets the current record's "Workshop" value
 * @method WorkstoryWorkshop setWorkstory()    Sets the current record's "Workstory" value
 * 
 * @package    dfmarketplace
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseWorkstoryWorkshop extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('workstory_workshop');
        $this->hasColumn('workstory_id', 'integer', 8, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => false,
             'length' => '8',
             ));
        $this->hasColumn('workshop_id', 'integer', 8, array(
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
        $this->hasOne('Workshop', array(
             'local' => 'workshop_id',
             'foreign' => 'id'));

        $this->hasOne('Workstory', array(
             'local' => 'workstory_id',
             'foreign' => 'id'));
    }
}