<?php
/* @var $gen \App\Generator\src\Commands\Crud */
/* @var $fields [] */
?>

@extends('<?=config('crud.layout')?>')

@section('content')

    <h2>Create a New <?=$gen->titleSingular()?></h2>

    <form action="/<?=$gen->route()?>" method="post">

        {{ csrf_field() }}
<?php foreach ( $fields as $field )  { ?>
<?php if( $str = \App\Generator\src\Db::getFormInputMarkup($field) ) { ?>

        <?=$str?>

<?php } ?>
<?php } ?>

        <button type="submit" class="btn btn-default">Submit</button>

    </form>

@endsection