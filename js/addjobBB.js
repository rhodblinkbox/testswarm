/**
 * JavaScript file for the "addjob" page.
 *
 * @author Timo Tijhof, 2012
 * @since 1.0.0
 * @package TestSwarm
 */
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
		$(this).siblings('.browser-details').toggleClass('hide', !checked);		
	});	
	
	$('.po').popover();
	
	$('.removeRun').live('click', function() { 
		$(this).parent().parent().remove();
	});
});