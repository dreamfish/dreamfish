<?php

/**
 * Workshop form base class.
 *
 * @method Workshop getObject() Returns the current form's model object
 *
 * @package    dfmarketplace
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseWorkshopForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'offer_type_id'     => new sfWidgetFormInputText(),
      'avatar_id'         => new sfWidgetFormInputText(),
      'description'       => new sfWidgetFormTextarea(),
      'welcome_status_id' => new sfWidgetFormInputText(),
      'embedded_video'    => new sfWidgetFormInputText(),
      'contact_id'        => new sfWidgetFormInputText(),
      'payment_method_id' => new sfWidgetFormInputText(),
      'created_at'        => new sfWidgetFormDateTime(),
      'updated_at'        => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'offer_type_id'     => new sfValidatorInteger(),
      'avatar_id'         => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'description'       => new sfValidatorString(array('required' => false)),
      'welcome_status_id' => new sfValidatorInteger(),
      'embedded_video'    => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'contact_id'        => new sfValidatorInteger(array('required' => false)),
      'payment_method_id' => new sfValidatorInteger(array('required' => false)),
      'created_at'        => new sfValidatorDateTime(),
      'updated_at'        => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('workshop[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Workshop';
  }

}
