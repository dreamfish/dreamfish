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
/*
		$this->widgetSchema['stage_id'] = new sfWidgetFormChoice(array(
				'choices' => Doctrine::getTable('Stage')->findAll()
				));
    $this->widgetSchema['project_type_id'] = new sfWidgetFormChoice(array(
				'choices' => Doctrine::getTable('ProjectType')->findAll()
				));
*/
	}
}
