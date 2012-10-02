<?php
/** 
 * "Adddevboxjob" page.
 * File is copied from AddjobPage.php and modified.
 *
 * @author Timo Tijhof, 2012
 * @author Maciej Borzecki 2012
 * @since 1.0.0
 * @package TestSwarm
 */

class AdddevboxjobPage extends Page {

	public function execute() {
		$action = AdddevboxjobAction::newFromContext( $this->getContext() );
		$action->doAction();

		$this->setAction( $action );
		$this->content = $this->initContent();
	}

	protected function initContent() {
	
		$request = $this->getContext()->getRequest();	
		
		$this->setTitle( "Job name" );
		
		if ( $request->getSessionData( "auth" ) !== "yes" ) {
			$html = html_tag( "div", array( "class" => "alert alert-error" ), "You must be authenticated in order to add a job." );
			return $html;
		};	
		
		$this->bodyScripts[] = swarmpath( "js/AddDevBoxJob.js" );
		$this->bodyScripts[] = swarmpath( "js/bootstrap-tooltip.js" );
		$this->bodyScripts[] = swarmpath( "js/bootstrap-popover.js" );
		$this->bodyScripts[] = swarmpath( "js/bootstrap-button.js" );
		$this->bodyScripts[] = swarmpath( "js/json2.js" );
		$this->styleSheets[] = swarmpath( "css/AddDevBoxJob.css" );
		
		$html = "";

		$error = $this->getAction()->getError();
		$data = $this->getAction()->getData();
				
		if ( $request->wasPosted() ) {
			if ( $error ) {
				$html .= html_tag( "div", array( "class" => "alert alert-error" ), $error["info"] );
			} elseif ( $data && isset( $data["id"] ) ) {
				$html .= '<div class="alert alert-success">'
					. '<strong><a href="' . htmlspecialchars( swarmpath( "job/{$data["id"]}" ) )
					. '">Job ' . $data["id"] . '</a> has been created!</strong><br>'
					. $data["runTotal"] . ' runs have been scheduled to be ran in ' . $data["uaTotal"]
					. ' different browsers.<br><br>'
					. '<a class="btn btn-primary btn-small" href="' . htmlspecialchars( swarmpath( "job/{$data["id"]}" ) )
					. '">continue to job page &raquo;</a>'
					. '</div>';
			}
		}
		
		if ($data && isset( $data["id"] ) ) {
			$jobId = $data["id"];
			header( "X-TestSwarm-JobId: $jobId", true );
		}
		
		$html .= $this->getAddjobFormHtml();

		return $html;
	}
	
	protected function getAddjobFormHtml() {
		$conf = $this->getContext()->getConf();
		$request = $this->getContext()->getRequest();
		
		$swarmUaIndex = BrowserInfo::getSwarmUAIndex();

		$addjobPageUrl = htmlspecialchars( swarmpath( "adddevboxjob" ) );
		$userName = $request->getSessionData( "username" ) && $request->getSessionData( "auth" ) == "yes" ? htmlspecialchars( $request->getSessionData( "username" ) ) : "";

		// fields to be taken from the querystring:
		$jobName = $request->getVal('jobName');
		$runMax = $request->getVal('runMax') ?: 3;
		$runNames = $request->getArray('runNames');
		$runUrls = $request->getArray('runUrls');	
		$selectedBrowsers = $request->getArray('browsers');	
		
		if ( $runNames && !$runUrls || !$runNames && $runUrls || $runNames && $runUrls && count($runNames) != count($runUrls) ) {
			throw new SwarmException( "runNames and runUrls must have the same number of elements in the array" );
			return;
		}
		
		$formHtml = <<<HTML
			
<form action="$addjobPageUrl" method="post" class="form-horizontal">
	<div class="row">
		<div class="span6">
HTML;
		$formHtml .= $this->getBrowsers($conf, $swarmUaIndex, $selectedBrowsers);
		$formHtml .= <<<HTML
		</div>
		<div class="span6">
			
HTML;
		$formHtml .= <<<HTML

			<fieldset>
				<legend>Runs <i class="icon-question-sign po" data-title="Runs" data-content='Each job consists of several runs. Every run has a name and a url to where that test suite can be ran. All the test suites should probably have the same common code base or some other grouping characteristic, where each run is part of the larger test suite. As example, for a QUnit test suite the <code>filter</code> url parameter can be used to only run one of the "modules" so every run would be the name of that module and the URL to the testsuite with <code>?filter=modulename</code> appended to it.'></i></legend>

				<label class="control-label" for="button-TickRuns">Ticked runs: &nbsp;</label>

				<div class="btn-group" data-toggle="buttons-radio" id="button-TickRuns">
					<button class="btn all" type="button">all</button>
					<button class="btn none" type="button">none</button>
					<button class="btn same" type="button">same as last time</button>
					<i class="icon-question-sign po" data-title="Same as last time" data-content='Restores runMax, browserSets and runNames fields from the cookie. runMax value is put into the form field. browserSets are ticked. Runs from the cookie are matched against runs in the html form value.'></i>
				</div>
				<div id="runs-container" class="well">
HTML;
		if($runNames && count($runNames)>0) {
			for($i=0;$i<count($runNames);$i++) {
				$formHtml .= $this->getRun($i+1, $runNames[$i], $runUrls[$i]);
			}		
		}		
		else {		
			for($i=0;$i<3;$i++) {
				$formHtml .= $this->getRun($i+1, $runNames[$i], $runUrls[$i]);
			}
		}

		$formHtml .= <<<HTML
				</div>
			</fieldset>
			
			<button class="btn" id="btnOtherInformation" type="button">Other information</button>
			
			<div class="well hide" id="otherInformation">
				<fieldset>
					<legend>Other information</legend>

					<div class="control-group">
						<label class="control-label" for="form-authUsername">User name:</label>
						<div class="controls">
							<input type="text" value="$userName" id="form-authUsername" disabled="disabled">
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="form-runMax">Run max:</label>
						<div class="controls">
							<input type="number" size="5" name="runMax" id="form-runMax" value="$runMax" min="1" max="99">
							<i class="icon-question-sign po" data-title="Run max" data-content='This is the maximum number of times a run is ran in a user agent. If a run passes
							without failures then it is only ran once. If it does not pass, TestSwarm will re-try the run
							(up to "Run max" times) for that useragent to avoid error pollution due to time-outs, slow
							computers or other unrelated conditions that can cause the server to not receive a success report.'></i>
						</div>
					</div>
				</fieldset>
				<input type="hidden" name="jobName" id="form-jobName" maxlength="255" value="$jobName">
			</div>
		</div>
	</div>
	
	<div class="form-actions">
		<input type="submit" value="Create job" class="btn btn-primary btn-large">
	</div>
</form>
HTML;

		return $formHtml;
	}
	
	private function getRun($i, $name = null, $url = null) {
		$enabled = $name && $url;
		$checked = $enabled ? "checked=\"checked\"" : '';
		//$disableInput = $enabled ? '' : 'disabled=\"disabled\"';
		
		return <<<HTML
				<fieldset>
					<legend><span>Run</span>&nbsp;<i class="icon-remove-sign removeRun"></i></legend>
					<label class="checkbox">
						<input type="checkbox" class="enableRun" $checked> Enable
					</label>
					<br/>
					<label for="form-runNames1">Name:</label>
					<input type="text" name="runNames[]" id="form-runNames$i" maxlength="255" value="$name">
					<br>
					<label for="form-runUrls1">URL:</label>
					<input type="text" name="runUrls[]" placeholder="http://" class="input-xlarge" id="form-runUrls$i" value="$url">					
				</fieldset>	
				<br/>
HTML;
	}
	
	private function getBrowsers($conf, $swarmUaIndex, $selectedBrowsers) {
		$formHtml = <<<HTML
			<fieldset>
				<legend>Browsers <i class="icon-question-sign po" data-title="Browsers" data-content="Choose which groups of user agents this job should be ran in. Some of the groups may
				overlap each other, TestSwarm will detect and remove duplicate entries in the resulting set."></i></legend>
HTML;
		foreach ( $conf->browserSets as $set => $browsers ) {
			$set = htmlspecialchars( $set );
			$browsersHtml = '';
			$last = count( $browsers ) - 1;
			foreach ( $browsers as $i => $browser ) {
				if ( $i !== 0 ) {
					$browsersHtml .= $i === $last ? '<br> and ' : ',<br>';
				}
				$browsersHtml .= htmlspecialchars( $swarmUaIndex->$browser->displaytitle );
			}		
			$checked = "";
			$hideBrowserDetails = "hide";
			if($selectedBrowsers) {
				foreach ( $selectedBrowsers as $b ) {
					if($b == $set) {
						$checked = "checked=\"checked\"";
						break;
					} else {
						$hideBrowserDetails = "hide";
					}
				}			
			}
			$formHtml .= <<<HTML
			<div class="control-group">
				<label class="checkbox" for="form-browserset-$set">
					<input type="checkbox" name="browserSets[]" value="$set" id="form-browserset-$set" $checked>
					<strong>$set</strong><div class="browser-details $hideBrowserDetails">$browsersHtml</div>
				</label>
			</div>
HTML;
		}
		$formHtml .= <<<HTML
			</fieldset>
HTML;
		return $formHtml;	
	}
}
