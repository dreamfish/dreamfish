<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('SkillProject', 'doctrine');

/**
 * BaseSkillProject
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $skill_id
 * @property integer $project_id
 * @property timestamp $created_at
 * @property timestamp $updated_at
 * @property Project $Project
 * @property Skill $Skill
 * 
 * @method integer      getSkillId()    Returns the current record's "skill_id" value
 * @method integer      getProjectId()  Returns the current record's "project_id" value
 * @method timestamp    getCreatedAt()  Returns the current record's "created_at" value
 * @method timestamp    getUpdatedAt()  Returns the current record's "updated_at" value
 * @method Project      getProject()    Returns the current record's "Project" value
 * @method Skill        getSkill()      Returns the current record's "Skill" value
 * @method SkillProject setSkillId()    Sets the current record's "skill_id" value
 * @method SkillProject setProjectId()  Sets the current record's "project_id" value
 * @method SkillProject setCreatedAt()  Sets the current record's "created_at" value
 * @method SkillProject setUpdatedAt()  Sets the current record's "updated_at" value
 * @method SkillProject setProject()    Sets the current record's "Project" value
 * @method SkillProject setSkill()      Sets the current record's "Skill" value
 * 
 * @package    dfmarketplace
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 6820 2009-11-30 17:27:49Z jwage $
 */
abstract class BaseSkillProject extends sfDoctrineRecord
{
    public function setTableDefinition()
    {
        $this->setTableName('skill_project');
        $this->hasColumn('skill_id', 'integer', 8, array(
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
        $this->hasOne('Project', array(
             'local' => 'project_id',
             'foreign' => 'id'));

        $this->hasOne('Skill', array(
             'local' => 'skill_id',
             'foreign' => 'id'));
    }
}