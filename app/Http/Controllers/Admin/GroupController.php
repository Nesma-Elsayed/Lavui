<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\GroupRequest;
use App\Http\Traits\ApiResponseTrait;
use App\Models\Group;
use Exception;

class GroupController extends AdminController
{
    use ApiResponseTrait;

    public function __construct()
    {
        parent::__construct();
        $this->middleware(['permission:settings'])->only('store', 'update', 'destroy', 'show');
    }

    public function index()
    {
        try {
            $groups = Group::get();
            return $this->apiResponse(200, 'All Groups', $groups);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function store(GroupRequest $request)
    {
        try {
            $group = Group::create([
                'name' => [
                    'en' => $request->name_en,
                    'ar' => $request->name_ar
                ],
                'status' => $request->status,
            ]);
            return $this->apiResponse(200, 'Group Created Successfully', $group);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function show(Group $group)
    {
        try {
            return $this->apiResponse(200, 'Group', $group);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function update(GroupRequest $request, Group $group)
    {
        try {
            $group->update([
                'name' => [
                    'en' => $request->name_en,
                    'ar' => $request->name_ar
                ],
                'status' => $request->status,
            ]);
            return $this->apiResponse(200, 'Group Updated Successfully', $group);
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }

    public function destroy(Group $group)
    {
        try {
            $group->delete();
            return $this->apiResponse(202, 'Group Deleted Successfully');
        } catch (Exception $exception) {
            return $this->apiResponse(422, $exception->getMessage());
        }
    }
}
