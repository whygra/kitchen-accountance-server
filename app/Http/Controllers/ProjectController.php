<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\DeleteProjectRequest;
use App\Http\Requests\Project\GetProjectRequest;
use App\Http\Requests\Project\GetProjectWithPurchaseOptionsRequest;
use App\Http\Requests\Project\GetUserProjectsRequest;
use App\Http\Requests\Project\UploadPurchaseOptionsFileRequest;
use App\Models\Project;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\StoreProjectWithPurchaseOptionsRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Requests\Project\UpdateProjectWithPurchaseOptionsRequest;
use App\Http\Requests\Project\UploadProjectBackdropImageRequest;
use App\Http\Requests\Project\UploadProjectLogoRequest;
use App\Http\Resources\Project\ProjectResource;
use App\Http\Resources\Project\UserProjectResource;
use App\Imports\PurchaseOptionsImport;
use App\Models\Product\Product;
use App\Models\Project\PurchaseOption;
use App\Models\Project\Unit;
use App\Models\Product\ProductPurchaseOption;
use App\Models\User\User;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function all_user_projects(GetUserProjectsRequest $request)
    {
        $user = User::find(Auth::user()->id);
        return response()->json(UserProjectResource::collection($user->projects()->get()));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $new = new Project;
        $new->name = $request->name;
        $new->backdrop_name = $request['backdrop']['name'] ?? '';
        $new->logo_name = $request['logo']['name'] ?? '';

        $new->save();
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetProjectRequest $request, $id)
    {
        $item = $request->user()->projects()->with('creator', 'updated_by_user')->find($id);
        if(empty($item))
            return response()->json([
                'message' => 'Проект с id='.$id.'не найден'
            ], 404);
        return response()->json(new UserProjectResource($item));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, $id)
    {
        $item = Project::find($id);
        if(empty($item))
            return response()->json([
                'message' => 'Проект с id='.$id.'не найден'
            ], 404);
         
        $item->name = $request->name;

        if(!empty($request['backdrop'])){
            if($item->backdrop_name != '')
                Storage::disk('public')->delete('images/project_'.$id.'/backdrop/'.$item->backdrop_name);
            $item->backdrop_name = $request['backdrop']['name'] ?? '';
        }

        if(!empty($request['logo'])){
            if($item->logo_name != '')
                Storage::disk('public')->delete('images/project_'.$id.'/logo/'.$item->logo_name);
            $item->logo_name = $request['logo']['name'] ?? '';
        }
        $item->save();
        return response()->json($item, 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DeleteProjectRequest $request, $id)
    {
        $item = Project::find($id);
        if(empty($item))
            return response()->json([
                'message' => "Не удалось найти проект с id=".$item->id
            ], 404);
        
        $item->delete();
        return response()->json($item);
    }

    

    public function upload_logo(UploadProjectLogoRequest $request, $project_id){
        $item = Project::find($project_id);
        $file = $request->file('file');

        $image_uploaded_path = $item->uploadLogo($file);

        $item->save();
        return response()->json([
            "name" => basename($image_uploaded_path),
            "url" => url()->to('/').Storage::url($image_uploaded_path),
        ], 201);
    }

    public function upload_backdrop(UploadProjectBackdropImageRequest $request, $project_id){
        $item = Project::find($project_id);
        $file = $request->file('file');
        
        $image_uploaded_path = $item->uploadBackdrop($file);

        $item->save();
        return response()->json([
            "name" => basename($image_uploaded_path),
            "url" => Storage::url($image_uploaded_path),
        ], 201);
    }
}
