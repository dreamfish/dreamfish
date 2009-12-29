<?php

/**
 * LanguagePerson form base class.
 *
 * @method LanguagePerson getObject() Returns the current form's model object
 *
 * @package    dfmarketplace
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseLanguagePersonForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'person_id'   => new sfWidgetFormInputHidden(),
      'language_id' => new sfWidgetFormInputHidden(),
      'created_at'  => new sfWidgetFormDateTime(),
      'updated_at'  => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'person_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'person_id', 'required' => false)),
      'language_id' => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'language_id', 'required' => false)),
      'created_at'  => new sfValidatorDateTime(),
      'updated_at'  => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('language_person[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'LanguagePerson';
  }

}
