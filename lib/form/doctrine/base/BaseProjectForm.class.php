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
      'id'          => new sfWidgetFormInputHidden(),
      'title'       => new sfWidgetFormInputText(),
      'name'        => new sfWidgetFormInputText(),
      'deadline'    => new sfWidgetFormDate(),
      'description' => new sfWidgetFormTextarea(),
      'created_at'  => new sfWidgetFormDateTime(),
      'updated_at'  => new sfWidgetFormDateTime(),
      'skills_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Skill')),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'title'       => new sfValidatorString(array('max_length' => 255)),
      'name'        => new sfValidatorString(array('max_length' => 255)),
      'deadline'    => new sfValidatorDate(),
      'description' => new sfValidatorString(),
      'created_at'  => new sfValidatorDateTime(),
      'updated_at'  => new sfValidatorDateTime(),
      'skills_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Skill', 'required' => false)),
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

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['skills_list']))
    {
      $this->setDefault('skills_list', $this->object->Skills->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->saveSkillsList($con);

    parent::doSave($con);
  }

  public function saveSkillsList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['skills_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->Skills->getPrimaryKeys();
    $values = $this->getValue('skills_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('Skills', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('Skills', array_values($link));
    }
  }

}
