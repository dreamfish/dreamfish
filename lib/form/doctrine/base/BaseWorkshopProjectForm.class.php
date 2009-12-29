<?php

/**
 * WorkshopProject form base class.
 *
 * @method WorkshopProject getObject() Returns the current form's model object
 *
 * @package    dfmarketplace
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseWorkshopProjectForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'workshop_id' => new sfWidgetFormInputHidden(),
      'project_id'  => new sfWidgetFormInputHidden(),
      'created_at'  => new sfWidgetFormDateTime(),
      'updated_at'  => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'workshop_id' => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'workshop_id', 'required' => false)),
      'project_id'  => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'project_id', 'required' => false)),
      'created_at'  => new sfValidatorDateTime(),
      'updated_at'  => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('workshop_project[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'WorkshopProject';
  }

}
