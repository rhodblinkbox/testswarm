<?php
/**
 * "Ping" action.
 *
 * @author Maciej Borzecki, 2012
 * @since 1.0.0
 * @package TestSwarm
 */
class DashboardRefreshAction extends Action {

	/**
	 * Return clients.
	 *
	 * @actionMethod POST: Required.
	 */
	public function doAction() {
		$db = $this->getContext()->getDB();
		
		// Get runs for this job
		$deviceRows = $db->getRows(str_queryf(
			'SELECT
				id, 
				ip, 
				useragent_id as \'browserName\', 
				updated
			FROM
				clients
			ORDER BY 
				updated desc;'
		));
		
		$devices = array();
		
		if( $deviceRows ) {
			foreach ( $deviceRows as $deviceRow ) {
				
				array_push($devices,
					array(
						'id' => $deviceRow->id,
						'ip' => $deviceRow->ip,
						'browserName' => $deviceRow->browserName,
						'updated' => $deviceRow->updated					
					)
				);
			}		
		}
		
		// Start of response data
		$respData = array(
			'devices' => $devices,
		);

		// Save data
		$this->setData( $respData );
	}
}
