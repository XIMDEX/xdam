<?php

namespace App\Http\Controllers;

use App\Http\Resources\ResourceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;




class ResourceJsonController extends Controller
{
    public function getJsonFile(Request $request){
        $uuid = $request->route('damResource');
         if (Storage::disk('semantic')->exists($uuid.'.json')) {
            $file = json_decode(Storage::disk("semantic")->get($uuid.".json"));
            return response()->json($file);
        }
    }
}
