<?php
namespace App\Aspect;

use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use App\Models\Log;

class LoggingAspect implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        // Before logic (logging request)
        $request = request();
        Log::create([
            'request' => json_encode($request->all()),
        ]);
        // Proceed with the original method
        $result = $invocation->proceed();
        // After logic (logging response)
        $response = response($result);
        Log::latest()->first()->update([
            'response' => json_encode($response->getContent()),
        ]);
        return $result;
    }
}
