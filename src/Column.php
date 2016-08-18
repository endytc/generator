<?php
namespace App\Generator\src;

/**
* kolom database
*/
class Column
{
	public function title(){
		return ucwords( str_replace( "_"," ", $this->name ) );
	}
}
?>