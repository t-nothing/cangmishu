<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function image(Request $request)
    {
        $this->validate($request, [
            'image' => 'required|image',
        ]);

        $path = $request->file('image')->storePublicly('/public/imgs');

        $path = Storage::url($path);
        $url = app('url')->to('/') . $path;

        return formatRet(0, '', compact('url', 'path'));
    }

    public function pdf(Request $request)
    {
        $this->validate($request, [
            'pdf' => 'required',
        ]);

        if (!$request->file('pdf')) {
            return formatRet("422", "请上传文件", [], 422);
        }

        $ext = $request->file('pdf')->getClientOriginalExtension();     // 扩展名
        if ($ext != 'pdf' && $ext != 'PDF') {
            return formatRet("422", "格式不对", [], 422);
        }

        $pdfname = $request->file('pdf')->getClientOriginalName();
        $path = $request->file('pdf')->storePublicly('/public/pdfs');

        $path = Storage::url($path);
        $url = app('url')->to('/') . $path;

        return formatRet(0, '', compact('pdfname', 'url', 'path'));
    }
}
