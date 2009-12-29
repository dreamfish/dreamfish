<?php

/**
 * Person form base class.
 *
 * @method Person getObject() Returns the current form's model object
 *
 * @package    dfmarketplace
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasePersonForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'name'              => new sfWidgetFormInputText(),
      'username'          => new sfWidgetFormInputText(),
      'password'          => new sfWidgetFormInputText(),
      'email'             => new sfWidgetFormInputText(),
      'mobile_phone'      => new sfWidgetFormInputText(),
      'avatar_id'         => new sfWidgetFormInputText(),
      'url'               => new sfWidgetFormInputText(),
      'city'              => new sfWidgetFormInputText(),
      'postal_code'       => new sfWidgetFormInputText(),
      'timezone'          => new sfWidgetFormInputText(),
      'feedback'          => new sfWidgetFormTextarea(),
      'about_me'          => new sfWidgetFormTextarea(),
      'twitter'           => new sfWidgetFormInputText(),
      'payment_method_id' => new sfWidgetFormInputText(),
      'chat_setting'      => new sfWidgetFormInputText(),
      'created_at'        => new sfWidgetFormDateTime(),
      'updated_at'        => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'name'              => new sfValidatorString(array('max_length' => 255)),
      'username'          => new sfValidatorString(array('max_length' => 255)),
      'password'          => new sfValidatorString(array('max_length' => 255)),
      'email'             => new sfValidatorString(array('max_length' => 255)),
      'mobile_phone'      => new sfValidatorString(array('max_length' => 31, 'required' => false)),
      'avatar_id'         => new sfValidatorInteger(array('required' => false)),
      'url'               => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'city'              => new sfValidatorString(array('max_length' => 63, 'required' => false)),
      'postal_code'       => new sfValidatorString(array('max_length' => 15, 'required' => false)),
      'timezone'          => new sfValidatorString(array('max_length' => 15, 'required' => false)),
      'feedback'          => new sfValidatorString(array('required' => false)),
      'about_me'          => new sfValidatorString(array('required' => false)),
      'twitter'           => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'payment_method_id' => new sfValidatorInteger(array('required' => false)),
      'chat_setting'      => new sfValidatorInteger(array('required' => false)),
      'created_at'        => new sfValidatorDateTime(),
      'updated_at'        => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('person[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Person';
  }

}
