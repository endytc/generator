@extends(Request::get('ajax') ? 'layouts.modal':'layouts.content')

@section('title', $title=($<?=$gen->modelVariableName()?>->exists?'Edit':'Tambah').' <?=$gen->titlePlural()?>')

@section('sub-content')

		{{ Form::model($<?=$gen->modelVariableName()?>,array('route' => array((!$<?=$gen->modelVariableName()?>->exists) ? '<?=$gen->route()?>.store':'<?=$gen->route()?>.update',$<?=$gen->modelVariableName()?>->pk()),
	        'class'=>'form-horizontal form-bordered','id'=>'<?=$gen->route()?>-form','method'=>(!$<?=$gen->modelVariableName()?>->exists) ? 'POST' : 'PUT')) }}
		
		<div class="modal-body">
			<div class="panel-body panel-body-nopadding" style="display: block;">
	            <div class="col-md-12 col-sm-12">
<?php foreach ( $fields as $field )  { ?>
<?php if( $str = \App\Generator\src\Db::getFormInputMarkup( $field, $gen->modelVariableName() ) ) { ?>
        <?=$str?>
<?php } ?>
<?php } ?>

	           	</div>
	        </div>
		</div>
		
		<div class="modal-footer panel-footer">
			@if(Request::get('ajax'))<button type="button" class="btn default" data-dismiss="modal">Tutup</button>@endif
			<button class="btn blue">Simpan</button>
		</div>
	    
	    {{ Form::close() }}

@endsection

@push('js')
<script type="text/javascript">
$(document).ready(function(){
	var rule=defaultValidation;
	$('#<?=$gen->route()?>-form').validate(rule);
});
</script>		
@endpush