<?php
/**
 * "DashboardRefresh" action.
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
	 * @actionParam deviceTooOldThreshold int
	 *
	 */
	public function doAction() {		
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
		$db = $this->getContext()->getDB();
		$deviceRows = $db->getRows(str_queryf(
			'SELECT
				id, 
				ip, 
				useragent_id as browser, 
				updated,
				created,
				device_name as name
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
						'browser' => $deviceRow->browser,
						'updated' => strtotime($deviceRow->updated),
						'created' => strtotime($deviceRow->created),
						'name' => $deviceRow->name						
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
