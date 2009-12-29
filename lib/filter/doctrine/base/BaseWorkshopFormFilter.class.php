<?php

/**
 * Workshop filter form base class.
 *
 * @package    dfmarketplace
 * @subpackage filter
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormFilterGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseWorkshopFormFilter extends BaseFormFilterDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'offer_type_id'     => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'avatar_id'         => new sfWidgetFormFilterInput(),
      'description'       => new sfWidgetFormFilterInput(),
      'welcome_status_id' => new sfWidgetFormFilterInput(array('with_empty' => false)),
      'embedded_video'    => new sfWidgetFormFilterInput(),
      'contact_id'        => new sfWidgetFormFilterInput(),
      'payment_method_id' => new sfWidgetFormFilterInput(),
      'created_at'        => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'updated_at'        => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'offer_type_id'     => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'avatar_id'         => new sfValidatorPass(array('required' => false)),
      'description'       => new sfValidatorPass(array('required' => false)),
      'welcome_status_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'embedded_video'    => new sfValidatorPass(array('required' => false)),
      'contact_id'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'payment_method_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'created_at'        => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
      'updated_at'        => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 00:00:00')), 'to_date' => new sfValidatorDateTime(array('required' => false, 'datetime_output' => 'Y-m-d 23:59:59')))),
    ));

    $this->widgetSchema->setNameFormat('workshop_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Workshop';
  }

  public function getFields()
  {
    return array(
      'id'                => 'Number',
      'offer_type_id'     => 'Number',
      'avatar_id'         => 'Text',
      'description'       => 'Text',
      'welcome_status_id' => 'Number',
      'embedded_video'    => 'Text',
      'contact_id'        => 'Number',
      'payment_method_id' => 'Number',
      'created_at'        => 'Date',
      'updated_at'        => 'Date',
    );
  }
}
