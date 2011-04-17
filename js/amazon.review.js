jQuery(document).ready( function($) {
	if ( $('#aml_review_submit').length ) {
		function updateTime (which) {
			var attemptedDate, originalDate, currentDate, aa = $('#aa-' + which).val(), mm = $('#mm-' + which).val(), jj = $('#jj-' + which).val(), hh = $('#hh-' + which).val(), mn = $('#mn-' + which).val();
			var stamp = $('#timestamp-' + which).html();

			attemptedDate = new Date( aa, mm - 1, jj, hh, mn );
			originalDate = new Date( $('#hidden_aa-' + which).val(), $('#hidden_mm-' + which).val() -1, $('#hidden_jj-' + which).val(), $('#hidden_hh-' + which).val(), $('#hidden_mn-' + which).val() );
			currentDate = new Date( $('#cur_aa-' + which).val(), $('#cur_mm-' + which).val() -1, $('#cur_jj-' + which).val(), $('#cur_hh-' + which).val(), $('#cur_mn-' + which).val() );

			if ( attemptedDate.getFullYear() != aa || (1 + attemptedDate.getMonth()) != mm || attemptedDate.getDate() != jj || attemptedDate.getMinutes() != mn ) {
				$('.timestamp-wrap', '#timestampdiv-' + which).addClass('form-invalid');
				return false;
			} else {
				$('.timestamp-wrap', '#timestampdiv-' + which).removeClass('form-invalid');
			}

			if ( originalDate.toUTCString() == attemptedDate.toUTCString() ) { //hack
				$('#timestamp-' + which).html(stamp);
			} else {
				$('#timestamp-' + which).html(
					' <b>' +
					$('option[value=' + $('#mm-' + which).val() + ']', '#mm-' + which).text() + ' ' +
					jj + ', ' +
					aa + ' @ ' +
					hh + ':' +
					mn + '</b> '
				);
			}

			return true;
		}

		function setTimeToNow (which) {
			var attemptedDate = new Date();
			var mm = attemptedDate.getMonth()+1;
			if (mm < 10)
				mm = "0" + mm;
			$('#aa-' + which).val(attemptedDate.getFullYear());
			$('#mm-' + which).val(mm);
			$('#jj-' + which).val(attemptedDate.getDate());
			$('#hh-' + which).val(attemptedDate.getHours());
			$('#mn-' + which).val(attemptedDate.getMinutes());

			$('#timestamp-' + which).html(
				' <b>' +
				$('option[value=' + mm + ']', '#mm-' + which).text() + ' ' +
				attemptedDate.getDate() + ', ' +
				attemptedDate.getFullYear() + ' @ ' +
				attemptedDate.getHours() + ':' +
				attemptedDate.getMinutes() + '</b> '
			);
		}

		function updateStatus() {
			var postStatus = $('#post_status'), origStatus = $('#original_post_status').val();
			if ( origStatus == 'onhold' || origStatus == 'using' || origStatus == 'finished' ) {
				$('option[value=added]', postStatus).remove();
			}

			if (postStatus.val() == 'using' && origStatus == 'added')
				setTimeToNow('started');
			if (postStatus.val() == 'finished')
				setTimeToNow('finished');

			if ( postStatus.is(':hidden') )
				$('.edit-post-status', '#misc-publishing-actions').show();

			$('#post-status-display').html($('option:selected', postStatus).text());
		}

		function getWhich (obj) {
			var tag = $(obj).parent().attr("id");
			return tag.replace('timestampdiv-', '');
		}

		$('a.edit-timestamp').click(function() {
			var which = getWhich(this);
			if ($('#timestampdiv-' + which).is(":hidden")) {
				$('#timestampdiv-' + which).slideDown("normal");
					$(this).hide();
				}
				return false;
		});

		$('.cancel-timestamp').click(function() {
			var which = getWhich($(this).parent());
			$('#timestampdiv-' + which).slideUp("normal");
			$('#mm-' + which).val($('#hidden_mm-' + which).val());
			$('#jj-' + which).val($('#hidden_jj-' + which).val());
			$('#aa-' + which).val($('#hidden_aa-' + which).val());
			$('#hh-' + which).val($('#hidden_hh-' + which).val());
			$('#mn-' + which).val($('#hidden_mn-' + which).val());
			$('#timestampdiv-' + which).siblings('a.edit-timestamp').show();
			updateTime(which);
			return false;
		});

		$('.save-timestamp').click(function () { // crazyhorse - multiple ok cancels
			var which = getWhich($(this).parent());
			if ( updateTime(which) ) {
				$('#timestampdiv-' + which).slideUp("normal");
				$('#timestampdiv-' + which).siblings('a.edit-timestamp').show();
			}
			return false;
		});

		$('#post-status-select').siblings('a.edit-post-status').click(function() {
			if ($('#post-status-select').is(":hidden")) {
				$('#post-status-select').slideDown("normal");
				$(this).hide();
			}
			return false;
		});

		$('.save-post-status', '#post-status-select').click(function() {
			$('#post-status-select').slideUp("normal");
			$('#post-status-select').siblings('a.edit-post-status').show();
			updateStatus();
			return false;
		});

		$('.cancel-post-status', '#post-status-select').click(function() {
			$('#post-status-select').slideUp("normal");
			$('#post_status').val($('#hidden_post_status').val());
			$('#post-status-select').siblings('a.edit-post-status').show();
			updateStatus();
			return false;
		});

		$('#parent_id').live('change', function() {
			var data = {
				action: 'aml_review_product',
				product: jQuery('#parent_id').val(),
			};

			jQuery.post(ajaxurl, data, function(image) {
				if (image != null) {
					jQuery('#aml_product-thumb').html('<img src="' + image + '" />');
				}
				else {
					jQuery('#aml_product-thumb').html('');
				}
			});
		});
	}
});