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
	 * @actionParam deviceTooOldThreshold run_token int
	 *
	 */
	public function doAction() {
		$db = $this->getContext()->getDB();
		
		$request = $this->getContext()->getRequest();

		if ( !$request->wasPosted() ) {
			$this->setError( "requires-post" );
			return;
		}
		
		// ignore devices seen more then deviceTooOldThreshold seconds ago
		$deviceTooOldThreshold = $request->getInt( "deviceTooOldThreshold" );

		if ( !$deviceTooOldThreshold ) {
			$this->setError( "missing-deviceTooOldThreshold" );
			return;
		}
		
		// Get runs for this job
		$deviceRows = $db->getRows(str_queryf(
			'SELECT
				id, 
				ip, 
				useragent_id as \'browserName\', 
				updated
			FROM
				clients
			WHERE 
				updated >= now() - %u
			ORDER BY 
				updated desc;',
			$deviceTooOldThreshold
		));
		
		$devices = array();
		
		if( $deviceRows ) {
			foreach ( $deviceRows as $deviceRow ) {
				array_push($devices,
					array(
						'id' => $deviceRow->id,
						'ip' => $deviceRow->ip,
						'browserName' => $deviceRow->browserName,
						'updated' => strtotime($deviceRow->updated)
					)
				);
			}		
		}
		
		// Start of response data
		$respData = array(
			'devices' => $devices,
			'serverTime' => time()//date('Y-m-d H:i:s')
		);

		// Save data
		$this->setData( $respData );
	}
}
