<?php

/**
 * Event form base class.
 *
 * @method Event getObject() Returns the current form's model object
 *
 * @package    dfmarketplace
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseEventForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'            => new sfWidgetFormInputHidden(),
      'title'         => new sfWidgetFormInputText(),
      'from_date'     => new sfWidgetFormDateTime(),
      'to_date'       => new sfWidgetFormDateTime(),
      'description'   => new sfWidgetFormTextarea(),
      'venue_type_id' => new sfWidgetFormInputText(),
      'venue_url'     => new sfWidgetFormInputText(),
      'city'          => new sfWidgetFormInputText(),
      'country_id'    => new sfWidgetFormInputText(),
      'register_url'  => new sfWidgetFormInputText(),
      'workshop_id'   => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Workshop'), 'add_empty' => true)),
      'contact_id'    => new sfWidgetFormInputText(),
      'created_at'    => new sfWidgetFormDateTime(),
      'updated_at'    => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'            => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'title'         => new sfValidatorString(array('max_length' => 255)),
      'from_date'     => new sfValidatorDateTime(),
      'to_date'       => new sfValidatorDateTime(),
      'description'   => new sfValidatorString(array('required' => false)),
      'venue_type_id' => new sfValidatorInteger(),
      'venue_url'     => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'city'          => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'country_id'    => new sfValidatorInteger(array('required' => false)),
      'register_url'  => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'workshop_id'   => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Workshop'), 'required' => false)),
      'contact_id'    => new sfValidatorInteger(array('required' => false)),
      'created_at'    => new sfValidatorDateTime(),
      'updated_at'    => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('event[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Event';
  }

}
