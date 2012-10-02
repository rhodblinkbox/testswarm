<?php
/**
 * "SetDeviceName" action
 *
 * @author Maciej Borzecki, 2012
 * @since 0.1.0
 * @package TestSwarm
 */
class SetDeviceNameAction extends Action {

	/**
	 *
	 * @actionMethod POST: Required.
	 * @actionParam id int
	 * @actionParam name string
	 *
	 */
	public function doAction() {
		$request = $this->getContext()->getRequest();

		if ( !$request->wasPosted() ) {
			$this->setError( "requires-post" );
			return;
		}
		
		$json = json_decode(file_get_contents('php://input'));

		if ( !$json->id ) {
			$this->setError( "missing-id" );
			return;
		}
		
		if ( !$json->name ) {
			$this->setError( "missing-name" );
			return;
		}
		
		// Save data
		$db = $this->getContext()->getDB();
		$db->query(str_queryf(
			'UPDATE 
				clients 
			SET 
				device_name = %s
			WHERE 
				id = %u;',
			$json->name,
			$json->id 
		));
				
		$this->setData( 'OK' );
	}
}

