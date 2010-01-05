<?php

/**
 * Skill filter form base class.
 *
 * @package    dfmarketplace
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSkillFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'                => new sfWidgetFormFilterInput(),
      'project_skills_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Project')),
    ));

    $this->setValidators(array(
      'name'                => new sfValidatorPass(array('required' => false)),
      'project_skills_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Project', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('skill_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function addProjectSkillsListColumnQuery(Doctrine_Query $query, $field, $values)
  {
    if (!is_array($values))
    {
      $values = array($values);
    }

    if (!count($values))
    {
      return;
    }

    $query->leftJoin('r.ProjectSkill ProjectSkill')
          ->andWhereIn('ProjectSkill.project_id', $values);
  }

  public function getModelName()
  {
    return 'Skill';
  }

  public function getFields()
  {
    return array(
      'id'                  => 'Number',
      'name'                => 'Text',
      'project_skills_list' => 'ManyKey',
    );
  }
}
