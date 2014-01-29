<?php

use Nette\Application\UI;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends UI\Presenter
{

	/**
	* Creates login form, which is part of the basic layout and thus part of any page.
	*/
	protected function createComponentLoginForm()
	{
		$form = new UI\Form();
		$form->addText('Username', 'Uživatelské jméno:')
			->setRequired('Musíte zadat uživatelské jméno');
		$form->addPassword('Password', 'Heslo:')
			->setRequired('Musíte zadat heslo');
		$form->addCheckbox('Permanent', 'Trvale');
		$form->addSubmit('Login', 'Přihlásit');
		$form->onSuccess[] = callback($this, 'processValidatedLoginForm');
		return $form;
	}



	public function processValidatedLoginForm($form)
	{
		$database = $this->context->database;
		$values = $form->getValues();
		$user = $this->getUser();
		try {
			$user->login($values['Username'], $values['Password']);
			$user->setExpiration($values['Permanent'] ? '+ 7 days' : '+ 30 minutes', false);
			$content = $database->table('Users')->where('Username', $values['Username'])->update(array(
				'LastLogin' => date("Y-m-d H:i:s",time())
			));
		} catch (\Nette\Security\AuthenticationException $ex) {
			$this->flashMessage($ex->getMessage(), 'error');
		}
		$this->redirect('Homepage:default');
	}



	public function handleLogout()
	{
		$this->getUser()->logout(true);
		$this->flashMessage('Byl(a) jste odhlášen(a).');
		$this->redirect('Homepage:default');
	}

}
