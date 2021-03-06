<?php

namespace App\Http\Controllers\Admin;

use App\Models\Upload;
use App\Http\Requests\UploadRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class UploadController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function __construct(Guard $auth) {
        $this->auth = $auth;
    }

    public function index() {
        //
        $data['title'] = 'Menu Upload';
        if ($this->auth->user()->status == 'admin') {
            return view('backend.upload.index', $data);
        }
        return view('guru.upload.index', $data);
    }

    public function apiUpload($id = null) {
        if ($this->auth->user()->status == 'admin') {
            $data = $id ? Upload::find($id) : Upload::OrderBy('id_download','desc')->get();
        } else {
            $penulis = $this->auth->user()->nama_pegawai;
            $data = Upload::where('author', $penulis)->get();
        }
        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create() {
        //
        $data['title'] = 'Tambah Upload';
        if ($this->auth->user()->status == 'admin') {
            return View('backend.upload.create', $data);
        }
        return View('guru.upload.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(UploadRequest $request) {
        //
        $path = public_path('upload/file');
        $input = $request->except('file');
        $upload = new Upload();
        if ($request->hasFile('file')) {
            $upload->judul_file = $input['data'];
            $upload->tgl_posting = date('Y-m-d');
            $upload->author = $this->auth->user()->nama_pegawai;
            $upload->nama_file = Str::random(25).".".$request->file('file')->getClientOriginalExtension();
            $request->file('file')->move($path, $upload->nama_file);
            if ($upload->save()) {
                return response()->json(array('success' => TRUE));
            };
        }
    }

    public function updateFile(UploadRequest $request) {
        $path = public_path('upload/file');
        $input = json_decode($request->get('data'));
        $upload = Upload::find($input->id_download);
        if ($request->hasFile('file')) {

            // File Delete By Agus Cahyono
            $file_lama = DB::table('tbl_download')->select('nama_file')->where('id_download',$input->id_download)->first();
            File::delete('upload/file/'.$file_lama->nama_file);


            $upload->judul_file = $input->judul_file;
            $upload->author = $this->auth->user()->nama_pegawai;
            $upload->nama_file = Str::random(25).".".$request->file('file')->getClientOriginalExtension();
            $request->file('file')->move($path, $upload->nama_file);
            if ($upload->save()) {
                return response()->json(array('success' => TRUE));
            };
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id) {
        //
        $data = Upload::find($id);
        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id) {
        //
        $data['title'] = 'Edit Upload';
        if ($this->auth->user()->status == 'admin') {
            return view('backend.upload.edit', $data);
        }
        return view('guru.upload.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(UploadRequest $request, $id) {
        //
        $input = $request->all();
        $input['author'] = $this->auth->user()->nama_pegawai;
        $upload = Upload::find($id);

        if ($upload->update($input)) {
            return response()->json(array('success' => TRUE));
        }
    }

    public function update_didownload($id) {
        $upload =  Upload::find($id);
        $jumlah = $upload->didownload + 1;
        $upload->didownload = $jumlah;
         if ($upload->update()) {
            return redirect()->to('upload/file/'.$upload->nama_file);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id) {
        // File Delete By Agus Cahyono
        $file_lama = DB::table('tbl_download')->select('nama_file')->where('id_download',$id)->first();
        File::delete('upload/file/'.$file_lama->nama_file);

        $data = Upload::find($id);
        if ($data->delete()) {
            return response()->json(array('success' => TRUE, 'msg' => 'Data Berhasil Dihapus'));
        }
    }

}
