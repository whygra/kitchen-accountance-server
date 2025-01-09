<?php

namespace App\Http\Controllers;

use App\Exports\ProjectExport;
use App\Http\Requests\Project\DeleteProjectRequest;
use App\Http\Requests\Project\DownloadProjectTablesRequest;
use App\Http\Requests\Project\GetProjectRequest;
use App\Http\Requests\Project\GetProjectWithPurchaseOptionsRequest;
use App\Http\Requests\Project\GetUserProjectsRequest;
use App\Http\Requests\Project\PublishProjectRequest;
use App\Http\Requests\Project\UploadProjectTablesRequest;
use App\Http\Requests\Project\UploadPurchaseOptionsFileRequest;
use App\Http\Requests\User\InviteUserRequest;
use App\Imports\ProjectImport;
use App\Models\Project;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\StoreProjectWithPurchaseOptionsRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Requests\Project\UpdateProjectWithPurchaseOptionsRequest;
use App\Http\Requests\Project\UploadProjectBackdropImageRequest;
use App\Http\Requests\Project\UploadProjectLogoRequest;
use App\Http\Resources\Project\ProjectResource;
use App\Http\Resources\Project\UserProjectResource;
use App\Imports\DistributorPurchaseOptionsImport;
use App\Models\Product\Product;
use App\Models\Project\PurchaseOption;
use App\Models\Project\Unit;
use App\Models\Product\ProductPurchaseOption;
use App\Models\User\Role;
use App\Models\User\User;
use Exception;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ProjectController extends Controller
{

    public function publish_project(PublishProjectRequest $request, $id) {
        $item = $request->user()->projects()->find($id);
        if(empty($item))
            return response()->json([
                'message' => 'Проект с id='.$id.'не найден'
            ], 404);
        $item->users()->attach(User::guest()->id, ['role_id' => Role::guest()->id]);
        return response()->json(new UserProjectResource($item));
    }

    public function unpublish_project(PublishProjectRequest $request, $id) {
        $item = $request->user()->projects()->find($id);
        if(empty($item))
            return response()->json([
                'message' => 'Проект с id='.$id.'не найден'
            ], 404);
        $item->users()->detach(User::guest()->id);
        return response()->json(new UserProjectResource($item));
    }

    public function all_user_projects(GetUserProjectsRequest $request)
    {
        $user = User::find(Auth::user()->id);
        return response()->json(UserProjectResource::collection($user->projects()->get()));
    }
    public function all_public_projects(GetUserProjectsRequest $request)
    {
        $user = User::guest();
        return response()->json(UserProjectResource::collection($user->projects()->get()));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $user = User::find(Auth::user()->id);
        if($user->freeProjectSlots()<1)
            return response()->json([
                'message' => "Достигнут лимит количества проектов."
            ], 400);
        $new = new Project;
        $new->name = $request->name;
        $new->backdrop_name = $request['backdrop']['name'] ?? null;
        $new->logo_name = $request['logo']['name'] ?? null;
        
        $new->save();
        
        return response()->json($new, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GetProjectRequest $request, $id)
    {
        $item = $request->user()
            ?->projects()->with('creator', 'updated_by_user')->find($id)
            ?? User::guest()->projects()->with('creator', 'updated_by_user')->find($id);
        
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

    public function download(DownloadProjectTablesRequest $request, $id) 
    {
        $project = Project::find($id);
        return (new ProjectExport($id))->download($project->name.'.xlsx');
    }

    public function upload(UploadProjectTablesRequest $request, $id) 
    {
        $project = Project::find($id);
        $path = $request->file('file')->store($project->getDirectoryPath().'/imports');
        try {
            Excel::import(new ProjectImport($project->id), $path);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            return response()->json(['message'=>$failures[0]->errors[0]]);
        } 

        // удалить файл
        Storage::delete($path);

        return response()->json($project);
    }
}
