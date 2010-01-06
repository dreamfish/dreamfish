<?php

/**
 * ProjectValue form base class.
 *
 * @method ProjectValue getObject() Returns the current form's model object
 *
 * @package    dfmarketplace
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseProjectValueForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'project_id' => new sfWidgetFormInputHidden(),
      'value_id'   => new sfWidgetFormInputHidden(),
    ));

    $this->setValidators(array(
      'project_id' => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'project_id', 'required' => false)),
      'value_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'value_id', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('project_value[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'ProjectValue';
  }

}
