function openTab(evt, tabName) {
  var i, ets_tabcontent, ets_tablinks;
  ets_tabcontent = document.getElementsByClassName("ets_tabcontent");
  for (i = 0; i < ets_tabcontent.length; i++) {
    ets_tabcontent[i].style.display = "none";
  }
  ets_tablinks = document.getElementsByClassName("ets_tablinks");
  for (i = 0; i < ets_tablinks.length; i++) {
    ets_tablinks[i].className = ets_tablinks[i].className.replace(" active", "");
  }
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
}
jQuery(document).ready(function () {
	jQuery('#disconnect-discord').on('click',function (e) {
		e.preventDefault();
		var userId = jQuery(this).data('user-id');
		jQuery.ajax({
			type:"POST",
			dataType:"JSON",
			url:etsPmproParams.admin_ajax,
            data: {'action': 'disconnect_from_discord','user_id':userId},
			beforeSend:function () {
				jQuery('#image-loader').show();
			},
			success:function (response) {
				if (response.status == 1) {
					location.reload();
				}
			},
			complete:function () {
				jQuery('#image-loader').hide();
			}

		});
	});
});