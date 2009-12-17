<?php

/**
 * Project form base class.
 *
 * @method Project getObject() Returns the current form's model object
 *
 * @package    dfmarketplace
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseProjectForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'project_id'      => new sfWidgetFormInputHidden(),
      'title'           => new sfWidgetFormInputText(),
      'response_wanted' => new sfWidgetFormDate(),
      'description'     => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'project_id'      => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'project_id', 'required' => false)),
      'title'           => new sfValidatorString(array('max_length' => 255)),
      'response_wanted' => new sfValidatorDate(array('required' => false)),
      'description'     => new sfValidatorString(array('max_length' => 4000, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('project[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Project';
  }

}
