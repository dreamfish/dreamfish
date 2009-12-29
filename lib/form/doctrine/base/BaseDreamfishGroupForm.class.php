<?php

/**
 * DreamfishGroup form base class.
 *
 * @method DreamfishGroup getObject() Returns the current form's model object
 *
 * @package    dfmarketplace
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseDreamfishGroupForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                      => new sfWidgetFormInputHidden(),
      'dreamfish_group_type_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('DreamfishGroupType'), 'add_empty' => true)),
      'location'                => new sfWidgetFormInputText(),
      'interest'                => new sfWidgetFormInputText(),
      'contact_id'              => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Person'), 'add_empty' => true)),
      'created_at'              => new sfWidgetFormDateTime(),
      'updated_at'              => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                      => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'dreamfish_group_type_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('DreamfishGroupType'), 'required' => false)),
      'location'                => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'interest'                => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'contact_id'              => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Person'), 'required' => false)),
      'created_at'              => new sfValidatorDateTime(),
      'updated_at'              => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('dreamfish_group[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'DreamfishGroup';
  }

}
