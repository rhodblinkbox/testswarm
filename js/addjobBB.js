/**
 * JavaScript file for the bb "addjob" page.
 *
 * @author Maciej Borzecki, 2012
 * @since 1.0.0
 * @package TestSwarm
 */
 
(function($) {	
	$.fn.toggleDisabled = function(disabled) {
        return this.each(function() {
            var $this = $(this);
			
			if(disabled) {
				$this.attr('disabled', 'disabled');
			} else {
				$this.removeAttr('disabled')
			}
        });
    };
	
	$.fn.serializeObject = function()
	{
		var o = {};
		var a = this.serializeArray();
		$.each(a, function() {
			if (o[this.name] !== undefined) {
				if (!o[this.name].push) {
					o[this.name] = [o[this.name]];
				}
				o[this.name].push(this.value || '');
			} else {
				o[this.name] = this.value || '';
			}
		});
		return o;
	};

})(jQuery);


jQuery(function ($) {
	var $runsContainer, $addRunBtn, $runFieldsetClean, cnt;

	$runsContainer = $('#runs-container');
	$runFieldsetClean = $runsContainer.children('fieldset').eq(0).clone().detach();
	cnt = $runsContainer.children('fieldset').length;

	$addRunBtn = $('<button>')
		.text('+ Run')
		.addClass('btn')
		.click(function (e) {
			e.preventDefault();

			cnt += 1;

			function fixNum(i, val) {
				return val.replace( '1', cnt );
			}

			$addRunBtn.before(
				$runFieldsetClean.clone()
					.find('input').val('')
					.end()
					.find('[for*="1"]').attr('for', fixNum)
					.end()
					.find('[id*="1"]').attr('id', fixNum)
					.end()
					.find('legend span').text(fixNum)
					.end()
			);
		})
		.appendTo('<div class="form-actions"></div>')
		.parent();

	$runsContainer.append( $addRunBtn );
	
	var cookieManager = (function() { 
		var cookieName = 'formdata';
		
		function serialize(e) {
		
			var formData = $('form').serializeObject();
			var browserSets = formData["browserSets[]"] || [];	// form field can be empty, have one value or multiple items in the array
			var runNames = formData["runNames[]"] || [];	// form field can be empty, have one value or multiple items in the array
						
			var data = {
				runMax: formData.runMax,
				browserSets: browserSets instanceof Array ? browserSets : [browserSets],
				runNames: runNames instanceof Array ? runNames : [runNames]
			};
			
			setCookie(cookieName, JSON.stringify(data), 180);
		}
		
		function deserialize() {
			return JSON.parse(getCookie(cookieName));
		}
		
		function setCookie(c_name, value, exdays)
		{
			var exdate = new Date();
			exdate.setDate(exdate.getDate() + exdays);
			var c_value = escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
			document.cookie = c_name + "=" + c_value;
		}
		
		function isCookieSet() {
			return getCookie(cookieName) != null;
		}
		
		function getCookie(c_name)
		{
			var i,x,y,ARRcookies=document.cookie.split(";");
			for (i=0;i<ARRcookies.length;i++)
			{
				x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
				y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
				x=x.replace(/^\s+|\s+$/g,"");
				if (x==c_name)
				{
					return unescape(y);
				}
			}
		}
		
		return {
			serializeForm: serialize,
			deserializeForm: deserialize,
			isCookieSet: isCookieSet
		}
	}) ();
	
	// slide browser detail upon checkbox state change
	(function setupBrowsers() {
		$('input[name="browserSets[]"]').bind('slideDetails', function () {
			var details = $(this).siblings('.browser-details');
			var checked = $(this).is(':checked');
			if( checked ) {
				details.slideDown();
			} else {
				details.slideUp();
			}
		});
		
		$('input[name="browserSets[]"]').click(function() {
			$(this).trigger('slideDetails');
		});	
	}) ();
	
	// setup all popovers
	$('.po').popover();
	
	// show more form details on button click
	$('#btnOtherInformation').click(function() {
		$('#otherInformation').slideToggle();
		$(this).hide();
	});
	
	(function setupRuns() {
		
		// disable run depending on checkbox state
		$('input:checkbox.enableRun').bind('disableRelatedFields', function () {
			var checked = $(this).is(':checked');	
			$(this).closest('fieldset').find('input').not(this).toggleDisabled(!checked);			
		});
		
		$('input:checkbox.enableRun').live('click', function() {
			$(this).trigger('disableRelatedFields');
		});	

		// setup remove run button
		$('.removeRun').live('click', function() { 
			$(this).parent().parent().remove();
		});
		
		$('#button-TickRuns .all').click(function() {
			$(this).closest('fieldset').find('input:checkbox.enableRun').not(':checked').prop('checked', true).trigger('disableRelatedFields');
		});
		
		$('#button-TickRuns .none').click(function() {
			$(this).closest('fieldset').find('input:checkbox:checked.enableRun').prop('checked', false).trigger('disableRelatedFields');	
		});
		
		$('#button-TickRuns .same').click(function() {
			var data = cookieManager.deserializeForm();

			// bind state from cookies to the UI
			$('#form-runMax').val(data.runMax);
			
			$('input[type=checkbox][name="browserSets[]"]:checked').prop('checked', false).trigger('slideDetails');
			for(var i=0;data.browserSets && i<data.browserSets.length;i++) {
				browserSet = data.browserSets[i];
				$('input[type=checkbox][name="browserSets[]"][value="'+browserSet+'"]').prop('checked', true).trigger('slideDetails');
			}
			
			$('input[type=checkbox].enableRun:checked').prop('checked', false).trigger('disableRelatedFields');
			for(var i=0;data.runNames && i<data.runNames.length;i++) {
				runName = data.runNames[i];
				$('input[type=text][name="runNames[]"]')
					.filter(function() { return $.trim(this.value) == $.trim(runName); })
					.closest('fieldset')
					.find('input[type=checkbox]')
					.prop('checked', true)
					.trigger('disableRelatedFields');
			}
		});
		
		if(!cookieManager.isCookieSet()) {
			$('#button-TickRuns .same').attr('disabled', 'disabled');
		}	
		
	}) ();
	
	// setup header
	(function setupHeader () {
		var $h1 = $('.container .hero-unit:first h1');
		var $input = $('<input class="input-xxlarge" type="text" maxlength="255" id="jobName"></input>');
		var $label = $('<label for="jobName"></label>');
		$label.text = $h1.text();
		$h1.append($label);
		$h1.append($input);
		$input.focus();
		
		$jobName = $('#jobName');
		$formJobName = $('#form-jobName');
		
		// copy initial text
		$jobName.val($formJobName.val());
		
		// setup handler so relevant form field is updated each time text is changed in the input box
		$jobName.blur(function() {
			$formJobName.val($(this).val());		
		});
		
	}) ();
	
	$('form').submit(function(e) {
		
		// disable runs without ticket checkbox
		$('input:checkbox.enableRun').not(':checked').closest('fieldset').find('input').not('.enableRun').attr('disabled', 'disabled');
		
		cookieManager.serializeForm(e);	
	});
});