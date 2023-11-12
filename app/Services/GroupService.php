<?php
namespace App\Services;
use App\Models\User;
use App\Models\File;
use App\Models\Group;
use App\Models\User_Group;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class GroupService
{
    public function create_group_service($Data){
        $group = new Group();
        $group->name=$Data['name'];
        $group->owner_id=auth()->id();
        $group->save();
        return $group;
    }
    public function add_file_to_group_service($Data){
        $group=Group::where('id',$Data['group_id'])->first();
        $owner_group=$group->owner_id;
        $user_id=auth()->id();

        if ($owner_group==$user_id){

        $file = $Data['file'];
        $file_name=$Data['name'] . '.'. $file->getClientOriginalExtension();
        $file->move(public_path("uploads/Group_files/"),$file_name);
        $path = "public/uploads/Group_files/" . $file_name;

        $isexist=File::where('name', $file_name)->first();

            if ($isexist){
                $file_group = "نعتذر هذا الاسم للملف قد تم استخدامه";
            }
            else {
            $file_group = new File();
            $file_group->name=$file_name;
            $file_group->owner_file_id=auth()->id();
            $file_group->group_id=$Data['group_id'];
            $file_group->status='free';
            $file_group->file=$path;
            $file_group->save();
            }
        }
        else {
            $file_group='نعتذر لا يحق لك اضافة ملف';
        }
        return $file_group;
    }
    public function delete_file_from_group_service($Data){
        $file = File::where([
            ['id', $Data['file_id']],
            ['group_id', $Data['group_id']],
        ])->first();
        if ($file){
            if ($file['status']=='free'){
                $group=Group::where('id',$Data['group_id'])->first();
                $owner_group=$group->owner_id;
                $user_id=auth()->id();
                if ($owner_group==$user_id){
                $file->delete();
                $response = "تم حذف الملف بنجاح ";
                }
                else {
                    $response = "لا يحق لك حذف هذا الملف ";
                }
            }
            else {
                $response = "هذا الملف محجوز";
            }

        }
        else $response = "هذا الملف غير موجود";

        return $response;
    }
    public function add_user_to_group_service($Data){
        $group=Group::where('id',$Data['group_id'])->first();
        if($group){
        $owner_group=$group->owner_id;
        $isexist = User_Group::where([
            ['user_id', $Data['user_id']],
            ['group_id', $Data['group_id']],
        ])->first();
        $user_id=auth()->id();
            if ($owner_group==$user_id){
                if ($isexist){
                    $user_group="هذا المستخدم موجود بالفعل";
                }
                else {
                $user_group=new User_Group();
                $user_group->user_id=$Data['user_id'];
                $user_group->group_id=$Data['group_id'];
                $user_group->save();
                }
            }
            else {
                $user_group = "لا يحق لك اضافة مستخدم ";
            }
        }
        else {
            $user_group = "فشلت العملية لعدم وجود هذا القروب او المستخدم";
        }

        return $user_group;
    }
    public function delete_user_from_group_service($Data){
        $group=Group::where('id',$Data['group_id'])->first();
        if($group){
        $owner_group=$group->owner_id;
        $user_group = User_Group::where([
            ['user_id', $Data['user_id']],
            ['group_id', $Data['group_id']],
        ])->first();
        $user_id=auth()->id();
        $file_reseved=File::where([
            ['user_file_id', $Data['user_id']],
            ['group_id', $Data['group_id']],
            ['status','reserved']
        ])->first();
            if ($owner_group==$user_id){
                if ($user_group){
                    if ($file_reseved){
                        $user_group="هذا المستخدم قد حجز ملف ، لا تستطيع حذفه";
                    }
                    else {
                        $user_group->delete();
                        $user_group="تم حذف المستخدم بنجاح";
                    }
                }
                else {
                    $user_group = "هذا المستخدم غير موجود في هذا القروب";
                }
            }
            else {
                $user_group = "لا يحق لك حذف هذا المستخدم ";
            }
        }
        else {
            $user_group = "فشلت العملية لعدم وجود هذا القروب او المستخدم";
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
            $allUsers = 'لا يوجد مستخدمين في هذه المجموعة';
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
        return $groups;
    }
    public function list_joined_groups_service($Data){
        $groups=User_Group::where('user_id',$Data)->get();
        return $groups;
    }
    public function read_file_service($Data){
        $file=File::where('id',$Data)->first();
        if ($file->status=='free'){
            $path = public_path() . "/uploads/Group_files/" . $file->name;
            $content = file_get_contents($path);
            $cleanString = mb_convert_encoding($content, 'UTF-8', 'UTF-8');
        }
        else {
            $cleanString='نعتذر هذا الملف محجوز ، لاتستطيع قراءته';
        }
        return $cleanString;
    }
}
