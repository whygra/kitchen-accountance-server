<?php
namespace App\Http\Rules;

class ProjectRules {
    
    public static function projectRules() {
        return [
            'project_id'=>'required|exists:projects,id',
        ];
    }
    
    public static function storeProjectRules() {
        return [
            'is_public' => 'nullable|boolean',
            'logo_name' => 'nullable|string',
            'backdrop_name' => 'nullable|string',
        ];
    }
    
    public static function updateProjectRules() {
        return [
            'project_id'=>'required|exists:projects,id',
            'is_public' => 'nullable|boolean',
            'logo_name' => 'nullable|string',
            'backdrop_name' => 'nullable|string',
        ];
    }

    public static function uploadLogoRules() {
        return [           
            'file'=>[
                'required', 
                'image:jpeg,png,jpg,svg',
                'max:2048'
            ]
        ];
    }

    public static function uploadBackdropRules() {
        return [           
            'file'=>[
                'required', 
                'image:jpeg,png,jpg,svg',
                'max:2048'
            ]
        ];
    }
    
}
