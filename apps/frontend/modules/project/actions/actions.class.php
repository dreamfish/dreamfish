<?php

/**
 * project actions.
 *
 * @package    dfmarketplace
 * @subpackage project
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class projectActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
    $this->projects = Doctrine::getTable('Project')
      ->createQuery('a')
      ->execute();
  }

  public function executeShow(sfWebRequest $request)
  {
    $this->project = Doctrine::getTable('Project')->find(array($request->getParameter('id')));
    $this->forward404Unless($this->project);
  }

  public function executeNew(sfWebRequest $request)
  {
    $this->form = new ProjectForm();
  }

  public function executeCreate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST));

    $this->form = new ProjectForm();

    $this->processForm($request, $this->form);

    $this->setTemplate('new');
  }

  public function executeEdit(sfWebRequest $request)
  {
    $this->forward404Unless($project = Doctrine::getTable('Project')->find(array($request->getParameter('id'))), sprintf('Object project does not exist (%s).', $request->getParameter('id')));
    $this->form = new ProjectForm($project);
  }

  public function executeUpdate(sfWebRequest $request)
  {
    $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
    $this->forward404Unless($project = Doctrine::getTable('Project')->find(array($request->getParameter('id'))), sprintf('Object project does not exist (%s).', $request->getParameter('id')));
    $this->form = new ProjectForm($project);

    $this->processForm($request, $this->form);

    $this->setTemplate('edit');
  }

  public function executeDelete(sfWebRequest $request)
  {
    $request->checkCSRFProtection();

    $this->forward404Unless($project = Doctrine::getTable('Project')->find(array($request->getParameter('id'))), sprintf('Object project does not exist (%s).', $request->getParameter('id')));
    $project->delete();

    $this->redirect('project/index');
  }

  protected function processForm(sfWebRequest $request, sfForm $form)
  {
    $values = $request->getParameter($form->getName());
    $values["values_list"] = array();
 
 		$valueList = array("security", "achievement", "learning", "global");

		foreach($valueList as $valueItem)
		{
			if (isset($values["value_".$valueItem."_list"])) 
				$values["values_list"] = array_merge($values["values_list"], $values["value_".$valueItem."_list"]);
				unset($values["value_".$valueItem."_list"]);
		}
		$form->bind($values);
    if ($form->isValid())
    {
//      $form->setValue('skills_list', array("1", "3"));
			$project = $form->save();

      $this->redirect('project/edit?id='.$project->getId());
    }

  }
}
