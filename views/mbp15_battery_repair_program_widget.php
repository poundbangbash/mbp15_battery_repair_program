<div class="col-lg-4 col-md-6">
	<div class="panel panel-default" id="mbp15_battery_repair_program-state-widget">
		<div class="panel-heading" data-container="body">
			<h3 class="panel-title"><i class="fa fa-display"></i>
			    <span data-i18n="mbp15_battery_repair_program.state"></span>
                <list-link data-url="/show/listing/mbp15_battery_repair_program/mbp15_battery_repair_program"></list-link>
			</h3>
		</div>
		<div class="panel-body text-center"></div>
	</div><!-- /panel -->
</div><!-- /col -->

<script>
$(document).on('appUpdate', function(e, lang) {

    $.getJSON( appUrl + '/module/mbp15_battery_repair_program/get_mbp15_battery_repair_program_state', function( data ) {

    	if(data.error){
    		//alert(data.error);
    		return;
    	}
		
		var panel = $('#mbp15_battery_repair_program-state-widget div.panel-body'),
			baseUrl = appUrl + '/show/listing/mbp15_battery_repair_program/mbp15_battery_repair_program';
		panel.empty();
		
		// Set statuses
		if(data.unknown != "0"){
		panel.append(' <a href="'+baseUrl+'#unknown" class="btn btn-info"><span class="bigger-150">'+data.unknown+'</span><br>'+i18n.t('unknown')+'</a>');
		}
		if(data.eligible != "0"){
			panel.append(' <a href="'+baseUrl+'#E00-Eligible" class="btn btn-danger"><span class="bigger-150">'+data.elgibile+'</span><br>&nbsp;&nbsp;'+i18n.t('eligible')+'&nbsp;&nbsp;</a>');
		}
		if(data.ineligible != "0"){
			panel.append(' <a href="'+baseUrl+'#E01-Ineligible" class="btn btn-success"><span class="bigger-150">'+data.ineligible+'</span><br>&nbsp;&nbsp;'+i18n.t('ineligible')+'&nbsp;&nbsp;</a>');
		}
\    });
});
</script>
