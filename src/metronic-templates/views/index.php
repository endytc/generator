@extends('layouts.master')

@section('title', $title='<?=$gen->titlePlural()?>')
@section('content')
<?php if($export):?>
<?="<?php "?>
$parameters = Request::all();
$build_query = urldecode(http_build_query($parameters));
$build_query = (Request::all())?"?".$build_query:"";
$build_query_add = str_replace(array("&detail=1","detail=1"),"",$build_query);
<?="?>"?>
<?php endif;?>
 <div class="row">
		<div class="col-md-12">
			<div class="portlet light">
				<div class="portlet-title">
					<div class="caption">
						<i class="fa fa-list font-blue-madison"></i>
						<span class="caption-subject font-blue-madison bold uppercase">{{ $title }}</span>
					</div>
				</div>
				<div class="portlet-body" style="display: block;">
					<div class="row dataTables_extended_wrapper">
						<div class="col-md-6 col-sm-12">
							@can('add-<?=$gen->authAttr?>')
							<a class="btn btn-sm blue-madison" href="{{ route('<?=$gen->route()?>.create') }}" target="ajax-modal"><i class="fa fa-plus"></i> Tambah</a>
							@endcan
							<?php if($export):?>
							<a href="javascript:void(0)" id='btn_export_data' data-url-parameter='{{$build_query}}' title='Export Data' class="btn btn-app btn-export-data">
								<i class="fa fa-download"></i> Export Data
							</a>
							<?php endif;?>
						</div>
						<?php if(in_array('deleted_at',$fieldsArr)):?>
						<div class="col-md-6 col-sm-12">
						 <div class="checkbox pull-right">
						    <label>
						      <input type="checkbox" @if(request()->get('status')) checked="checked" @endif class="trash-ck"> <i class="fa fa-trash"></i> Trash data
						    </label>
						  </div>
						</div>
						<?php endif; ?>
					</div>
		            <div class="clearfix"></div>
					<div class="table-responsive">
						<table class="table table-striped table-bordered table-hover" id="<?=$gen->route()?>-table">
					        <thead>
					            <tr>
					                <th>No</th>
			<?php foreach ( $fields as $field )  { ?>
			<?php if(in_array($field->name,\App\Generator\src\DB::skippedFields()) && $field->name!='created_at') continue;?>
						            <th><?=$field->title()?></th>
			<?php } ?>
					                <th>Action</th>
					            </tr>
					        </thead>
					    </table>
					</div>
				</div>
				
			</div>

		</div>
	</div>

@include('layouts.delete')
	
@endsection

@push('js')
<script type="text/javascript">
$(function() {
	<?php if($export):?>
	$('.btn-export-data').click(function() {
		$('#export-data').modal('show');
	});
	var toggle_advanced_report_boolean = 1;
	$(".toggle_advanced_report").click(function() {
		
		if(toggle_advanced_report_boolean==1) {
			$("#advanced_export").slideDown();
			$(this).html("<i class='fa fa-minus-square-o'></i> Show Advanced Export");
			toggle_advanced_report_boolean = 0;
		}else{
			$("#advanced_export").slideUp();
			$(this).html("<i class='fa fa-plus-square-o'></i> Show Advanced Export");
			toggle_advanced_report_boolean = 1;
		}		
		
	});
	<?php endif;?>

	$('.trash-ck').click(function(){
		if ($('.trash-ck').prop('checked')) {
			document.location = '{{ url("<?=$gen->route()?>?status=trash") }}';
		} else {
			document.location = '{{ url("<?=$gen->route()?>") }}';
		}
	});
    $('#<?=$gen->route()?>-table').DataTable({
    	stateSave: true,
		processing : true,
		serverSide : true,
		pageLength:20,
        ajax : {
				<?php if(in_array('deleted_at',$fieldsArr)) { ?>
					@if(request()->get('status') == 'trash')
					url:"{{ url('<?=$gen->route()?>/load-data') }}?status=trash",
					@else
					url:"{{ url('<?=$gen->route()?>/load-data') }}",
					@endif
				<?php }else{ ?>
					url:"{{ url('<?=$gen->route()?>/load-data') }}",
				<?php } ?>
				data: function (d) {
		             
		            }
			},
        columns: [
        	{ data: 'nomor', name: 'nomor',searchable:false,orderable:false },
        	<?php $count=1;?>
			<?php foreach ( $fields as $field )  { ?>
			<?php if(in_array($field->name,\App\Generator\src\DB::skippedFields()) && $field->name!='created_at') continue;?>
        	{ data: '<?=$field->name?>', name: '<?=$field->name?>' },
			<?php $count++; } ?>
        	{ data: 'action', name: 'action', orderable: false, searchable: false },
    	],
    	language: {
            lengthMenu : '{{ "Menampilkan _MENU_ data" }}',
            zeroRecords : '{{ "Data tidak ditemukan" }}' ,
            info : '{{ "_PAGE_ dari _PAGES_ halaman" }}',
            infoEmpty : '{{ "Data tidak ditemukan" }}',
            infoFiltered : '{{ "(Penyaringan dari _MAX_ data)" }}',
            loadingRecords : '{{ "Memuat data dari server" }}' ,
            processing :    '{{ "Memuat data data" }}',
            search :        '{{ "Pencarian:" }}',
            paginate : {
                first :     '{{ "<" }}' ,
                last :      '{{ ">" }}' ,
                next :      '{{ ">>" }}',
                previous :  '{{ "<<" }}'
            }
        },
         buttons: [
            {
                text: 'Reload table',
                action: function () {
                    table.ajax.reload();
                }
            }
        ],
        // bFilter : false,
        bLengthChange : true,
        "columnDefs": [
		    // { className: "right", "targets": [ 6 ] },
		    { className: "center", "targets": [ 0,<?=$count?> ] }
		]
    });
});
</script> 
<?php if($export):?>
<div class="modal fade" tabindex="-1" role="dialog" id='export-data'>
	<div class="modal-dialog">
		<div class="modal-content" >
			<div class="modal-header">
				<button class="close" aria-label="Close" type="button" data-dismiss="modal">
				<span aria-hidden="true">×</span></button>
				<h4 class="modal-title"><i class='fa fa-download'></i> Export Data</h4>
			</div>
			<form method='post' target="_blank" action='{{url("pendidikan/export-data?t=".time())}}'> 
			<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<div class="modal-body">
					
					@if(@$_GET)
					@foreach(@$_GET as $a=>$b)
					<?="<?php"?>
					if(is_array($b)) {
					$a = $a.'['.key($b).']';
					$b = $b[key($b)];
					}					
					echo "<input type='hidden' name='$a' value='$b'/>";
					<?="?>"?>
					@endforeach
					@endif

					<div class="form-group">
						<label>File Name</label>
						<input type='text' name='filename' class='form-control' required value='Report Pendidikan - {{date("d M Y")}}'/>
						<div class='help-block'>You can rename the filename according to your whises</div>
					</div>

					<p><a href='javascript:void(0)' class='toggle_advanced_report' title='Click here for more advanced configuration export data'><i class='fa fa-plus-square-o'></i> Show Advanced Export</a></p>

					<div id='advanced_export' style='display: none'>
					<div class="form-group">
						<label>Max Data</label>
						<input type='number' name='limit' class='form-control' required value='20' max="10000" min="1" />						
					</div>	

					<div class="form-group">
						<label>Format Export</label>
						<select name='fileformat' class='form-control'>
							<option value='pdf'>PDF</option>
							<option value='xls'>Microsoft Excel (xls)</option>							
							<option value='csv'>CSV</option>
						</select>
					</div>							

					<div class="form-group">
						<label>Page Size</label>
						<select class='form-control' name='page_size'>
							<option value='Letter'>Letter</option>
							<option value='Legal'>Legal</option>
							<option value='Ledger'>Ledger</option>
							<?="<?php"?>
							for($i=0;$i<=8;$i++):
							<?="?>"?>
							<option value='A{{$i}}'>A{{$i}}</option>
							<?="<?php endfor;?>"?>

							<?='<?php for($i=0;$i<=10;$i++):'?>
							<?="?>"?>
							<option  value='B{{$i}}'>B{{$i}}</option>
							<?="<?php endfor;?>"?>
						</select>		
						<div class='help-block'><input type='checkbox' name='default_paper_size' value='1'/> Set As Default Paper Size</div>				
					</div>

					<div class="form-group">
						<label>Page Orientation</label>
						<select class='form-control' name='page_orientation'>
							<option value='potrait'>Potrait</option>
							<option value='landscape'>Landscape</option>
						</select>						
					</div>
					</div>

				</div>
				<div class="modal-footer">
					<button class="btn btn-default pull-left" type="button" data-dismiss="modal">Close</button>					
					<button class="btn btn-primary btn-submit" type="submit">Export</button>
				</div>
			</form>
		</div>
		<!-- /.modal-content -->
	</div>
</div>
<?php endif;?>

@endpush
