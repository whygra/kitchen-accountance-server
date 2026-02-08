<?php

namespace App\Exports\ProjectExport\Sheets;

use App\Exports\ProjectExport\ProjectExport;
use App\Models\Distributor\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class DishesTagsSheet implements FromCollection, WithTitle, WithMapping
{
    private $project_id;

    public function __construct(int $id) {
        $this->project_id = $id;
    }

    public function collection()
    {
        $project_id = $this->project_id;

        return DB::table('dishes_tags')
            ->join('dishes', 'dishes_tags.dish_id', 'dishes.id')
            ->join('dish_tags', 'dishes_tags.tag_id', 'dish_tags.id')
            ->where('dish_tags.project_id', $project_id)
            ->select(['dishes.name as dish', 'dish_tags.name as tag'])
            ->get();
    }

    /**
    * @param Collection $dish_tag
    */
    public function map($dish_tag): array
    {
        return [
            [
                $dish_tag->dish,
                $dish_tag->tag,
            ]
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return ProjectExport::DISHES_TAGS_SHEET_TITLE;
    }
}