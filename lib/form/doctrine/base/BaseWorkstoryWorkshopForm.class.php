<?php

/**
 * WorkstoryWorkshop form base class.
 *
 * @method WorkstoryWorkshop getObject() Returns the current form's model object
 *
 * @package    dfmarketplace
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseWorkstoryWorkshopForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'workstory_id' => new sfWidgetFormInputHidden(),
      'workshop_id'  => new sfWidgetFormInputHidden(),
      'created_at'   => new sfWidgetFormDateTime(),
      'updated_at'   => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'workstory_id' => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'workstory_id', 'required' => false)),
      'workshop_id'  => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'workshop_id', 'required' => false)),
      'created_at'   => new sfValidatorDateTime(),
      'updated_at'   => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('workstory_workshop[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'WorkstoryWorkshop';
  }

}
