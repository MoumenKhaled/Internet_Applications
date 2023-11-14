<?php
// app/Services/UserService.php
namespace App\Services;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Public_File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class PublicService
{
    public function list_public_files_service($Data)
    {
        if ($Data) {
            foreach ($Data as $file) {
                if ($file['user_file_id']) {
                    $user = User::where('id', $file['user_file_id'])->first();
                    $file['file_using_from'] = $user;
                }
            }
        }

        return $Data;
    }
    public function add_public_file_service($Data)
    {
        $file = $Data['file'];
        $file_name=$Data['name'] . '.'. $file->getClientOriginalExtension();
        $file->move(public_path('uploads/publicfiles'),$file_name);
        $path = "public/uploads/publicfiles/" . $file_name;

        $isexist= $email=Public_file::where('name', $file_name)->first();
        if ($isexist){
            $publicfile = "This name of file has already been taken,please change it";
        }
        else {
            $publicfile=new Public_file();
            $publicfile->owner_file_id=auth()->id();
            $publicfile->name=$file_name;
            $publicfile->file=$path;
            $publicfile->save();
        }

        return $publicfile;
    }
    public function my_public_files_service($Data)
    {
        $my_files = Public_file::where('owner_file_id', $Data)->get();

        if ($my_files) {
            foreach ($my_files as $file) {
                if ($file['user_file_id']) {
                    $user = User::where('id', $file['user_file_id'])->first();
                    $file['file_using_from'] = $user;
                }
            }
        }

        return $my_files;
    }
    public function check_in_public_file_service($Data)
    {
        $my_files = Public_file::where('id', $Data)->first();
        $user_id=auth()->id();
        $user_info = User::where('id',$user_id)->first();
        if ($user_info->number_of_files < 10 && $my_files->status=="free"){
            $my_files->status="reserved";
            $my_files->user_file_id=$user_id;
            $my_files->save();

            $user_info->number_of_files=($user_info->number_of_files)+1;
            $user_info->save();

            // download file
              //PDF file is stored under project/public/download/info.pdf
           $file = public_path() . "/uploads/publicfiles/" . $my_files->name;
           $response = new BinaryFileResponse($file);
           $response->setContentDisposition(
             ResponseHeaderBag::DISPOSITION_INLINE // أو استخدم DISPOSITION_ATTACHMENT إذا كنت ترغب في التنزيل
            );
            // $my_files =[
            //         'file_info' => $my_files,
            //         'user_info' => $user_info,
            //         'file' => $response
            // ];
        }
        else {
            $response="Sorry, you cannot reserve this file";
        }
        return $response;
    }
    public function check_in_public_list_files_service($Data)
    {
          foreach ($Data as $number) {
            $array = $array + $number;
        }
          return $array;
    }
    public function  check_out_public_file($Data)
    {
        $file = $Data['file'];
        $file_name = $file->getClientOriginalName();

        $orginal_file=Public_file::where('id',$Data['file_id'])->first();
        if ($orginal_file['name']==$file_name){
             $file->move(public_path('uploads/publicfiles'),$file_name);
             $orginal_file->status='free';
             $orginal_file->user_file_id=null;
             $orginal_file->save();
        }
        else {
            $orginal_file='Sorry, this file is different from the one that was reserved';
        }
        return $orginal_file;
}
}
