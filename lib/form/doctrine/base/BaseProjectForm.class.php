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
      'id'              => new sfWidgetFormInputHidden(),
      'project_type_id' => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectType'), 'add_empty' => false)),
      'description'     => new sfWidgetFormTextarea(),
      'stage_id'        => new sfWidgetFormDoctrineChoice(array('model' => $this->getRelatedModelName('Stage'), 'add_empty' => true)),
      'wiki_page'       => new sfWidgetFormInputText(),
      'contact_id'      => new sfWidgetFormInputText(),
      'created_at'      => new sfWidgetFormDateTime(),
      'updated_at'      => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'              => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'project_type_id' => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('ProjectType'))),
      'description'     => new sfValidatorString(),
      'stage_id'        => new sfValidatorDoctrineChoice(array('model' => $this->getRelatedModelName('Stage'), 'required' => false)),
      'wiki_page'       => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'contact_id'      => new sfValidatorInteger(array('required' => false)),
      'created_at'      => new sfValidatorDateTime(),
      'updated_at'      => new sfValidatorDateTime(),
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
