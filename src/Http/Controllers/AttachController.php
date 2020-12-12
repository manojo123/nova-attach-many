<?php

namespace NovaAttachPivot\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Laravel\Nova\Http\Requests\NovaRequest;

class AttachController extends Controller
{
    public function create(NovaRequest $request, $parent, $relationship)
    {
        return [
            'available' => $this->getAvailableResources($request, $relationship, $parent),
        ];
    }

    public function edit(NovaRequest $request, $parent, $parentId, $relationship)
    {
        return [
            'selected' => $request->findResourceOrFail()->model()->{$relationship}->pluck('id'),
            'available' => $this->getAvailableResources($request, $relationship, $parent, $parentId),
        ];
    }

    public function getAvailableResources($request, $relationship, $parent, $parentId=false)
    {
        $parent = str_replace('-', '_', $parent);

        $resourceClass = $request->newResource();

        $field = $resourceClass
            ->availableFields($request)
            ->where('component', 'nova-attach-pivot')
            ->where('attribute', $relationship)
            ->first();

        $query = $field->resourceClass::newModel();

        return $field->resourceClass::relatableQuery($request, $query)->get()
            ->mapInto($field->resourceClass)
            ->filter(function ($resource) use ($request, $field) {
                return $request->newResource()->authorizedToAttach($request, $resource->resource);
            })->map(function($resource) use ($request, $parent, $parentId) {
                $pivotFields = [];
                if($request->pivots){
                    foreach (explode(',', $request->pivots) as $pivot) {
                        $pivotValue = null;
                        
                        if($parentId && $resource->$parent()->find($parentId)){
                            $lower_pivot = Str::lower($pivot);

                            $pivotValue = $resource->$parent()->find($parentId)->pivot->$lower_pivot;
                        }

                        $pivotFields[] = [
                            'display' => $pivot,
                            'label' => __(Str::title(Str::of($pivot)->replace(['_', '-', '.'], " "))),
                            'value' => ($pivotValue ?: 0)
                        ];
                    }
                }

                $searchableContent = $resource->title();
                if($request->searchableFields){
                    foreach (explode(',', $request->searchableFields) as $searchableField) {
                        $searchableContent .= $resource->$searchableField;
                    }
                }

                return [
                    'display' => $resource->title(),
                    'value' => $resource->getKey(),
                    'pivots' => $pivotFields,
                    'searchableContent' => $searchableContent
                ];
            })->sortBy('display')->values();
    }
}