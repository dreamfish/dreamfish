<?php

/**
 * ProjectSkill form base class.
 *
 * @method ProjectSkill getObject() Returns the current form's model object
 *
 * @package    dfmarketplace
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseProjectSkillForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'project_id' => new sfWidgetFormInputHidden(),
      'skill_id'   => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'project_id' => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'project_id', 'required' => false)),
      'skill_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'skill_id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('project_skill[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ProjectSkill';
  }

}
