<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('Event', 'doctrine');

/**
 * BaseEvent
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property string $title
 * @property timestamp $from_date
 * @property timestamp $to_date
 * @property string $description
 * @property integer $venue_type_id
 * @property string $venue_url
 * @property string $city
 * @property integer $country_id
 * @property string $register_url
 * @property integer $workshop_id
 * @property integer $contact_id
 * @property timestamp $created_at
 * @property timestamp $updated_at
 * @property Workshop $Workshop
 * @property Doctrine_Collection $ValueEvent
 * 
 * @method integer             getId()            Returns the current record's "id" value
 * @method string              getTitle()         Returns the current record's "title" value
 * @method timestamp           getFromDate()      Returns the current record's "from_date" value
 * @method timestamp           getToDate()        Returns the current record's "to_date" value
 * @method string              getDescription()   Returns the current record's "description" value
 * @method integer             getVenueTypeId()   Returns the current record's "venue_type_id" value
 * @method string              getVenueUrl()      Returns the current record's "venue_url" value
 * @method string              getCity()          Returns the current record's "city" value
 * @method integer             getCountryId()     Returns the current record's "country_id" value
 * @method string              getRegisterUrl()   Returns the current record's "register_url" value
 * @method integer             getWorkshopId()    Returns the current record's "workshop_id" value
 * @method integer             getContactId()     Returns the current record's "contact_id" value
 * @method timestamp           getCreatedAt()     Returns the current record's "created_at" value
 * @method timestamp           getUpdatedAt()     Returns the current record's "updated_at" value
 * @method Workshop            getWorkshop()      Returns the current record's "Workshop" value
 * @method Doctrine_Collection getValueEvent()    Returns the current record's "ValueEvent" collection
 * @method Event               setId()            Sets the current record's "id" value
 * @method Event               setTitle()         Sets the current record's "title" value
 * @method Event               setFromDate()      Sets the current record's "from_date" value
 * @method Event               setToDate()        Sets the current record's "to_date" value
 * @method Event               setDescription()   Sets the current record's "description" value
 * @method Event               setVenueTypeId()   Sets the current record's "venue_type_id" value
 * @method Event               setVenueUrl()      Sets the current record's "venue_url" value
 * @method Event               setCity()          Sets the current record's "city" value
 * @method Event               setCountryId()     Sets the current record's "country_id" value
 * @method Event               setRegisterUrl()   Sets the current record's "register_url" value
 * @method Event               setWorkshopId()    Sets the current record's "workshop_id" value
 * @method Event               setContactId()     Sets the current record's "contact_id" value
 * @method Event               setCreatedAt()     Sets the current record's "created_at" value
 * @method Event               setUpdatedAt()     Sets the current record's "updated_at" value
 * @method Event               setWorkshop()      Sets the current record's "Workshop" value
 * @method Event               setValueEvent()    Sets the current record's "ValueEvent" collection
 * 
 * @package    dfmarketplace
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseEvent extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('event');
        $this->hasColumn('id', 'integer', 8, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => true,
             'autoincrement' => true,
             'length' => '8',
             ));
        $this->hasColumn('title', 'string', 255, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => '255',
             ));
        $this->hasColumn('from_date', 'timestamp', 25, array(
             'type' => 'timestamp',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => '25',
             ));
        $this->hasColumn('to_date', 'timestamp', 25, array(
             'type' => 'timestamp',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => '25',
             ));
        $this->hasColumn('description', 'string', null, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => '',
             ));
        $this->hasColumn('venue_type_id', 'integer', 8, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => true,
             'autoincrement' => false,
             'length' => '8',
             ));
        $this->hasColumn('venue_url', 'string', 255, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => '255',
             ));
        $this->hasColumn('city', 'string', 255, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => '255',
             ));
        $this->hasColumn('country_id', 'integer', 8, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => '8',
             ));
        $this->hasColumn('register_url', 'string', 255, array(
             'type' => 'string',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => '255',
             ));
        $this->hasColumn('workshop_id', 'integer', 8, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
             'autoincrement' => false,
             'length' => '8',
             ));
        $this->hasColumn('contact_id', 'integer', 8, array(
             'type' => 'integer',
             'fixed' => 0,
             'unsigned' => false,
             'primary' => false,
             'notnull' => false,
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

        $this->hasMany('ValueEvent', array(
             'local' => 'id',
             'foreign' => 'event_id'));
    }
}