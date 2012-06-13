/**
 * JavaScript file for the bb "addjob" page.
 *
 * @author Maciej Borzecki, 2012
 * @since 1.0.0
 * @package TestSwarm
 */
 
(function($) {
    $.fn.toggleDisabled = function() {
        return this.each(function() {
            var $this = $(this);
            if ($this.attr('disabled')) $this.removeAttr('disabled');
            else $this.attr('disabled', 'disabled');
        });
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
	
	// slide browser detail upon checkbox state change
	$('input[name="browserSets[]"]').click(function() {
		var checked = $(this).is(':checked');
		$(this).siblings('.browser-details').slideToggle(!checked);		
	});	
	
	// setup all popovers
	$('.po').popover();
	
	// setup remove run button
	$('.removeRun').live('click', function() { 
		$(this).parent().parent().remove();
	});
	
	// show more form details on button click
	$('#btnOtherInformation').click(function() {
		$('#otherInformation').slideToggle();
		$(this).hide();
	});
	
	// disable run depending on checkbox state
	$('.enableRun').live('click', function() {
		$(this).closest('fieldset').find('input').not(this).toggleDisabled();	
	});
	
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
		
	})();
	
});