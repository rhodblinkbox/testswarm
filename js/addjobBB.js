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
	
	$('input[name="browserSets[]"]').click(function() {
		var checked = $(this).is(':checked');
		$(this).siblings('.browser-details').slideToggle(!checked);		
	});	
	
	$('.po').popover();
	
	$('.removeRun').live('click', function() { 
		$(this).parent().parent().remove();
	});
	
	$('#btnOtherInformation').click(function() {
		$('#otherInformation').slideToggle();
		$(this).hide();
	});
	
	$('.enableRun').live('click', function() {
		$(this).closest('fieldset').find('input').not(this).toggleDisabled();	
	});
});