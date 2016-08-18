<?php
/* @var $gen \App\Generator\src\Commands\Crud */
?>
<?='<?php'?>

namespace App\Http\Controllers;

use App\Models\{{$gen->modelClassName()}};
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Datatables;

class {{$gen->controllerClassName()}} extends Controller
{
    public $viewDir = "{{$gen->viewsDirName()}}";

    public function index()
    {
        $this->authorize('view-{{$gen->authAttr}}');
        return $this->view( "index");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('add-{{$gen->authAttr}}');
        return $this->view("form",['{{$gen->modelVariableName()}}' => new {{$gen->modelClassName()}}]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store( Request $request )
    {
        $this->authorize('add-{{$gen->authAttr}}');
        $this->validate($request, {{$gen->modelClassName()}}::validationRules());

        $act={{$gen->modelClassName()}}::create($request->all());
        message($act,'Data {{$gen->titlePlural()}} berhasil ditambahkan','Data {{$gen->titlePlural()}} gagal ditambahkan');
        return redirect('{{$gen->route()}}');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $kode)
    {
        $this->authorize('view-{{$gen->authAttr}}');
        ${{$gen->modelVariableName()}}={{$gen->modelClassName()}}::find($kode);
        return $this->view("show",['{{$gen->modelVariableName()}}' => ${{$gen->modelVariableName()}}]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $kode)
    {
        $this->authorize('edit-{{$gen->authAttr}}');
        ${{$gen->modelVariableName()}}={{$gen->modelClassName()}}::find($kode);
        return $this->view( "form", ['{{$gen->modelVariableName()}}' => ${{$gen->modelVariableName()}}] );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $kode)
    {
        $this->authorize('edit-{{$gen->authAttr}}');
        ${{$gen->modelVariableName()}}={{$gen->modelClassName()}}::find($kode);
        if( $request->isXmlHttpRequest() )
        {
            $data = [$request->name  => $request->value];
            $validator = \Validator::make( $data, {{$gen->modelClassName()}}::validationRules( $request->name ) );
            if($validator->fails())
                return response($validator->errors()->first( $request->name),403);
            ${{$gen->modelVariableName()}}->update($data);
            return "Record updated";
        }
        $this->validate($request, {{$gen->modelClassName()}}::validationRules());

        $act=${{$gen->modelVariableName()}}->update($request->all());
        message($act,'Data {{$gen->titlePlural()}} berhasil diupdate','Data {{$gen->titlePlural()}} gagal diupdate');

        return redirect('/{{$gen->route()}}');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $kode)
    {
        $this->authorize('delete-{{$gen->authAttr}}');
        ${{$gen->modelVariableName()}}={{$gen->modelClassName()}}::find($kode);
        $act=false;
        try {
            $act=${{$gen->modelVariableName()}}->forceDelete();
        } catch (\Exception $e) {
            ${{$gen->modelVariableName()}}={{$gen->modelClassName()}}::find(${{$gen->modelVariableName()}}->pk());
            $act=${{$gen->modelVariableName()}}->delete();
        }
        message($act,'Data {{$gen->titlePlural()}} berhasil dihapus','Data {{$gen->titlePlural()}} gagal dihapus');
        return redirect('/{{$gen->route()}}');
    }

    protected function view($view, $data = [])
    {
        return view($this->viewDir.".".$view, $data);
    }
    public function loadData()
    {
        $GLOBALS['nomor']=\Request::input('start',1)+1;
        $dataList = {{$gen->modelClassName()}}::select('*');
        if (request()->get('status') == 'trash') {
            $dataList->onlyTrashed();
        }
        return Datatables::of($dataList)
            ->addColumn('nomor',function($kategori){
                return $GLOBALS['nomor']++;
            })
            ->addColumn('action', function ($data) {
                $content = '';
                if (can('edit-{{$gen->authAttr}}')) {
                  $content .= '<a href="'.url("{{$gen->route()}}/".$data->pk()."/edit").'" class="btn btn-xs btn-primary" target="ajax-modal"><i class="glyphicon glyphicon-edit"></i> Edit</a>';
                }

                if (can('delete-{{$gen->authAttr}}') && request()->get('status') != 'trash') {
                  $content .= '<a href="'.url("{{$gen->route()}}/".$data->pk()).'" class="btn btn-xs btn-danger hapus" data-toggle="modal" data-method="delete"> <i class="glyphicon glyphicon-trash"></i> Delete</a>';
                }
@if(in_array('deleted_at',$fieldsArr))
                if ($data->trashed()) {
                    $content .= "<a href='".\URL::to('{{$gen->route()}}/'.$data->pk().'/restore')."' message='".('Apakah anda yakin ingin mengembalikan data '.$data->pk().' yang sudah terhapus? ')."'' class='btn blue-madison btn-xs hapus' data-toggle=\"modal\" data-method=\"delete\" title=\"Restore data\"><i class='fa fa-history'></i></a>";
                }
@endif
                return $content;
            })
            ->make(true);
    }
@if(in_array('deleted_at',$fieldsArr))
    public function restore($id)
    {
        $model = {{$gen->modelClassName()}}::where('{{ $table->pkStr() }}',$id);
        $model->restore();
        message($model, ('{{$gen->titlePlural()}} berhasil dikembalikan!'),('{{$gen->titlePlural()}} gagal dikembalikan!'));
        return redirect()->back();
    }    
@endif
@if($export)
    public function postExportData() {
        $this->limit        = \Request::input('limit');
        $filetype           = \Request::input('fileformat');
        $filename           = \Request::input('filename');
        $papersize          = \Request::input('page_size');
        $paperorientation   = \Request::input('page_orientation');       

        if(\Request::input('default_paper_size')) {
            DB::table('temp_crudbooster.cms_settings')->where('name','default_paper_size')->update(['content'=>$papersize]);
        }
        $response['columns']=\Schema::getColumnListing('{{$table->name}}');
        $response['result']={{$gen->modelClassName()}}::all();
        // dd($response['columns']);
        switch($filetype) {
            case "pdf":
                $view = view('layouts.export',$response)->render();
                $pdf = \App::make('dompdf.wrapper');
                $pdf->loadHTML($view);
                $pdf->setPaper($papersize,$paperorientation);

                // $pdf = \Barryvdh\DomPDF\Facade::loadView('layouts.export', $response);
                // return $pdf->download($filename.'.pdf');

                return $pdf->stream($filename.'.pdf'); 
            break;
            case 'xls':
                Excel::create($filename, function($excel) use($response,$filename,$paperorientation){
                    $excel->setTitle($filename)
                        ->setCreator("Fendi Tri Cahyono")
                        ->setCompany("Fendami Corporation");                  
                    $excel->sheet($filename, function($sheet) use ($response,$paperorientation) {
                        $sheet->setOrientation($paperorientation);
                        $sheet->loadView('layouts.export',$response);
                    });
                })->export('xls');
            break;
            case 'csv':
                Excel::create($filename, function($excel) use ($response,$filename,$paperorientation) {
                    $excel->setTitle($filename)
                    ->setCreator("crocodic.com")
                    ->setCompany("Fendami Corp.");                  
                    $excel->sheet($filename, function($sheet) use ($response,$paperorientation) {
                        $sheet->setOrientation($paperorientation);
                        $sheet->loadView('layouts.export',$response);
                    });
                })->export('csv');
            break;
        }
    }
@endif
}
