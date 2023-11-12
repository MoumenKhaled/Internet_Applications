<?php
namespace App\Aspect;

use Closure;
use App\Models\Log;
//use Illuminate\Support\Facades\Log;

class LogRequestsAndResponses
{
    public function handle($request, Closure $next)
    {

        $response = $next($request);


        // تخزين السجل في قاعدة البيانات
        $log = new Log();
        $log->request =(json_encode($response));
        $log->response =  $response->getContent();
        $log->save();

        return $response;
}
}