<?php

/**
 * Skill form base class.
 *
 * @method Skill getObject() Returns the current form's model object
 *
 * @package    dfmarketplace
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormGeneratedTemplate.php 24171 2009-11-19 16:37:50Z Kris.Wallsmith $
 */
abstract class BaseSkillForm extends BaseFormDoctrine
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                  => new sfWidgetFormInputHidden(),
      'name'                => new sfWidgetFormInputText(),
      'project_skills_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Project')),
    ));

    $this->setValidators(array(
      'id'                  => new sfValidatorDoctrineChoice(array('model' => $this->getModelName(), 'column' => 'id', 'required' => false)),
      'name'                => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'project_skills_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Project', 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('skill[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();

    parent::setup();
  }

  public function getModelName()
  {
    return 'Skill';
  }

  public function updateDefaultsFromObject()
  {
    parent::updateDefaultsFromObject();

    if (isset($this->widgetSchema['project_skills_list']))
    {
      $this->setDefault('project_skills_list', $this->object->ProjectSkills->getPrimaryKeys());
    }

  }

  protected function doSave($con = null)
  {
    $this->saveProjectSkillsList($con);

    parent::doSave($con);
  }

  public function saveProjectSkillsList($con = null)
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    if (!isset($this->widgetSchema['project_skills_list']))
    {
      // somebody has unset this widget
      return;
    }

    if (null === $con)
    {
      $con = $this->getConnection();
    }

    $existing = $this->object->ProjectSkills->getPrimaryKeys();
    $values = $this->getValue('project_skills_list');
    if (!is_array($values))
    {
      $values = array();
    }

    $unlink = array_diff($existing, $values);
    if (count($unlink))
    {
      $this->object->unlink('ProjectSkills', array_values($unlink));
    }

    $link = array_diff($values, $existing);
    if (count($link))
    {
      $this->object->link('ProjectSkills', array_values($link));
    }
  }

}
