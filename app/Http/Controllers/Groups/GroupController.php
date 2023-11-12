<?php

namespace App\Http\Controllers\Groups;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GroupService;
use App\Models\User;
class GroupController extends Controller
{
    protected $groupservice;

    public function __construct(GroupService $groupservice)
    {
        $this->groupservice = $groupservice;
    }
    public function create_group(Request $request){
        $group=$request->validate([
            'name'=>'required',
        ]);
        $response = $this->groupservice->create_group_service($group);
        return response($response);
    }
    public function add_file_to_group(Request $request){
        $file_group=$request->validate([
            'name'=>'required',
            'file'=>'required',
            'group_id'=>'required',
        ]);
        $response = $this->groupservice->add_file_to_group_service($file_group);
        return response($response);
    }
    public function delete_file_from_group(Request $request){
        $file_group=$request->validate([
            'file_id'=>'required',
            'group_id'=>'required'
        ]);
        $response = $this->groupservice->delete_file_from_group_service($file_group);
        return response($response);
    }
    public function add_user_to_group(Request $request){
        $Data=$request->validate([
            'user_id'=>'required',
            'group_id'=>'required'
        ]);
        $response = $this->groupservice->add_user_to_group_service($Data);
        return response($response);
    }
    public function delete_user_from_group(Request $request){
        $Data=$request->validate([
            'user_id'=>'required',
            'group_id'=>'required'
        ]);
        $response = $this->groupservice->delete_user_from_group_service($Data);
        return response($response);
    }
    public function list_user_in_my_group(Request $request,$group_id){
        $response = $this->groupservice->list_user_in_my_group_service($group_id);
        return response($response);
    }
    public function group_details(Request $request,$group_id){
        $response = $this->groupservice->group_details_service($group_id);
        return response($response);
    }
    public function list_created_groups(Request $request){
        $owner_id=auth()->id();
        $response = $this->groupservice->list_created_groups_service($owner_id);
        return response($response);
    }
    public function list_joined_groups(Request $request){
        $owner_id=auth()->id();
        $response = $this->groupservice->list_joined_groups_service($owner_id);
        return response($response);
    }
    public function list_users(Request $request){
        $response = User::get();
        return response($response);
    }
    public function read_file(Request $request,$file_id){
        $response = $this->groupservice->read_file_service($file_id);
        return response($response);
    }

}
