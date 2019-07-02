<?php

namespace App\Http\Controllers;

use App\Http\Requests\BaseRequests;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function image(BaseRequests $request)
    {
        $this->validate($request, [
            'image' => 'required|image',
            'type'=>'string'
        ]);
        $file = $request->input('type','imgs');
        $path = $request->file('image')->storePublicly('/public/'.$file);

        $path = Storage::url($path);
        $url = env("APP_URL").  $path;

        return formatRet(0, '', compact('url'));
    }

    public function pdf(BaseRequests $request)
    {
        $this->validate($request, [
            'pdf' => 'required',
        ]);

        if (!$request->file('pdf')) {
            return formatRet("422", trans('message.pleaseUploadFile'), [], 422);
        }

        $ext = $request->file('pdf')->getClientOriginalExtension();     // 扩展名
        if ($ext != 'pdf' && $ext != 'PDF') {
            return formatRet("422", trans('message.formatWrong'), [], 422);
        }

        $pdfname = $request->file('pdf')->getClientOriginalName();
        $path = $request->file('pdf')->storePublicly('/public/pdfs');

        $path = Storage::url($path);
        $url = app('url')->to('/') . $path;

        return formatRet(0, '', compact('pdfname', 'url', 'path'));
    }
}
