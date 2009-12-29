<?php

/**
 * ValueWorkstory form base class.
 *
 * @method ValueWorkstory getObject() Returns the current form's model object
 *
 * @package    dfmarketplace
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseValueWorkstoryForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'workstory_id' => new sfWidgetFormInputHidden(),
      'value_id'     => new sfWidgetFormInputHidden(),
      'created_at'   => new sfWidgetFormDateTime(),
      'updated_at'   => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'workstory_id' => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'workstory_id', 'required' => false)),
      'value_id'     => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'value_id', 'required' => false)),
      'created_at'   => new sfValidatorDateTime(),
      'updated_at'   => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('value_workstory[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ValueWorkstory';
  }

}