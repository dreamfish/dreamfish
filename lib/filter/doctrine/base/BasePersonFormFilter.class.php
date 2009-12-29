<?php

/**
 * Person filter form base class.
 *
 * @package    dfmarketplace
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BasePersonFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'name'              => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'username'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'password'          => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'email'             => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'mobile_phone'      => new sfWidgetFormFilterInput(),
      'avatar_id'         => new sfWidgetFormFilterInput(),
      'url'               => new sfWidgetFormFilterInput(),
      'city'              => new sfWidgetFormFilterInput(),
      'postal_code'       => new sfWidgetFormFilterInput(),
      'timezone'          => new sfWidgetFormFilterInput(),
      'feedback'          => new sfWidgetFormFilterInput(),
      'about_me'          => new sfWidgetFormFilterInput(),
      'twitter'           => new sfWidgetFormFilterInput(),
      'payment_method_id' => new sfWidgetFormFilterInput(),
      'chat_setting'      => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'created_at'        => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'        => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'name'              => new sfValidatorPass(array('required' => false)),
      'username'          => new sfValidatorPass(array('required' => false)),
      'password'          => new sfValidatorPass(array('required' => false)),
      'email'             => new sfValidatorPass(array('required' => false)),
      'mobile_phone'      => new sfValidatorPass(array('required' => false)),
      'avatar_id'         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'url'               => new sfValidatorPass(array('required' => false)),
      'city'              => new sfValidatorPass(array('required' => false)),
      'postal_code'       => new sfValidatorPass(array('required' => false)),
      'timezone'          => new sfValidatorPass(array('required' => false)),
      'feedback'          => new sfValidatorPass(array('required' => false)),
      'about_me'          => new sfValidatorPass(array('required' => false)),
      'twitter'           => new sfValidatorPass(array('required' => false)),
      'payment_method_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'chat_setting'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'created_at'        => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'        => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
    ));

    $this->widgetSchema->setNameFormat('person_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Person';
  }

  public function getFields()
  {
    return array(
      'id'                => 'Number',
      'name'              => 'Text',
      'username'          => 'Text',
      'password'          => 'Text',
      'email'             => 'Text',
      'mobile_phone'      => 'Text',
      'avatar_id'         => 'Number',
      'url'               => 'Text',
      'city'              => 'Text',
      'postal_code'       => 'Text',
      'timezone'          => 'Text',
      'feedback'          => 'Text',
      'about_me'          => 'Text',
      'twitter'           => 'Text',
      'payment_method_id' => 'Number',
      'chat_setting'      => 'Number',
      'created_at'        => 'Date',
      'updated_at'        => 'Date',
    );
  }
}
