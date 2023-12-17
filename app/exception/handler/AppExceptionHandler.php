<?php
declare(strict_types=1);

namespace app\exception\handler;

use app\exception\BusinessException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

class AppExceptionHandler extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var string[]
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     * @param Throwable $exception
     * @return void
     */
    public function report(Throwable $exception): void
    {
        parent::report($exception); // TODO: Change the autogenerated stub
    }

    /**
     * @param $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        if ($e instanceof ValidateException) {
            return json([
                'code' => $e->getCode(),
                'message'  => $e->getMessage(),
            ]);
        }
        if ($e instanceof BusinessException) {
            return json([
                'code' => $e->getCode(),
                'message'  => $e->getMessage(),
            ]);
        }
        return parent::render($request, $e); // TODO: Change the autogenerated stub
    }
}