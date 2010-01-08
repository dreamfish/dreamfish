<?php

/**
 * Project form.
 *
 * @package    dfmarketplace
 * @subpackage form
 * @author     Your name here
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProjectForm extends BaseProjectForm
{
  public function configure()
  {
		unset(
			$this['created_at'],
			$this['updated_at']
		);

		$this->widgetSchema->setLabel('name', 'Project name <small>(what project is the request for?)</small>');
		$this->widgetSchema->setLabel('request', 'Request <small>(what kind of service?)</small>');
		$this->widgetSchema->setLabel('deadline', 'When do you want responses by?');
		$this->widgetSchema->setLabel('description', 'Describe your project <small>(what are the goals, tasks and time estimate?)</small>');

    $filter =  create_function('$cs', '$ret = Array(); foreach($cs as $c) { $ret[$c["id"]] = $c["name"]; }return $ret;');

    $choices =  $filter(Doctrine::getTable('Value')->findByDql('type = ?', array('Security'))->toArray());

		$this->widgetSchema['value_security_list'] = new sfWidgetFormChoice(array(
				'choices' => $choices
				, 'label' => 'Security'
				, 'multiple' => true));
    
    
    $this->widgetSchema['value_achievement_list'] = new sfWidgetFormChoice(array(
       'choices' => $filter(Doctrine::getTable('Value')->findByDql('type = ?', array('Achievement'))->toArray())
       , 'label' => 'Achievement'
       , 'multiple' => true));


  $this->widgetSchema['value_learning_list'] = new sfWidgetFormChoice(array(
       'choices' => $filter(Doctrine::getTable('Value')->findByDql('type = ?', array('Learning'))->toArray())
       , 'label' => 'Learning'
       , 'multiple' => true));



  $this->widgetSchema['value_global_list'] = new sfWidgetFormChoice(array(
      'choices' => $filter(Doctrine::getTable('Value')->findByDql('type = ?', array('Global'))->toArray())
      , 'label' => 'Global'
      , 'multiple' => true));


/*    
     $this->widgetSchema['values_list'] = new sfWidgetFormChoice(array(
           'choices' => $filter(Doctrine::getTable('Value')->findAll()->toArray())
           , 'label' => 'Values'
           , 'multiple' => true));
   
*/

  }
  public function updateDefaultsFromObject()
  {
    $values = $this->object->Values->toArray();
    $getId = create_function('$a', 'return $a["id"];');

    $valueTypes = array("Security", "Achievement", "Learning", "Global");

    foreach($valueTypes as $valueType)
    {
      $list = array_map($getId, array_filter($values, create_function('$a', 
        'return $a["type"] == "'.$valueType.'";')));

      $this->setDefault('value_'.strtolower($valueType).'_list', $list);
    }
   
    parent::updateDefaultsFromObject();
  }
}
