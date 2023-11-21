<?php
namespace App\Services;
use App\Models\User;
use App\Models\File;
use App\Models\Group;
use App\Models\User_Group;
use App\Models\Public_File;
use App\Models\Report;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Carbon\Carbon;
class GroupService
{
    public function create_group_service($Data){
        $isexist=Group::where('name',$Data['name'])->first();
        if($isexist){
            $group['message']='This group name has been taken, please change it';
        }
        else {
        $group = new Group();
        $group->name=$Data['name'];
        $group->owner_id=auth()->id();
        $group->save();
        }
        return $group;
    }
    public function add_file_to_group_service($Data){
        $group=Group::where('name',$Data['group_name'])->first();
        if ($group){

        $owner_group=$group->owner_id;
        $user_id=auth()->id();

        if ($owner_group==$user_id){

        $file = $Data['file'];
        $file_name=$Data['name'] . '.'. $file->getClientOriginalExtension();
        $file->move(public_path("uploads/Group_files/$group->name/"),$file_name);
        $path = "public/uploads/Group_files/$group->name/" . $file_name;

        $isexist = File::where([
            ['name', $file_name],
            ['group_id', $group->id],
        ])->first();
            if ($isexist){
                $file_group['message'] = "This file name has been taken in this group, please change it";
            }
            else {
            $file_group = new File();
            $file_group->name=$file_name;
            $file_group->owner_file_id=auth()->id();
            $file_group->group_id=$group->id;
            $file_group->status='free';
            $file_group->file=$path;
            $file_group->save();
            }
        }
        else {
            $file_group['message']='Sorry, you can not add file';
        }
    }
        else $file_group['message']='This group does not exist';
        return $file_group;
    }
    public function delete_file_from_group_service($Data){
        $group=Group::where('name',$Data['group_name'])->first();
        $file = File::where([
            ['name', $Data['file_name']],
            ['group_id', $group->id],
        ])->first();
        if ($file){
            if ($file['status']=='free'){
                $owner_group=$group->owner_id;
                $user_id=auth()->id();
                if ($owner_group==$user_id){
                $file->delete();
                $response['message'] = "The file has been deleted successfully";
                }
                else {
                    $response ['message'] = "You do not have the right to delete this file";
                }
            }
            else {
                $response['message'] = "This file is reserved";
            }

        }
        else $response['message'] = "This file does not exist";

        return $response;
    }
    public function add_user_to_group_service($Data){
        $group=Group::where('name',$Data['group_name'])->first();
        $user=User::where('user_name',$Data['user_name'])->first();
        if($group){
        $owner_group=$group->owner_id;
        $isexist = User_Group::where([
            ['user_id', $user->id],
            ['group_id', $group->id],
        ])->first();
        $user_id=auth()->id();
            if ($owner_group==$user_id){
                if ($isexist){
                    $user_group['message']="This user already exists";
                }
                else {
                $user_group=new User_Group();
                $user_group->user_id=$user->id;
                $user_group->group_id=$group->id;
                $user_group->save();
                }
            }
            else {
                $user_group['message'] = "You do not have the right to add a user";
            }
        }
        else {
            $user_group['message'] = "The operation failed because this group or user does not exist";
        }

        return $user_group;
    }
    public function delete_user_from_group_service($Data){
        $group=Group::where('name',$Data['group_name'])->first();
        $user=User::where('user_name',$Data['user_name'])->first();
        if($group){
        $owner_group=$group->owner_id;
        $user_group = User_Group::where([
            ['user_id', $user->id],
            ['group_id', $group->id],
        ])->first();
        $user_id=auth()->id();
        $file_reseved=File::where([
            ['user_file_id', $user->id],
            ['group_id',  $group->id],
            ['status','reserved']
        ])->first();
            if ($owner_group==$user_id){
                if ($user_group){
                    if ($file_reseved){
                        $user_group=['message'=>"This user has reserved a file, which you cannot delete"];
                    }
                    else {
                        $user_group->delete();
                        $user_group=['message'=>'The user has been deleted successfully'];
                    }
                }
                else {
                    $user_group= ['message'=>"This user does not exist in this group"];
                }
            }
            else {
                $user_group=['message'=>"You do not have the right to delete this user"] ;
            }
        }
        else {
            $user_group = ['message'=>"The operation failed because this group or user does not exist"];
        }

        return $user_group;
    }
    public function list_user_in_my_group_service($Data){
        $owner_id=auth()->id();
        $user_group = User_Group::where('group_id', $Data)->get();
        if ($user_group) {
            $allUsers = [];
            foreach ($user_group as $user) {
                if ($user['user_id']) {
                    $users = User::where('id', $user['user_id'])->first();
                    $user['users'] = $users;
                    // تخزين المستخدم في المصفوفة
                    $allUsers[] = $users;
                }
            }
        }
        else {
            $allUsers ['message']= 'There are no users in this group';
        }
        return $allUsers;
    }
    public function group_details_service($Data){
        $owner_id=auth()->id();
        $group_users = User_Group::where('group_id', $Data)->get();
        $group_files = File::where('group_id', $Data)->get();
        $group_info= Group::where('id',$Data)->get();
        if ($group_users) {
            $allUsers = [];
            foreach ($group_users as $user) {
                if ($user['user_id']) {
                    $users = User::where('id', $user['user_id'])->first();
                    $user['users'] = $users;
                    // تخزين المستخدم في المصفوفة
                    $allUsers[] = $users;
                }
            }
            foreach ($group_files as $file){
                if ($file->status=="reserved"){
                    $user_file = User::where('id', $file->user_file_id)->first();
                    $file['used_from']=$user_file->user_name;
                }
            }
        }
        $response=[
            'Group_info'=>$group_info,
            'group_files'=>$group_files,
            'group_users'=>$allUsers
               ];
        return $response;
    }
    public function list_created_groups_service($Data){
        $groups=Group::where('owner_id',$Data)->get();
        foreach($groups as $group){
            $group['file_count']=count(File::where('group_id',$group->id)->get());
        }
        return $groups;
    }
    public function list_joined_groups_service($Data){
        $groups=User_Group::where('user_id',$Data)->get();
        return $groups;
    }
    public function read_file_from_group_service($Data){
        $file=File::where('id',$Data)->first();
        $group=Group::where('id',$file->group_id)->first();
        if ($file->status=='free'){
            $path = public_path() . "/uploads/Group_files/$group->name/" . $file->name;
            $content = file_get_contents($path);
            $cleanString = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        }
        else {
            $cleanString['message']='Sorry, this file is reserved, you cannot read it';
        }
        return $cleanString;
    }
    public function check_in_group_file_service($Data)
    {
        $my_file = File::where('id', $Data)->first();
        $group=Group::where('id',$my_file->group_id)->first();
        $user_id=auth()->id();
        $user_info = User::where('id',$user_id)->first();

        if ($user_info->number_of_files < 10 && $my_file->status=="free"){
            $my_file->status="reserved";
            $my_file->user_file_id=$user_id;
            $my_file->save();

            $user_info->number_of_files=($user_info->number_of_files)+1;
            $user_info->save();
            // download file
           $file = public_path() . "/uploads/Group_files/$group->name/" . $my_file->name;
           $response = new BinaryFileResponse($file);
           $response->setContentDisposition(
             ResponseHeaderBag::DISPOSITION_INLINE // أو استخدم DISPOSITION_ATTACHMENT إذا كنت ترغب في التنزيل
            );
            ////report
            $report=new Report();
            $report->user_file_id=$user_id;
            $report->owner_file_name=User::where('id',$my_file->owner_file_id)->first()->user_name;
            $report->user_name=$user_info->user_name;
            $report->group_id=$group->id;
            $report->group_name=$group->name;
            $report->file_id=$my_file->id;
            $report->file_name=$my_file->name;
            $report->check_in_date = Carbon::now();
            $report->save();
        }
        else {
            $response['message']="Sorry, this file is reserved, and you cannot reserve it";
        }
        return $response;
    }
    public function check_out_group_file($Data)
    {
        $file = $Data['file'];
        $file_name = $file->getClientOriginalName();
        $orginal_file=file::where('id',$Data['file_id'])->first();
        $group=Group::where('id',$orginal_file->group_id)->first();

        if ($orginal_file['name']==$file_name){
            $destinationPath = public_path() . "/uploads/Group_files/$group->name/";
            $file->move($destinationPath, $file_name);
             $orginal_file->status='free';
             $orginal_file->user_file_id=null;
             $orginal_file->save();
               //report check_out_date
               $report = Report::where([
                ['file_id', $orginal_file->id],
                ['group_id',$group->id],
                ['group_name',$group->name]
               ])->latest()->first();
               if (is_null($report->check_out_date)) {
                  $report->check_out_date=Carbon::now();
                  $report->save();
              }
        }
        else {
            $orginal_file=['message'=>'Sorry, this file is different from the one that was reserved'];
        }
        return $orginal_file;
}
    public function my_reserved_file_service($Data){
        $public_files=Public_file::where('user_file_id',$Data)->get();
        $group_files=File::where('user_file_id',$Data)->get();
        foreach ($group_files as $index => $file) {
            $group = Group::where('id', $file->group_id)->first();
            $group_files[$index]['group_name'] = $group->name;
        }
        $response=[
           'Public Group'=> $public_files,
            'Groups'=>$group_files
        ];
        return $response;
     }
}
