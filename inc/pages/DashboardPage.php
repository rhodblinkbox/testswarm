<?php
/**
 * "Info" page.
 *
 * @author Maciej Borzecki, 2012
 * @since 1.0.0
 * @package TestSwarm
 */

class DashboardPage extends Page {

	public function execute() {
		$action = DashboardAction::newFromContext( $this->getContext() );
		$action->doAction();

		$this->setAction( $action );
		$this->content = $this->initContent();
	}

	protected function initContent() {
		$this->setTitle( 'TV devices dashboard' );

		$html = file_get_contents( 'inc/pages/DashboardPage.html' );

		return $html;
	}
}
