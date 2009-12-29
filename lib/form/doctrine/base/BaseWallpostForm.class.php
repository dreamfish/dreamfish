<?php

/**
 * Wallpost form base class.
 *
 * @method Wallpost getObject() Returns the current form's model object
 *
 * @package    dfmarketplace
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseWallpostForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'person_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Person'), 'add_empty' => false)),
      'workshop_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Workshop'), 'add_empty' => false)),
      'date'        => new sfWidgetFormDateTime(),
      'content'     => new sfWidgetFormTextarea(),
      'created_at'  => new sfWidgetFormDateTime(),
      'updated_at'  => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'person_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Person'))),
      'workshop_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Workshop'))),
      'date'        => new sfValidatorDateTime(),
      'content'     => new sfValidatorString(array('required' => false)),
      'created_at'  => new sfValidatorDateTime(),
      'updated_at'  => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('wallpost[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Wallpost';
  }

}
