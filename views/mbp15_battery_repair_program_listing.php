<?php $this->view('partials/head'); ?>

<?php //Initialize models needed for the table
new Machine_model;
new Reportdata_model;
new Mbp15_battery_repair_program_model;
?>

<div class="container">

  <div class="row">

  	<div class="col-lg-12">

		  <h3><span data-i18n="mbp15_battery_repair_program.mbp15_battery_repair_program"></span> <span id="total-count" class='label label-primary'>…</span></h3>

		  <table class="table table-striped table-condensed table-bordered">
		    <thead>
		      <tr>
		      	<th data-i18n="listing.computername" data-colname='machine.computer_name'></th>
		      	<th data-i18n="serial" data-colname='reportdata.serial_number'></th>
		        <th data-i18n="username" data-colname='reportdata.long_username'></th>
		      	<th data-i18n="mbp15_battery_repair_program.eligibility" data-colname='mbp15_battery_repair_program.eligibility'></th>
		      	<th data-i18n="mbp15_battery_repair_program.datecheck" data-colname='mbp15_battery_repair_program.datecheck'></th>
		      	<th data-i18n="machine.model" data-colname='machine.machine_model'></th>
		      	<th data-i18n="description" data-colname='machine.machine_desc'></th>
		      </tr>
		    </thead>
		    <tbody>
		    	<tr>
					<td data-i18n="listing.loading" colspan="15" class="dataTables_empty"></td>
				</tr>
		    </tbody>
		  </table>
    </div> <!-- /span 12 -->
  </div> <!-- /row -->
</div>  <!-- /container -->

<script type="text/javascript">

	$(document).on('appUpdate', function(e){

		var oTable = $('.table').DataTable();
		oTable.ajax.reload();
		return;

	});

	$(document).on('appReady', function(e, lang) {
		// Get column names from data attribute
		var columnDefs = [],
            col = 0; // Column counter
		$('.table th').map(function(){
              columnDefs.push({name: $(this).data('colname'), targets: col});
              col++;
		});
	    oTable = $('.table').dataTable( {
	        columnDefs: columnDefs,
	        ajax: {
                url: appUrl + '/datatables/data',
                type: "POST"
            },
            dom: mr.dt.buttonDom,
            buttons: mr.dt.buttons,
	        createdRow: function( nRow, aData, iDataIndex ) {
	        	// Update name in first column to link
	        	var name=$('td:eq(0)', nRow).html();
	        	if(name == ''){name = "No Name"};
	        	var sn=$('td:eq(1)', nRow).html();
				var link = mr.getClientDetailLink(name, sn, '<?php echo url(); ?>/');
				$('td:eq(0)', nRow).html(link);


		// Format Check-In timestamp
	        	var checkin = $('td:eq(4)', nRow).html();
	        	if(checkin == '') {
			} else {
				var date = new Date(checkin * 1000);
			}


			if(date) {
				$('td:eq(4)', nRow).html('<span title="'+date+'">'+moment(date).fromNow()+'</span>');
			} else if(!date) {
				$('td:eq(4)', nRow).html('<span title=""></span>');
			}
		}
	    });
	});
</script>

<?php $this->view('partials/foot'); ?>
