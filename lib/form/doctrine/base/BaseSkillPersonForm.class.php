<?php

/**
 * SkillPerson form base class.
 *
 * @method SkillPerson getObject() Returns the current form's model object
 *
 * @package    dfmarketplace
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSkillPersonForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'skill_id'   => new sfWidgetFormInputHidden(),
      'person_id'  => new sfWidgetFormInputHidden(),
      'created_at' => new sfWidgetFormDateTime(),
      'updated_at' => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'skill_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'skill_id', 'required' => false)),
      'person_id'  => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'person_id', 'required' => false)),
      'created_at' => new sfValidatorDateTime(),
      'updated_at' => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('skill_person[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'SkillPerson';
  }

}
